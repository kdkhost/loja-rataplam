@extends('master.back')

@section('styles')
<style>
    .analytics-status {
        color: #6f7a8a;
        font-size: 13px;
    }
    .analytics-table td,
    .analytics-table th {
        vertical-align: middle;
        white-space: nowrap;
    }
    .analytics-table td:first-child {
        white-space: normal;
        min-width: 180px;
    }
</style>
@endsection

@section('content')

<div class="container-fluid">

    <!-- Page Heading -->

    @if(session()->has('multipledomain'))
        <div class="alert alert-danger" style="background-color: #FFE4E4;" id="license_alert">
            <strong>One Purchase Code Use in multiple domain :</strong>
            @foreach (session()->get('multipledomain') as $item)
                <p style="margin-bottom: 0px;color: #155724;">{{ $item }}</p>
            @endforeach
            <hr>
            <strong>
                {{ __('Envato not allow to install script multiple domin using one purchase code. ') }}
                <br>
                {{ __('One purched codes for one Domin.
                Author can take action any time for that.') }}
                <br>
                <hr>
                {{ __('Author Contact : geniusdevs24@gmail.com') }}
            </strong>
        </div>
    @endif

    <div class="card mb-4">
        <h3 class="mb-0 px-3 py-4"><b>{{ __('Dashboard') }}</b></h3>
    </div>


    @include('alerts.alerts')
  @php
      $analyticsDashboard = $analyticsDashboard ?? [];
      $analyticsCards = $analyticsDashboard['cards'] ?? [];
  @endphp
  <!-- Content Row -->
  <div class="row">

    <div class="col-xl-3 col-md-6">
        <div class="card card-stats card-round">
            <div class="card-body ">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-success bubble-shadow-small">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="mb-0"><b>{{ __('Total Orders') }}</b></p>
                            <h4 class="card-title">{{ $totalOrders }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card card-stats card-round">
            <div class="card-body ">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-success bubble-shadow-small">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="mb-0"><b>{{ __('Pending Orders') }}</b></p>
                            <h4 class="card-title">{{ $totalPendingOrders }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="card card-stats card-round">
            <div class="card-body ">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-success bubble-shadow-small">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="mb-0"><b>{{ __('Delivered Orders') }}</b></p>
                            <h4 class="card-title">{{$totalDeliveredOrders}}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card card-stats card-round">
            <div class="card-body ">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-success bubble-shadow-small">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="mb-0"><b>{{ __('Canceled Orders') }}</b></p>
                            <h4 class="card-title">{{$totalCanceledOrders}}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card card-stats card-round">
            <div class="card-body ">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-secondary  bubble-shadow-small">
                            <i class="far fa-chart-bar"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="mb-0"><b>{{ __('Total Product Sale') }}</b></p>
                            <h4 class="card-title">{{$totalProductSale}}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="card card-stats card-round">
            <div class="card-body ">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-secondary  bubble-shadow-small">
                            <i class="far fa-chart-bar"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="mb-0"><b>{{ __('Today Product Order') }}</b></p>
                            <h4 class="card-title">{{$totalTodayProductSale}}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card card-stats card-round">
            <div class="card-body ">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-secondary  bubble-shadow-small">
                            <i class="far fa-chart-bar"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="mb-0"><b>{{ __('This Month Sale') }}</b></p>
                            <h4 class="card-title">{{$totalCurrentMonthProductSale}}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card card-stats card-round">
            <div class="card-body ">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-secondary  bubble-shadow-small">
                            <i class="far fa-chart-bar"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="mb-0"><b>{{ __('This Year Product Sale') }}</b></p>
                            <h4 class="card-title">{{$totalLatYearProductSale}}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card card-stats card-round">
            <div class="card-body ">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-danger  bubble-shadow-small">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="mb-0"><b>{{ __('Total Earning') }}</b></p>
                            <h4 class="card-title">{{ PriceHelper::adminCurrencyPrice($totalEarning) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>



      <div class="col-xl-3 col-md-6">
        <div class="card card-stats card-round">
            <div class="card-body ">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-danger  bubble-shadow-small">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="mb-0"><b>{{ __('Today Pending Earning') }}</b></p>
                            <h4 class="card-title">{{ PriceHelper::adminCurrencyPrice($totalTodayEarning) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card card-stats card-round">
            <div class="card-body ">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-danger  bubble-shadow-small">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="mb-0"><b>{{ __('This Month Earning') }}</b></p>
                            <h4 class="card-title">{{ PriceHelper::adminCurrencyPrice($totalMonthEarning) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card card-stats card-round">
            <div class="card-body ">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-danger  bubble-shadow-small">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="mb-0"><b>{{ __('This Year Erning') }}</b></p>
                            <h4 class="card-title">{{ PriceHelper::adminCurrencyPrice($totalYearEarning) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>



        <!-- Pending Requests Card Example -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-stats card-round">
                <div class="card-body ">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                <i class="far fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="mb-0"><b>{{ __('Total Products') }}</b></p>
                                <h4 class="card-title">{{ $totalItems }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

      <div class="col-xl-3 col-md-6">
        <div class="card card-stats card-round">
            <div class="card-body ">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-info bubble-shadow-small">
                            <i class="far fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="mb-0"><b>{{ __('Total Customers') }}</b></p>
                            <h4 class="card-title">{{ $totalUsers }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

      </div>


      <div class="col-xl-3 col-md-6">
        <div class="card card-stats card-round">
            <div class="card-body ">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-info bubble-shadow-small">
                            <i class="far fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="mb-0"><b>{{ __('Total Categories') }}</b></p>
                            <h4 class="card-title">{{ $totalCategory }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card card-stats card-round">
            <div class="card-body ">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-info bubble-shadow-small">
                            <i class="far fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="mb-0"><b>{{ __('Total Brands') }}</b></p>
                            <h4 class="card-title">{{ $totalBrand }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card card-stats card-round">
            <div class="card-body ">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-info bubble-shadow-small">
                            <i class="far fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="mb-0"><b>{{ __('Total Reviews') }}</b></p>
                            <h4 class="card-title">{{ $totalReview }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card card-stats card-round">
            <div class="card-body ">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-info bubble-shadow-small">
                            <i class="far fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="mb-0"><b>{{ __('Total Transactions') }}</b></p>
                            <h4 class="card-title">{{ $totalTransaction }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>


      <div class="col-xl-3 col-md-6">
        <div class="card card-stats card-round">
            <div class="card-body ">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-info bubble-shadow-small">
                            <i class="far fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="mb-0"><b>{{ __('Total Tickets') }}</b></p>
                            <h4 class="card-title">{{ $totalTicket }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>



      <div class="col-xl-3 col-md-6">
        <div class="card card-stats card-round">
            <div class="card-body ">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-info bubble-shadow-small">
                            <i class="far fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="mb-0"><b>{{ __('Pending Tickets') }}</b></p>
                            <h4 class="card-title">{{ $totalPendingTicket }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card card-stats card-round">
            <div class="card-body ">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-info bubble-shadow-small">
                            <i class="far fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="mb-0"><b>{{ __('Open Tickets') }}</b></p>
                            <h4 class="card-title">{{ $totalTicket }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>


      <div class="col-xl-3 col-md-6">
        <div class="card card-stats card-round">
            <div class="card-body ">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-info bubble-shadow-small">
                            <i class="far fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="mb-0"><b>{{ __('Total Blogs') }}</b></p>
                            <h4 class="card-title">{{ $totalBlog }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card card-stats card-round">
            <div class="card-body ">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-info bubble-shadow-small">
                            <i class="far fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="mb-0"><b>{{ __('Total Subscribers') }}</b></p>
                            <h4 class="card-title">{{ $totalSubscriber }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="card card-stats card-round">
            <div class="card-body ">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-info  bubble-shadow-small">
                            <i class="far fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="mb-0"><b>{{ __('Total System User') }}</b></p>
                            <h4 class="card-title">{{$totalSystemUserEarning}}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>

  </div>

  <div class="card mb-4">
      <div class="card-body d-sm-flex align-items-center justify-content-between">
          <div>
              <h4 class="mb-1"><b>{{ __('Analitico publico em tempo real') }}</b></h4>
              <span class="analytics-status">{{ __('Ultima atualizacao') }}: <span id="dashboard-updated-at">{{ $analyticsDashboard['updated_at'] ?? now()->format('d/m/Y H:i:s') }}</span></span>
          </div>
          <a class="btn btn-primary btn-sm mt-3 mt-sm-0" href="{{ route('back.analytics.index') }}">
              <i class="fas fa-chart-line"></i> {{ __('Ver analitico completo') }}
          </a>
      </div>
  </div>

  <div class="row">
      <div class="col-xl-3 col-md-6">
          <div class="card card-stats card-round">
              <div class="card-body">
                  <div class="row align-items-center">
                      <div class="col-icon"><div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-eye"></i></div></div>
                      <div class="col col-stats ml-3 ml-sm-0">
                          <div class="numbers">
                              <p class="mb-0"><b>{{ __('Visualizacoes hoje') }}</b></p>
                              <h4 class="card-title" id="analytics-views-today">{{ number_format($analyticsCards['views_today'] ?? 0, 0, ',', '.') }}</h4>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
      <div class="col-xl-3 col-md-6">
          <div class="card card-stats card-round">
              <div class="card-body">
                  <div class="row align-items-center">
                      <div class="col-icon"><div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-users"></i></div></div>
                      <div class="col col-stats ml-3 ml-sm-0">
                          <div class="numbers">
                              <p class="mb-0"><b>{{ __('Visitantes hoje') }}</b></p>
                              <h4 class="card-title" id="analytics-visitors-today">{{ number_format($analyticsCards['visitors_today'] ?? 0, 0, ',', '.') }}</h4>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
      <div class="col-xl-3 col-md-6">
          <div class="card card-stats card-round">
              <div class="card-body">
                  <div class="row align-items-center">
                      <div class="col-icon"><div class="icon-big text-center icon-info bubble-shadow-small"><i class="fas fa-signal"></i></div></div>
                      <div class="col col-stats ml-3 ml-sm-0">
                          <div class="numbers">
                              <p class="mb-0"><b>{{ __('Online agora') }}</b></p>
                              <h4 class="card-title" id="analytics-online-now">{{ number_format($analyticsCards['online_now'] ?? 0, 0, ',', '.') }}</h4>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
      <div class="col-xl-3 col-md-6">
          <div class="card card-stats card-round">
              <div class="card-body">
                  <div class="row align-items-center">
                      <div class="col-icon"><div class="icon-big text-center icon-secondary bubble-shadow-small"><i class="fas fa-box-open"></i></div></div>
                      <div class="col col-stats ml-3 ml-sm-0">
                          <div class="numbers">
                              <p class="mb-0"><b>{{ __('Produtos vistos hoje') }}</b></p>
                              <h4 class="card-title" id="analytics-product-views-today">{{ number_format($analyticsCards['product_views_today'] ?? 0, 0, ',', '.') }}</h4>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
      <div class="col-xl-3 col-md-6">
          <div class="card card-stats card-round">
              <div class="card-body">
                  <div class="row align-items-center">
                      <div class="col-icon"><div class="icon-big text-center icon-warning bubble-shadow-small"><i class="fas fa-mouse-pointer"></i></div></div>
                      <div class="col col-stats ml-3 ml-sm-0">
                          <div class="numbers">
                              <p class="mb-0"><b>{{ __('Eventos hoje') }}</b></p>
                              <h4 class="card-title" id="analytics-events-today">{{ number_format($analyticsCards['events_today'] ?? 0, 0, ',', '.') }}</h4>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
      <div class="col-xl-3 col-md-6">
          <div class="card card-stats card-round">
              <div class="card-body">
                  <div class="row align-items-center">
                      <div class="col-icon"><div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-search"></i></div></div>
                      <div class="col col-stats ml-3 ml-sm-0">
                          <div class="numbers">
                              <p class="mb-0"><b>{{ __('Media SEO') }}</b></p>
                              <h4 class="card-title"><span id="analytics-seo-average">{{ number_format($analyticsCards['seo_average'] ?? 0, 0, ',', '.') }}</span>/100</h4>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
      <div class="col-xl-3 col-md-6">
          <div class="card card-stats card-round">
              <div class="card-body">
                  <div class="row align-items-center">
                      <div class="col-icon"><div class="icon-big text-center icon-danger bubble-shadow-small"><i class="fas fa-exclamation-triangle"></i></div></div>
                      <div class="col col-stats ml-3 ml-sm-0">
                          <div class="numbers">
                              <p class="mb-0"><b>{{ __('Produtos com SEO baixo') }}</b></p>
                              <h4 class="card-title" id="analytics-seo-attention">{{ number_format($analyticsCards['seo_attention'] ?? 0, 0, ',', '.') }}</h4>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
  </div>

  <!-- Content Row -->
  <div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <div class="card-title">{{__('Monthly Product Sales Report')}} </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="multipleLineChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <div class="card-title">{{__('Monthly Earnings Report')}} </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="multipleLineChart2"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="card-title">{{__('Recent Orders')}}</div>
            </div>
            <div class="card-body pb-0">
                <div class="card-body">
                    @if ($recentOrders->count() > 0)
                      <div class="gd-responsive-table">
                          <table class="table table-bordered table-striped" id="recent-orders" width="100%" cellspacing="0">
                          <thead>
                              <th>{{ __('Customer') }}</th>
                              <th>{{ __('Order ID') }}</th>
                              <th>{{ __('Payment Method') }}</th>
                              <th>{{ __('Total') }}</th>
                          </thead>
                          <tbody>
                              @foreach($recentOrders as $data)
                              <tr>
                                  <td>
                                      <a href="{{route('back.user.show',$data->user_id)}}">{{ $data->user->displayName()}}</a>
                                  </td>
                                  <td>
                                      <a href="{{route('back.order.invoice',$data->id)}}">{{ $data->transaction_number}}</a>
                                  </td>
                                  <td>
                                      {{ $data->payment_method}}
                                  </td>
                                  <td>
                                      {{$data->currency_sign}}{{PriceHelper::OrderTotal($data)}}
                                  </td>
                              </tr>
                              @endforeach
                          </tbody>
                          </table>
                      </div>

                      @else
                      <p class="d-block text-center">
                          {{ __('No Order Found') }}
                      </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

  </div>


</div>


@endsection

@section('scripts')
<script>

    multipleLineChart = document.getElementById('multipleLineChart').getContext('2d'),
    multipleLineChart2 = document.getElementById('multipleLineChart2').getContext('2d')


        var myMultipleLineChart = new Chart(multipleLineChart, {
			type: 'line',
			data: {
				labels: [{!! $order_days !!}],
				datasets: [{
					label: "Vendas de produtos",
					borderColor: "#1d7af3",
					pointBorderColor: "#FFF",
					pointBackgroundColor: "#1d7af3",
					pointBorderWidth: 2,
					pointHoverRadius: 4,
					pointHoverBorderWidth: 1,
					pointRadius: 4,
					backgroundColor: 'transparent',
					fill: true,
					borderWidth: 2,
					data: [{!! $order_sales !!}]
				}]
			},
			options : {
				responsive: true,
				maintainAspectRatio: false,
				legend: {
					display: false
				},
				tooltips: {
					bodySpacing: 4,
					mode:"nearest",
					intersect: 0,
					position:"nearest",
					xPadding:10,
					yPadding:10,
					caretPadding:10
				},
				layout:{
					padding:{left:15,right:15,top:15,bottom:15}
				}
			}
		});

        var myMultipleLineChart2 = new Chart(multipleLineChart2, {
			type: 'line',
			data: {
				labels: [{!! $earning_days !!}],
				datasets: [ {
					label: "Receita"+' {{PriceHelper::adminCurrency()}}',
					borderColor: "#f3545d",
					pointBorderColor: "#FFF",
					pointBackgroundColor: "#f3545d",
					pointBorderWidth: 2,
					pointHoverRadius: 4,
					pointHoverBorderWidth: 1,
					pointRadius: 4,
					backgroundColor: 'transparent',
					fill: true,
					borderWidth: 2,
					data: [{!!$total_incomess!!}]
				}]
			},
			options : {
				responsive: true,
				maintainAspectRatio: false,
				legend: {
					display: false
				},
				tooltips: {
					bodySpacing: 4,
					mode:"nearest",
					intersect: 0,
					position:"nearest",
					xPadding:10,
					yPadding:10,
					caretPadding:10
				},
				layout:{
					padding:{left:15,right:15,top:15,bottom:15}
				}
			}
		});

        var analyticsDashboard = @json($analyticsDashboard ?? []);
        var analyticsCharts = {};
        var analyticsColors = ['#1d7af3', '#31ce36', '#ffad46', '#f3545d', '#6861ce', '#48abf7', '#1572e8', '#8d9498'];

        function analyticsNumber(value) {
            return new Intl.NumberFormat('pt-BR').format(Number(value || 0));
        }

        function analyticsEscape(value) {
            return String(value === null || value === undefined ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function analyticsLineOptions(showLegend) {
            return {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: !!showLegend },
                tooltips: {
                    bodySpacing: 4,
                    mode: 'nearest',
                    intersect: 0,
                    position: 'nearest',
                    xPadding: 10,
                    yPadding: 10,
                    caretPadding: 10
                },
                layout: { padding: { left: 15, right: 15, top: 15, bottom: 15 } },
                scales: {
                    yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }]
                }
            };
        }

        function analyticsDoughnutOptions() {
            return {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'bottom' },
                tooltips: { bodySpacing: 4, xPadding: 10, yPadding: 10 }
            };
        }

        function analyticsCanvas(id) {
            var canvas = document.getElementById(id);
            return canvas ? canvas.getContext('2d') : null;
        }

        function setChartData(chart, labels, datasets) {
            if (!chart) return;
            chart.data.labels = labels || [];
            chart.data.datasets = datasets || [];
            chart.update();
        }

        function createAnalyticsCharts(data) {
            var views = data.views_chart || { labels: [], views: [], visitors: [] };
            var topProducts = data.top_products_chart || { labels: [], data: [] };
            var devices = data.devices_chart || { labels: [], data: [] };
            var referrers = data.referrers_chart || { labels: [], data: [] };
            var seo = data.seo_chart || { labels: [], data: [] };

            var viewsCtx = analyticsCanvas('analyticsViewsChart');
            if (viewsCtx) {
                analyticsCharts.views = new Chart(viewsCtx, {
                    type: 'line',
                    data: {
                        labels: views.labels || [],
                        datasets: [
                            {
                                label: 'Visualizacoes',
                                borderColor: '#1d7af3',
                                backgroundColor: 'rgba(29,122,243,.08)',
                                pointBackgroundColor: '#1d7af3',
                                fill: true,
                                borderWidth: 2,
                                data: views.views || []
                            },
                            {
                                label: 'Visitantes',
                                borderColor: '#31ce36',
                                backgroundColor: 'rgba(49,206,54,.08)',
                                pointBackgroundColor: '#31ce36',
                                fill: true,
                                borderWidth: 2,
                                data: views.visitors || []
                            }
                        ]
                    },
                    options: analyticsLineOptions(true)
                });
            }

            var productsCtx = analyticsCanvas('analyticsTopProductsChart');
            if (productsCtx) {
                analyticsCharts.products = new Chart(productsCtx, {
                    type: 'bar',
                    data: {
                        labels: topProducts.labels || [],
                        datasets: [{
                            label: 'Visualizacoes',
                            backgroundColor: '#1d7af3',
                            borderColor: '#1d7af3',
                            data: topProducts.data || []
                        }]
                    },
                    options: analyticsLineOptions(false)
                });
            }

            var devicesCtx = analyticsCanvas('analyticsDevicesChart');
            if (devicesCtx) {
                analyticsCharts.devices = new Chart(devicesCtx, {
                    type: 'doughnut',
                    data: {
                        labels: devices.labels || [],
                        datasets: [{ data: devices.data || [], backgroundColor: analyticsColors }]
                    },
                    options: analyticsDoughnutOptions()
                });
            }

            var referrersCtx = analyticsCanvas('analyticsReferrersChart');
            if (referrersCtx) {
                analyticsCharts.referrers = new Chart(referrersCtx, {
                    type: 'doughnut',
                    data: {
                        labels: referrers.labels || [],
                        datasets: [{ data: referrers.data || [], backgroundColor: analyticsColors }]
                    },
                    options: analyticsDoughnutOptions()
                });
            }

            var seoCtx = analyticsCanvas('analyticsSeoChart');
            if (seoCtx) {
                analyticsCharts.seo = new Chart(seoCtx, {
                    type: 'doughnut',
                    data: {
                        labels: seo.labels || [],
                        datasets: [{ data: seo.data || [], backgroundColor: ['#31ce36', '#1d7af3', '#ffad46', '#f3545d'] }]
                    },
                    options: analyticsDoughnutOptions()
                });
            }
        }

        function renderAnalyticsTables(data) {
            var productsBody = document.getElementById('analytics-top-products-table');
            var pagesBody = document.getElementById('analytics-top-pages-table');
            var products = (data.top_products_chart && data.top_products_chart.rows) ? data.top_products_chart.rows : [];
            var pages = data.top_pages || [];

            if (productsBody) {
                productsBody.innerHTML = products.length ? products.map(function (row) {
                    return '<tr><td>' + analyticsEscape(row.name) + '</td><td>' + analyticsNumber(row.views) + '</td><td>' + analyticsNumber(row.visitors) + '</td></tr>';
                }).join('') : '<tr><td colspan="3" class="text-center">{{ __('Nenhum dado encontrado') }}</td></tr>';
            }

            if (pagesBody) {
                pagesBody.innerHTML = pages.length ? pages.map(function (row) {
                    return '<tr><td><strong>' + analyticsEscape(row.title) + '</strong><br><small>' + analyticsEscape(row.path) + '</small></td><td>' + analyticsEscape(row.type) + '</td><td>' + analyticsNumber(row.views) + '</td><td>' + analyticsNumber(row.visitors) + '</td></tr>';
                }).join('') : '<tr><td colspan="4" class="text-center">{{ __('Nenhum dado encontrado') }}</td></tr>';
            }
        }

        function updateAnalyticsCards(data) {
            var cards = data.cards || {};
            var mapping = {
                'analytics-views-today': cards.views_today,
                'analytics-visitors-today': cards.visitors_today,
                'analytics-online-now': cards.online_now,
                'analytics-product-views-today': cards.product_views_today,
                'analytics-events-today': cards.events_today,
                'analytics-seo-average': cards.seo_average,
                'analytics-seo-attention': cards.seo_attention
            };

            Object.keys(mapping).forEach(function (id) {
                var element = document.getElementById(id);
                if (element) element.textContent = analyticsNumber(mapping[id]);
            });

            var updatedAt = document.getElementById('dashboard-updated-at');
            if (updatedAt && data.updated_at) updatedAt.textContent = data.updated_at;
        }

        function updateAnalyticsCharts(data) {
            var sales = data.sales_chart || { labels: [], sales: [] };
            var earnings = data.earnings_chart || { labels: [], earnings: [] };
            var views = data.views_chart || { labels: [], views: [], visitors: [] };
            var topProducts = data.top_products_chart || { labels: [], data: [] };
            var devices = data.devices_chart || { labels: [], data: [] };
            var referrers = data.referrers_chart || { labels: [], data: [] };
            var seo = data.seo_chart || { labels: [], data: [] };

            setChartData(myMultipleLineChart, sales.labels, [{
                label: 'Vendas de produtos',
                borderColor: '#1d7af3',
                pointBorderColor: '#FFF',
                pointBackgroundColor: '#1d7af3',
                pointBorderWidth: 2,
                pointHoverRadius: 4,
                pointHoverBorderWidth: 1,
                pointRadius: 4,
                backgroundColor: 'transparent',
                fill: true,
                borderWidth: 2,
                data: sales.sales || []
            }]);

            setChartData(myMultipleLineChart2, earnings.labels, [{
                label: 'Receita {{ PriceHelper::adminCurrency() }}',
                borderColor: '#f3545d',
                pointBorderColor: '#FFF',
                pointBackgroundColor: '#f3545d',
                pointBorderWidth: 2,
                pointHoverRadius: 4,
                pointHoverBorderWidth: 1,
                pointRadius: 4,
                backgroundColor: 'transparent',
                fill: true,
                borderWidth: 2,
                data: earnings.earnings || []
            }]);

            setChartData(analyticsCharts.views, views.labels, [
                {
                    label: 'Visualizacoes',
                    borderColor: '#1d7af3',
                    backgroundColor: 'rgba(29,122,243,.08)',
                    pointBackgroundColor: '#1d7af3',
                    fill: true,
                    borderWidth: 2,
                    data: views.views || []
                },
                {
                    label: 'Visitantes',
                    borderColor: '#31ce36',
                    backgroundColor: 'rgba(49,206,54,.08)',
                    pointBackgroundColor: '#31ce36',
                    fill: true,
                    borderWidth: 2,
                    data: views.visitors || []
                }
            ]);

            setChartData(analyticsCharts.products, topProducts.labels, [{
                label: 'Visualizacoes',
                backgroundColor: '#1d7af3',
                borderColor: '#1d7af3',
                data: topProducts.data || []
            }]);

            setChartData(analyticsCharts.devices, devices.labels, [{ data: devices.data || [], backgroundColor: analyticsColors }]);
            setChartData(analyticsCharts.referrers, referrers.labels, [{ data: referrers.data || [], backgroundColor: analyticsColors }]);
            setChartData(analyticsCharts.seo, seo.labels, [{ data: seo.data || [], backgroundColor: ['#31ce36', '#1d7af3', '#ffad46', '#f3545d'] }]);
            renderAnalyticsTables(data);
        }

        function refreshRealtimeDashboard() {
            fetch('{{ route('back.dashboard.realtime') }}', {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    updateAnalyticsCards(data);
                    updateAnalyticsCharts(data);
                })
                .catch(function () {});
        }

        createAnalyticsCharts(analyticsDashboard);
        updateAnalyticsCards(analyticsDashboard);
        renderAnalyticsTables(analyticsDashboard);
        setInterval(refreshRealtimeDashboard, 20000);


</script>
@endsection

