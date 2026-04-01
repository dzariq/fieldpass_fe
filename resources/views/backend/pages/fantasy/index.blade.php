@extends('backend.layouts.master')

@section('title')
    {{ __('fantasy - Admin Panel') }}
@endsection

@section('styles')
    <!-- Start datatable css -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.jqueryui.min.css">
@endsection

@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">{{ __('fantasy') }}</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><span>{{ __('All Fantasy') }}</span></li>
                </ul>
            </div>
        </div>
        <div class="col-sm-6 clearfix">
            @include('backend.layouts.partials.logout')
        </div>
    </div>
</div>
<!-- page title area end -->

<div class="main-content-inner">
    <div class="row">
        <!-- data table start -->
        <div class="col-12 mt-5">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title float-left">{{ __('fantasy') }}</h4>
                    <p class="float-right mb-2">
                        @if (auth()->user()->can('fantasy.edit'))
                            <a class="btn btn-primary text-white" href="{{ route('admin.fantasy.create') }}">
                                {{ __('Create New Fantasy') }}
                            </a>
                        @endif
                    </p>
                    <div class="clearfix"></div>
                    <div class="data-tables">
                        @include('backend.layouts.partials.messages')
                        <table id="dataTable" class="text-center">
                          <thead class="bg-light text-capitalize">
    <tr>
        <th width="5%">{{ __('Sl') }}</th>
        <th width="5%">{{ __('Season') }}</th>
        <th width="5%">{{ __('Current Matchweek/Total') }}</th>
        <th width="5%">{{ __('Credit') }}</th>
        <th width="5%">{{ __('Transfer') }}</th>
        <th width="5%">{{ __('Bench Boost') }}</th>
        <th width="5%">{{ __('Wildcard') }}</th>
        <th width="5%">{{ __('Triple Captain') }}</th>
        <th width="5%">{{ __('Same Team Limit') }}</th>
        <th width="5%">{{ __('GK Limit') }}</th>
        <th width="5%">{{ __('DF Limit') }}</th>
        <th width="5%">{{ __('MF Limit') }}</th>
        <th width="5%">{{ __('ST Limit') }}</th>
        <th width="10%">{{ __('Action') }}</th>
    </tr>
</thead>
<tbody>
   @foreach ($fantasy as $fan)
   <tr>
        <td>{{ $loop->index+1 }}</td>
        <td>{{ $fan->season }}</td>
        <td>{{ $fan->matchweek }}/{{ $fan->matchweeks }}</td>
        <td>{{ $fan->credit }}</td>
        <td>{{ $fan->transfer }}</td>
        <td>{{ $fan->benchboost }}</td>
        <td>{{ $fan->wildcard }}</td>
        <td>{{ $fan->triple }}</td>
        <td>{{ $fan->max_same_club }}</td>
        <td>{{ $fan->GK }}</td>
        <td>{{ $fan->DF }}</td>
        <td>{{ $fan->MF }}</td>
        <td>{{ $fan->ST }}</td>
        <td>
            @if (auth()->user()->can('competition.edit'))
                <a class="btn btn-success btn-sm" href="{{ route('admin.fantasy.edit', $fan->competition_id) }}">
            <i class="fa fa-edit"></i> {{ __('Edit') }}
        </a>
            @endif
        </td>
    </tr>
   @endforeach
</tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- data table end -->
    </div>
</div>
@endsection

@section('scripts')
     <!-- Start datatable js -->
     <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
     <script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
     <script src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>
     <script src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>
     <script src="https://cdn.datatables.net/responsive/2.2.3/js/responsive.bootstrap.min.js"></script>
     
     <script>
        if ($('#dataTable').length) {
            $('#dataTable').DataTable({
                responsive: true
            });
        }
     </script>
@endsection