<?php

namespace App\Http\Controllers\Back;

use App\Helpers\ImageHelper;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\InternalCronTask;
use App\Models\Item;
use App\Models\PromoCode;
use App\Models\Setting;
use App\Services\CorreiosService;
use App\Services\Cron\InternalCronRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PlatformController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('adminlocalize');
    }

    public function pwa()
    {
        return view('back.platform.pwa');
    }

    public function updatePwa(Request $request)
    {
        $request->validate([
            'pwa_icon' => 'nullable|mimes:jpeg,jpg,png,svg,webp|max:2048',
            'pwa_icon_192' => 'nullable|mimes:jpeg,jpg,png,webp|max:2048',
            'pwa_icon_512' => 'nullable|mimes:jpeg,jpg,png,webp|max:4096',
            'pwa_install_popup_image' => 'nullable|mimes:jpeg,jpg,png,svg,webp|max:2048',
            'pwa_name' => 'nullable|max:255',
            'pwa_short_name' => 'nullable|max:30',
            'pwa_theme_color' => 'nullable|max:20',
            'pwa_background_color' => 'nullable|max:20',
            'pwa_start_url' => 'nullable|max:255',
            'pwa_install_popup_title' => 'nullable|max:255',
            'pwa_install_popup_button_text' => 'nullable|max:255',
            'pwa_install_popup_later_text' => 'nullable|max:255',
            'pwa_install_popup_delay' => 'nullable|integer|min:0|max:120',
        ]);

        $setting = Setting::findOrFail(1);
        $input = $request->only([
            'pwa_name',
            'pwa_short_name',
            'pwa_theme_color',
            'pwa_background_color',
            'pwa_start_url',
            'pwa_install_popup_title',
            'pwa_install_popup_text',
            'pwa_install_popup_button_text',
            'pwa_install_popup_later_text',
            'pwa_install_popup_delay',
        ]);

        $input['is_pwa'] = $request->has('is_pwa') ? 1 : 0;
        $input['pwa_install_popup_enabled'] = $request->has('pwa_install_popup_enabled') ? 1 : 0;
        $input['pwa_auto_generate_icons'] = $request->has('pwa_auto_generate_icons') ? 1 : 0;
        $input['pwa_start_url'] = $input['pwa_start_url'] ?: '/';
        $input['pwa_install_popup_delay'] = $input['pwa_install_popup_delay'] ?? 3;

        if ($file = $request->file('pwa_icon')) {
            $input['pwa_icon'] = ImageHelper::handleUpdatedUploadedImage($file, 'images', $setting, 'images/', 'pwa_icon');
        }

        if ($file = $request->file('pwa_install_popup_image')) {
            $input['pwa_install_popup_image'] = ImageHelper::handleUpdatedUploadedImage($file, 'images', $setting, 'images/', 'pwa_install_popup_image');
        }

        if ($file = $request->file('pwa_icon_192')) {
            $input['pwa_icon_192'] = ImageHelper::handleUpdatedUploadedImage($file, 'images', $setting, 'images/', 'pwa_icon_192');
        }

        if ($file = $request->file('pwa_icon_512')) {
            $input['pwa_icon_512'] = ImageHelper::handleUpdatedUploadedImage($file, 'images', $setting, 'images/', 'pwa_icon_512');
        }

        if ($request->has('pwa_auto_generate_icons')) {
            $sourceIcon = $input['pwa_icon'] ?? $setting->pwa_icon ?: $setting->favicon;
            $generatedIcons = $this->generatePwaIcons($sourceIcon);
            $input = array_merge($input, $generatedIcons);
        }

        $setting->update($input);

        return redirect()->back()->withSuccess(__('Dados atualizados com sucesso.'));
    }

    private function generatePwaIcons(?string $sourceIcon): array
    {
        $sourceExtension = strtolower(pathinfo((string) $sourceIcon, PATHINFO_EXTENSION));

        if (!$sourceIcon || $sourceExtension === 'svg') {
            return [];
        }

        if (!in_array($sourceExtension, ['jpeg', 'jpg', 'png', 'webp'], true)) {
            return [];
        }

        $sourcePath = storage_path('app/public/images/' . $sourceIcon);
        if (!file_exists($sourcePath)) {
            $sourcePath = public_path('storage/images/' . $sourceIcon);
        }

        if (!file_exists($sourcePath)) {
            return [];
        }

        $icons = [];
        foreach ([192, 512] as $size) {
            $filename = 'pwa-' . $size . '-' . time() . Str::random(6) . '.' . $sourceExtension;
            $storagePath = storage_path('app/public/images/' . $filename);
            $publicPath = public_path('storage/images/' . $filename);

            File::ensureDirectoryExists(dirname($storagePath));
            File::ensureDirectoryExists(dirname($publicPath));
            copy($sourcePath, $storagePath);
            copy($storagePath, $publicPath);

            $icons['pwa_icon_' . $size] = $filename;
        }

        return $icons;
    }

    public function cron()
    {
        app(InternalCronRegistry::class)->ensureDefaults();

        $tasks = InternalCronTask::orderBy('id', 'desc')->get();
        $frequencies = InternalCronTask::frequencies();
        $cronRegistry = app(InternalCronRegistry::class);
        $commonSettings = $cronRegistry->commonSettings();
        $commandOptions = $cronRegistry->commandOptions();
        $selectOptions = $cronRegistry->selectOptions();

        return view('back.platform.cron', compact('tasks', 'frequencies', 'commonSettings', 'commandOptions', 'selectOptions'));
    }

    public function storeCron(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable|max:2000',
            'command' => 'required|max:255',
            'minute' => 'required|max:40|regex:/^[0-9*,\/-]+$/',
            'hour' => 'required|max:40|regex:/^[0-9*,\/-]+$/',
            'day' => 'required|max:40|regex:/^[0-9*,\/-]+$/',
            'month' => 'required|max:40|regex:/^[0-9*,\/-]+$/',
            'weekday' => 'required|max:40|regex:/^[0-9*,\/-]+$/',
        ]);
        $data['frequency'] = 'custom';

        $task = new InternalCronTask($data);
        $task->is_active = $request->has('is_active');
        $task->is_system = 0;
        $task->next_run_at = $task->calculateNextRun(now()->subMinute());
        $task->save();

        return redirect()->back()->withSuccess(__('Dados atualizados com sucesso.'));
    }

    public function updateCron(Request $request, InternalCronTask $task)
    {
        $data = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable|max:2000',
            'command' => 'required|max:255',
            'minute' => 'required|max:40|regex:/^[0-9*,\/-]+$/',
            'hour' => 'required|max:40|regex:/^[0-9*,\/-]+$/',
            'day' => 'required|max:40|regex:/^[0-9*,\/-]+$/',
            'month' => 'required|max:40|regex:/^[0-9*,\/-]+$/',
            'weekday' => 'required|max:40|regex:/^[0-9*,\/-]+$/',
        ]);

        $data['frequency'] = 'custom';
        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $task->fill($data);
        $task->next_run_at = $task->calculateNextRun(now()->subMinute());
        $task->save();

        return redirect()->back()->withSuccess(__('Dados atualizados com sucesso.'));
    }

    public function runCron(InternalCronTask $task)
    {
        Artisan::call('internal-cron:run', ['--task' => $task->id]);

        return redirect()->back()->withSuccess(__('Cron executado com sucesso.'));
    }

    public function destroyCron(InternalCronTask $task)
    {
        $task->delete();

        return redirect()->back()->withSuccess(__('Dados excluídos com sucesso.'));
    }

    public function correios()
    {
        $this->ensureSection('Correios Integration');
        return view('back.platform.correios');
    }

    public function updateCorreios(Request $request)
    {
        $this->ensureSection('Correios Integration');
        $data = $request->validate([
            'correios_mode' => 'required|in:free,paid',
            'correios_origin_cep' => 'nullable|max:20',
            'correios_services' => 'nullable|max:255',
            'correios_company_code' => 'nullable|max:255',
            'correios_posting_card' => 'nullable|max:255',
            'correios_username' => 'nullable|max:255',
            'correios_password' => 'nullable|max:255',
            'correios_token' => 'nullable',
            'correios_free_endpoint' => 'nullable|url|max:255',
            'correios_extra_days' => 'nullable|integer|min:0|max:30',
        ]);

        $data['correios_enabled'] = $request->has('correios_enabled') ? 1 : 0;
        $data['correios_extra_days'] = $data['correios_extra_days'] ?? 0;
        Setting::findOrFail(1)->update($data);

        return redirect()->back()->withSuccess(__('Dados atualizados com sucesso.'));
    }

    public function testCorreios(Request $request, CorreiosService $service)
    {
        $this->ensureSection('Correios Integration');
        $request->validate([
            'destination_cep' => 'required|max:20',
            'weight' => 'nullable|numeric|min:0.1|max:30',
            'height' => 'nullable|numeric|min:1|max:200',
            'width' => 'nullable|numeric|min:1|max:200',
            'length' => 'nullable|numeric|min:1|max:200',
        ]);

        $result = $service->quote($request->all());

        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['success'] ? 'Consulta Correios executada.' : $result['message']);
    }

    public function popups()
    {
        $this->ensureSection('Promotional Popups');
        $items = Item::whereStatus(1)->orderBy('name')->select('id', 'name', 'discount_price', 'previous_price')->get();
        $promoCodes = PromoCode::whereStatus(1)->orderBy('title')->get();

        return view('back.platform.popups', compact('items', 'promoCodes'));
    }

    public function updatePopups(Request $request)
    {
        $this->ensureSection('Promotional Popups');
        $request->validate([
            'promo_popup_image' => 'nullable|mimes:jpeg,jpg,png,svg,webp|max:2048',
            'promo_popup_title' => 'nullable|max:255',
            'promo_popup_button_text' => 'nullable|max:255',
            'promo_popup_link' => 'nullable|max:255',
            'promo_popup_delay' => 'nullable|integer|min:0|max:120',
            'promo_popup_mode' => 'nullable|in:manual,product',
            'promo_popup_item_id' => 'nullable|exists:items,id',
            'promo_popup_campaign_type' => 'nullable|in:flash,blackfriday,custom',
            'promo_popup_badge' => 'nullable|max:255',
            'promo_popup_starts_at' => 'nullable|date',
            'promo_popup_ends_at' => 'nullable|date|after_or_equal:promo_popup_starts_at',
            'exit_popup_title' => 'nullable|max:255',
            'exit_popup_coupon' => 'nullable|max:100',
            'exit_popup_button_text' => 'nullable|max:255',
            'exit_popup_link' => 'nullable|max:255',
            'exit_popup_mode' => 'nullable|in:manual,coupon,product,mixed',
            'exit_popup_coupon_ids' => 'nullable|array',
            'exit_popup_coupon_ids.*' => 'nullable|exists:promo_codes,id',
            'exit_popup_product_ids' => 'nullable|array',
            'exit_popup_product_ids.*' => 'nullable|exists:items,id',
        ]);

        $setting = Setting::findOrFail(1);
        $data = $request->only([
            'promo_popup_title',
            'promo_popup_text',
            'promo_popup_button_text',
            'promo_popup_link',
            'promo_popup_delay',
            'promo_popup_mode',
            'promo_popup_item_id',
            'promo_popup_campaign_type',
            'promo_popup_badge',
            'promo_popup_starts_at',
            'promo_popup_ends_at',
            'exit_popup_title',
            'exit_popup_text',
            'exit_popup_coupon',
            'exit_popup_button_text',
            'exit_popup_link',
            'exit_popup_mode',
        ]);
        $data['promo_popup_enabled'] = $request->has('promo_popup_enabled') ? 1 : 0;
        $data['exit_popup_enabled'] = $request->has('exit_popup_enabled') ? 1 : 0;
        $data['promo_popup_delay'] = $data['promo_popup_delay'] ?? 3;
        $data['promo_popup_mode'] = $data['promo_popup_mode'] ?? 'manual';
        $data['exit_popup_mode'] = $data['exit_popup_mode'] ?? 'manual';
        $data['exit_popup_show_random'] = $request->has('exit_popup_show_random') ? 1 : 0;

        if ($data['promo_popup_mode'] !== 'product') {
            $data['promo_popup_item_id'] = null;
        }

        $data['exit_popup_coupon_ids'] = json_encode(array_values($request->input('exit_popup_coupon_ids', [])));
        $data['exit_popup_product_ids'] = json_encode(array_values($request->input('exit_popup_product_ids', [])));

        if ($file = $request->file('promo_popup_image')) {
            $data['promo_popup_image'] = ImageHelper::handleUpdatedUploadedImage($file, 'images', $setting, 'images/', 'promo_popup_image');
        }

        $setting->update($data);

        return redirect()->back()->withSuccess(__('Dados atualizados com sucesso.'));
    }

    public function whatsapp()
    {
        $this->ensureSection('WhatsApp Floating Buttons');
        return view('back.platform.whatsapp');
    }

    public function updateWhatsapp(Request $request)
    {
        $this->ensureSection('WhatsApp Floating Buttons');
        $data = $request->validate([
            'admin_whatsapp_title' => 'nullable|max:255',
            'admin_support_names' => 'nullable|array',
            'admin_support_names.*' => 'nullable|max:255',
            'admin_support_phones' => 'nullable|array',
            'admin_support_phones.*' => 'nullable|max:30',
            'admin_support_labels' => 'nullable|array',
            'admin_support_labels.*' => 'nullable|max:255',
            'admin_support_messages' => 'nullable|array',
            'admin_support_messages.*' => 'nullable|max:255',
            'admin_whatsapp_phone' => 'nullable|max:30',
            'admin_whatsapp_primary_name' => 'nullable|max:255',
            'admin_whatsapp_primary_label' => 'nullable|max:255',
            'admin_whatsapp_message' => 'nullable|max:255',
            'admin_whatsapp_secondary_name' => 'nullable|max:255',
            'admin_whatsapp_secondary_phone' => 'nullable|max:30',
            'admin_whatsapp_secondary_label' => 'nullable|max:255',
            'admin_whatsapp_secondary_message' => 'nullable|max:255',
            'site_whatsapp_phone' => 'nullable|max:30',
            'site_whatsapp_attendant_name' => 'nullable|max:255',
            'site_whatsapp_attendant_photo' => 'nullable|mimes:jpeg,jpg,png,webp|max:2048',
            'site_whatsapp_support_message' => 'nullable|max:500',
            'site_whatsapp_offline_message' => 'nullable|max:500',
            'site_whatsapp_working_days' => 'nullable|array',
            'site_whatsapp_working_days.*' => 'nullable|integer|min:0|max:6',
            'site_whatsapp_working_start' => 'nullable|max:10',
            'site_whatsapp_working_end' => 'nullable|max:10',
            'site_whatsapp_message' => 'nullable|max:255',
            'site_whatsapp_position' => 'nullable|in:left,right',
        ], [
            'site_whatsapp_attendant_photo.uploaded' => 'A foto do atendente não chegou completa ao servidor. O sistema tenta otimizar automaticamente, mas se persistir envie JPG, PNG ou WEBP com até 2 MB.',
            'site_whatsapp_attendant_photo.mimes' => 'A foto do atendente deve ser JPG, PNG ou WEBP.',
            'site_whatsapp_attendant_photo.max' => 'A foto do atendente deve ter no maximo 2 MB.',
        ]);

        if (auth('admin')->id() === 1) {
            $data['admin_whatsapp_enabled'] = $request->has('admin_whatsapp_enabled') ? 1 : 0;
            $data['admin_whatsapp_secondary_enabled'] = $request->has('admin_whatsapp_secondary_enabled') ? 1 : 0;
            $data['admin_whatsapp_contacts'] = json_encode($this->normalizeAdminWhatsAppContacts($request));
        } else {
            unset(
                $data['admin_whatsapp_title'],
                $data['admin_support_names'],
                $data['admin_support_phones'],
                $data['admin_support_labels'],
                $data['admin_support_messages'],
                $data['admin_whatsapp_phone'],
                $data['admin_whatsapp_primary_name'],
                $data['admin_whatsapp_primary_label'],
                $data['admin_whatsapp_message'],
                $data['admin_whatsapp_secondary_name'],
                $data['admin_whatsapp_secondary_phone'],
                $data['admin_whatsapp_secondary_label'],
                $data['admin_whatsapp_secondary_message']
            );
        }

        unset($data['admin_support_names'], $data['admin_support_phones'], $data['admin_support_labels'], $data['admin_support_messages']);

        $data['site_whatsapp_enabled'] = $request->has('site_whatsapp_enabled') ? 1 : 0;
        $data['site_whatsapp_position'] = $data['site_whatsapp_position'] ?? 'right';
        $data['site_whatsapp_working_days'] = json_encode(array_values($request->input('site_whatsapp_working_days', [])));

        if ($file = $request->file('site_whatsapp_attendant_photo')) {
            $setting = Setting::findOrFail(1);
            $data['site_whatsapp_attendant_photo'] = ImageHelper::handleUpdatedUploadedImage($file, 'images', $setting, 'images/', 'site_whatsapp_attendant_photo');
        }

        Setting::findOrFail(1)->update($data);

        return redirect()->back()->withSuccess(__('Dados atualizados com sucesso.'));
    }

    private function normalizeAdminWhatsAppContacts(Request $request): array
    {
        $names = $request->input('admin_support_names', []);
        $phones = $request->input('admin_support_phones', []);
        $labels = $request->input('admin_support_labels', []);
        $messages = $request->input('admin_support_messages', []);
        $contacts = [];

        foreach ($phones as $index => $phone) {
            $digits = preg_replace('/\D+/', '', (string) $phone);

            if (strlen($digits) < 10) {
                continue;
            }

            $contacts[] = [
                'name' => trim((string) ($names[$index] ?? 'Suporte')),
                'phone' => $digits,
                'label' => trim((string) ($labels[$index] ?? 'Atendimento')),
                'message' => trim((string) ($messages[$index] ?? 'Olá, preciso de suporte no painel admin.')),
            ];
        }

        return $contacts;
    }

    public function oldProducts()
    {
        $this->ensureSection('Import Old Products');
        return view('back.platform.old-products');
    }

    public function importOldProducts(Request $request)
    {
        $this->ensureSection('Import Old Products');
        $request->validate([
            'csv' => 'nullable|file|mimes:csv,txt|max:10240',
            'csv_url' => 'nullable|url',
        ]);

        if (!$request->hasFile('csv') && !$request->csv_url) {
            return redirect()->back()->withError('Informe um arquivo CSV ou uma URL CSV do site antigo.');
        }

        $path = $request->hasFile('csv')
            ? $request->file('csv')->getRealPath()
            : $this->downloadImportFile($request->csv_url);

        $count = $this->importCsvProducts($path);

        return redirect()->back()->withSuccess($count . ' produtos importados para a estrutura atual.');
    }

    private function downloadImportFile(string $url): string
    {
        $response = Http::timeout(60)->get($url);
        abort_if(!$response->successful(), 422, 'Não foi possível baixar o CSV do site antigo.');

        $path = storage_path('app/temp_old_products_' . time() . '.csv');
        file_put_contents($path, $response->body());

        return $path;
    }

    private function importCsvProducts(string $path): int
    {
        $handle = fopen($path, 'r');
        abort_if(!$handle, 422, 'CSV invalido.');

        $headers = array_map(fn ($header) => Str::slug(trim((string) $header), '_'), fgetcsv($handle) ?: []);
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($headers, array_slice(array_pad($row, count($headers), null), 0, count($headers)));
            if (!$data) {
                continue;
            }

            $name = $this->firstValue($data, ['name', 'nome', 'title', 'titulo', 'produto']);
            if (!$name) {
                continue;
            }

            $categoryName = $this->firstValue($data, ['category', 'categoria']) ?: 'Importados';
            $category = Category::firstOrCreate(['name' => $categoryName], ['slug' => Str::slug($categoryName), 'status' => 1]);
            $brandName = $this->firstValue($data, ['brand', 'marca']);
            $brand = $brandName ? Brand::firstOrCreate(['name' => $brandName], ['slug' => Str::slug($brandName), 'status' => 1]) : null;
            $slug = Str::slug($this->firstValue($data, ['slug']) ?: $name);

            $photo = $this->importRemoteProductImage($this->firstValue($data, ['photo', 'foto', 'image', 'imagem', 'thumbnail']));

            Item::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $category->id,
                    'brand_id' => $brand?->id,
                    'name' => $name,
                    'sku' => $this->firstValue($data, ['sku', 'codigo', 'referencia']) ?: strtoupper(Str::random(8)),
                    'sort_details' => $this->firstValue($data, ['sort_details', 'resumo', 'descricao_curta']) ?: $name,
                    'details' => $this->firstValue($data, ['details', 'descricao', 'description']) ?: $name,
                    'photo' => $photo,
                    'thumbnail' => $photo,
                    'discount_price' => $this->moneyToDecimal($this->firstValue($data, ['discount_price', 'preco', 'price', 'valor'])),
                    'previous_price' => $this->moneyToDecimal($this->firstValue($data, ['previous_price', 'preco_antigo', 'old_price'])),
                    'stock' => (int) ($this->firstValue($data, ['stock', 'estoque', 'quantity', 'quantidade']) ?: 1),
                    'status' => 1,
                    'is_type' => 'new',
                    'item_type' => 'normal',
                ]
            );
            $count++;
        }

        fclose($handle);

        return $count;
    }

    private function firstValue(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($data[$key]) && trim((string) $data[$key]) !== '') {
                return trim((string) $data[$key]);
            }
        }

        return null;
    }

    private function moneyToDecimal(?string $value): float
    {
        $value = preg_replace('/[^\d,.\-]/', '', (string) $value);
        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        return (float) $value;
    }

    private function importRemoteProductImage(?string $url): string
    {
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return 'placeholder.png';
        }

        try {
            $response = Http::timeout(30)->get($url);
            if (!$response->successful()) {
                return 'placeholder.png';
            }

            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $filename = 'OM_' . time() . Str::random(8) . '.' . strtolower($extension);
            Storage::put('images/' . $filename, $response->body());
            $publicDir = public_path('storage/images');
            if (!is_dir($publicDir)) {
                mkdir($publicDir, 0755, true);
            }
            file_put_contents($publicDir . DIRECTORY_SEPARATOR . $filename, $response->body());

            return $filename;
        } catch (\Throwable $th) {
            return 'placeholder.png';
        }
    }

    private function ensureSection(string $section): void
    {
        $admin = auth('admin')->user();

        if ($admin && (int) $admin->id === 1) {
            return;
        }

        abort_if(!$admin || !$admin->sectionCheck($section), 403);
    }
}
