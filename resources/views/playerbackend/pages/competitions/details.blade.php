@extends('playerbackend.layouts.master')

@section('title')
{{ __('Competition Detail - Admin Panel') }}
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
                <h4 class="page-title pull-left">{{ $competition->name }}</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><span>{{ __('Competition Details') }}</span></li>
                </ul>
            </div>
        </div>
        <div class="col-sm-6 clearfix">
            @include('playerbackend.layouts.partials.logout')
        </div>
    </div>
</div>
<!-- page title area end -->

<div class="main-content-inner">
    <div class="container mt-4">
        <!-- Nav Tabs -->
        <ul class="nav nav-tabs" id="competitionTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="overview-tab" data-toggle="tab" href="#overview" role="tab" aria-controls="overview" aria-selected="true">Overview</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="clubs-tab" data-toggle="tab" href="#clubs" role="tab" aria-controls="clubs" aria-selected="false">Clubs</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="invites-tab" data-toggle="tab" href="#invites" role="tab" aria-controls="invites" aria-selected="false">Matches</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content mt-3" id="competitionTabsContent">
            <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                <h3>Overview</h3>
                <p>Competition overview content goes here.</p>
            </div>
            <div class="tab-pane fade" id="clubs" role="tabpanel" aria-labelledby="clubs-tab">
                    <div class="col-12 mt-5">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title float-left">{{ __('Clubs') }}</h4>
                                <div class="clearfix"></div>
                                <div class="data-tables">
                                    @include('playerbackend.layouts.partials.messages')
                                    <table id="dataTable" style="width:100%" class="text-center">
                                        <thead class="bg-light text-capitalize">
                                            <tr>
                                                <th width="5%">{{ __('Sl') }}</th>
                                                <th width="10%">{{ __('Club Name') }}</th>
                                                <th width="10%">&nbsp;</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($competition->clubs()->wherePivot('status', 'ACTIVE')->get() as $club)
                                            <tr>
                                                <td>{{ $loop->index+1 }}</td>
                                                <td>{{ $club->name }}</td>
                                                <td>&nbsp;</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="tab-pane fade" id="invites" role="tabpanel" aria-labelledby="invites-tab">
                <h3>Matches</h3>
                <p>List of invited clubs...</p>
            </div>
        </div>
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