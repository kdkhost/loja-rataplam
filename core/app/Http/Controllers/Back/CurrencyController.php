<?php

namespace App\Http\Controllers\Back;

use App\{
    Models\Currency,
    Repositories\Back\CurrencyRepository,
    Http\Requests\CurrencyRequest,
    Http\Controllers\Controller
};

class CurrencyController extends Controller
{
    /**
     * Constructor Method.
     *
     * Setting Authentication
     *
     * @param  \App\Repositories\Back\CurrencyRepository $repository
     *
     */
    public function __construct(CurrencyRepository $repository)
    {
        $this->middleware('auth:admin');
        $this->middleware('adminlocalize');
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('back.currency.index',[
            'datas' => Currency::whereIn('name', ['BRL', 'USD'])
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('back.currency.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CurrencyRequest $request)
    {
        if (!in_array(strtoupper($request->name), ['BRL', 'USD'], true)) {
            return redirect()->back()->withErrors(__('Somente BRL e USD podem ser cadastrados como moedas do sistema.'));
        }

        $this->repository->store($request);
        return redirect()->route('back.currency.index')->withSuccess(__('New Currency Added Successfully.'));
    }

    /**
     * Change the status for editing the specified resource.
     *
     * @param  int  $id
     * @param  int  $status
     * @return \Illuminate\Http\Response
     */
    public function status($id,$status)
    {
        $currency = Currency::findOrFail($id);

        if (!in_array(strtoupper($currency->name), ['BRL', 'USD'], true)) {
            return redirect()->route('back.currency.index')->withErrors(__('Somente BRL e USD podem ser ativadas.'));
        }

        if ((int) $status === 0) {
            $activeCount = Currency::whereIn('name', ['BRL', 'USD'])->where('status', 1)->count();

            if ($activeCount <= 1 && $currency->status == 1) {
                return redirect()->route('back.currency.index')->withErrors(__('Pelo menos uma moeda deve permanecer ativa.'));
            }

            $currency->update(['status' => 0, 'is_default' => 0]);

            if (!Currency::where('is_default', 1)->exists()) {
                $fallback = Currency::whereIn('name', ['BRL', 'USD'])->where('status', 1)->orderByRaw("name = 'BRL' desc")->first();
                if ($fallback) {
                    $fallback->update(['is_default' => 1]);
                }
            }

            return redirect()->route('back.currency.index')->withSuccess(__('Status Updated Successfully.'));
        }

        $currency->update(['status' => 1, 'is_default' => 1]);
        Currency::where('id','!=',$id)->update(['is_default' => 0]);
        return redirect()->route('back.currency.index')->withSuccess(__('Status Updated Successfully.'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Currency $currency)
    {
        return view('back.currency.edit',compact('currency'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CurrencyRequest $request, Currency $currency)
    {
        if (!in_array(strtoupper($request->name), ['BRL', 'USD'], true)) {
            return redirect()->back()->withErrors(__('Somente BRL e USD podem ser cadastrados como moedas do sistema.'));
        }

        $this->repository->update($currency, $request);
        return redirect()->route('back.currency.index')->withSuccess(__('Currency Updated Successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Currency $currency)
    {
        if ($currency->is_default == 1 || (Currency::whereIn('name', ['BRL', 'USD'])->where('status', 1)->count() <= 1 && $currency->status == 1)) {
            return redirect()->back()->withErrors(__('Não é possível excluir a moeda padrão ou a última moeda ativa.'));
        }

        $this->repository->delete($currency);
        return redirect()->route('back.currency.index')->withSuccess(__('Currency Deleted Successfully.'));
    }
}
