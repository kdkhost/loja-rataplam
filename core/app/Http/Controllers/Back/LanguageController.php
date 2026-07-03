<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Language;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LanguageController extends Controller
{
    /**
     * Constructor Method.
     *
     * Setting Authentication
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('adminlocalize');
    }

    public function index()
    {
        $datas = Language::query()
            ->where(function ($query) {
                $query->whereIn('language', ['English', 'PortuguÃªs', 'Portugues', 'Portuguese'])
                    ->orWhereIn('name', ['en', 'en_website', 'en_dashboard', 'pt', 'pt_website', 'pt_dashboard']);
            })
            ->orderBy('type')
            ->orderByDesc('is_default')
            ->orderBy('language')
            ->get();
        return view('back.language.index', compact('datas'));
    }

    public function create()
    {
        $data = $this->languageTemplate();
        $lang = $this->readLanguageFile($data);

        return view('back.language.create', compact('data', 'lang'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'language' => 'required|unique:languages,language',
        ]);

        if (!$this->isAllowedLanguageName($request->language)) {
            return redirect()->back()->withErrors(__('Somente Portugues e Ingles podem ser cadastrados como idiomas do sistema.'));
        }

        $new = null;
        $input = $request->all();
        $data = new Language();

        $name = time() . Str::random(8);
        $data->name = Str::random(8);
        $data->language = $request->language;
        $data->file = $name . '.json';
        $data->type = "Website";
        $data->save();

        $languages = $this->readLanguageFile($this->languageTemplate());

        foreach ($languages as $key => $value) {
            $n = str_replace("_", " ", $key);
            $new[$n] = $value;
        }
        $mydata = json_encode($new);
        file_put_contents(resource_path() . '/lang/' . $data->file, $mydata);
        return redirect()->route('back.language.index')->withSuccess(__('Language Added Successfully.'));
    }

    public function edit($id)
    {
        $data = Language::findOrFail($id);

        if (!$this->isAllowedLanguageName($data->language)) {
            return redirect()->route('back.language.index')->withErrors(__('Somente Portugues e Ingles podem ser editados.'));
        }

        if (!$this->languageFileExists($data)) {
            return redirect()->route('back.language.index')->withErrors(__('Arquivo de idioma não encontrado. Selecione Português ou Inglês ativo.'));
        }

        $lang = $this->readLanguageFile($data);
        return view('back.language.edit', compact('data', 'lang'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        //--- Logic Section
        $new = null;
        $data = Language::findOrFail($id);

        if (!$this->isAllowedLanguageName($data->language) || !$this->languageFileExists($data)) {
            return redirect()->route('back.language.index')->withErrors(__('Somente Portugues e Ingles com arquivo valido podem ser atualizados.'));
        }

        if (file_exists(resource_path() . '/lang/' . $data->file)) {
            unlink(resource_path() . '/lang/' . $data->file);
        }
        $name = time() . Str::random(8);
        $data->name = $name;
        $data->language = $request->language;
        $data->file = $name . '.json';
        $data->update();

        $keys = is_array($request->keys) ? $request->keys : [];
        $values = is_array($request->values) ? $request->values : [];
        foreach ($keys as $index => $key) {
            $n = str_replace("_", " ", $key);
            $new[$n] = $values[$index] ?? '';
        }
        $mydata = json_encode($new ?: [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        file_put_contents(resource_path() . '/lang/' . $data->file, $mydata);
        //--- Logic Section Ends

        return redirect()->back()->withSuccess(__('Language Updated Successfully.'));
    }

    public function status($id, $status)
    {
        $data = Language::findOrFail($id);

        if (!$this->isAllowedLanguageName($data->language)) {
            return redirect()->route('back.language.index')->withErrors(__('Somente Portugues e Ingles podem ser ativados.'));
        }

        if ((int) $status === 0) {
            $activeCount = Language::whereType($data->type)
                ->where('status', 1)
                ->whereIn('language', ['English', 'PortuguÃªs', 'Portugues', 'Portuguese'])
                ->count();

            if ($activeCount <= 1 && $data->status == 1) {
                return redirect()->route('back.language.index')->withErrors(__('Pelo menos um idioma deve permanecer ativo.'));
            }

            $data->status = 0;
            $data->is_default = 0;
            $data->save();

            $fallback = Language::whereType($data->type)
                ->where('status', 1)
                ->whereIn('language', ['English', 'PortuguÃªs', 'Portugues', 'Portuguese'])
                ->orderByRaw("language = 'PortuguÃªs' desc")
                ->first();

            if ($fallback && !Language::whereType($data->type)->where('is_default', 1)->exists()) {
                $fallback->is_default = 1;
                $fallback->save();
                $this->syncLocaleDefaults($fallback);
            }

            return redirect()->route('back.language.index')->withSuccess(__('Language Updated Successfully.'));
        }

        $get = Language::whereType($data->type)->where('id', "!=", $id)->get();
        $data->status = 1;
        $data->is_default = 1;

        foreach ($get as $lang) {
            $lang->is_default = 0;
            $lang->update();
        }

        $data->update();
        $this->syncLocaleDefaults($data);

        return redirect()->route('back.language.index')->withSuccess(__('Language Updated Successfully.'));
    }

    protected function isAllowedLanguageName(string $language): bool
    {
        $language = Str::ascii(strtolower(trim($language)));

        return str_contains($language, 'english') || str_contains($language, 'portugu');
    }

    protected function languageFileExists(Language $language): bool
    {
        return $language->file && file_exists(resource_path('lang/' . $language->file));
    }

    protected function readLanguageFile(Language $language): array
    {
        if (!$this->languageFileExists($language)) {
            return [];
        }

        return json_decode(file_get_contents(resource_path('lang/' . $language->file)), true) ?: [];
    }

    protected function languageTemplate(): Language
    {
        $language = Language::query()
            ->where('status', 1)
            ->get()
            ->first(fn (Language $language) => $this->isAllowedLanguageName($language->language) && $this->languageFileExists($language));

        return $language ?: Language::query()->get()->first(fn (Language $language) => $this->languageFileExists($language));
    }

    protected function syncLocaleDefaults(Language $language): void
    {
        $locale = strtolower(str_replace('-', '_', $language->name ?: pathinfo($language->file, PATHINFO_FILENAME)));
        $setting = Setting::first();

        if (!$setting) {
            return;
        }

        if ($locale === 'pt' || str_starts_with($locale, 'pt_')) {
            Currency::query()->update(['is_default' => 0]);
            Currency::updateOrCreate(
                ['name' => 'BRL'],
                ['sign' => 'R$', 'value' => 1, 'is_default' => 1, 'status' => 1]
            );
            $setting->update([
                'currency_direction' => 1,
                'is_decimal' => 1,
                'decimal_separator' => ',',
                'thousand_separator' => '.',
            ]);
        }
    }

    public function destroy($id)
    {
        $data = Language::find($id);
        if($data->is_default == 1 || $data->id == 1){
            return redirect()->back()->withSuccess(__("You can't delete this language"));
        }
        
        if (file_exists(resource_path() . '/lang/' . $data->file)) {
            unlink(resource_path() . '/lang/' . $data->file);
        }
        $data->delete();
        return redirect()->back()->withSuccess(__('Language Delete Successfully.'));
    }

}
