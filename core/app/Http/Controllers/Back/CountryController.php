<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CountryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('adminlocalize');
    }

    public function index()
    {
        return view('back.country.index', [
            'datas' => Country::orderBy('status', 'desc')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('back.country.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['status'] = $request->has('status') ? 1 : 0;

        Country::create($data);

        return redirect()->route('back.country.index')->withSuccess('País cadastrado com sucesso.');
    }

    public function edit(Country $country)
    {
        return view('back.country.edit', compact('country'));
    }

    public function update(Request $request, Country $country)
    {
        $data = $this->validatedData($request, $country);
        $data['status'] = $request->has('status') ? 1 : 0;

        $country->update($data);

        return redirect()->route('back.country.index')->withSuccess('País atualizado com sucesso.');
    }

    public function status($id, $status)
    {
        Country::findOrFail($id)->update(['status' => (int) $status === 1 ? 1 : 0]);

        return redirect()->route('back.country.index')->withSuccess('Status atualizado com sucesso.');
    }

    public function destroy(Country $country)
    {
        $country->delete();

        return redirect()->route('back.country.index')->withSuccess('País excluído com sucesso.');
    }

    private function validatedData(Request $request, ?Country $country = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'max:255',
                Rule::unique('countries', 'name')->ignore($country?->id),
            ],
        ], [
            'name.required' => 'Informe o nome do país.',
            'name.unique' => 'Este país já está cadastrado.',
        ]);
    }
}
