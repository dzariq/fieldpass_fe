@extends('playerbackend.layouts.master')

@section('title')
{{ __('Match Details - Admin Panel') }}
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
                <h4 class="page-title pull-left">{{ $match->name }}</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><span>{{ __('Match Details') }}</span></li>
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
        <ul class="nav nav-tabs" id="matchTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="overview-tab" data-toggle="tab" href="#overview" role="tab" aria-controls="overview" aria-selected="true">Overview</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="actions-tab" data-toggle="tab" href="#actions" role="tab" aria-controls="actions" aria-selected="false">Actions</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content mt-3" id="matchTabsContent">
            <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                <h3>Overview</h3>
                <div class="container mt-4">
                    <div class="row align-items-center">
                        <!-- Home Club Logo and Score -->
                        <div class="col-3 text-center">
                            <img src="home_club_logo.png" alt="Home Club Logo" class="img-fluid" style="max-width: 100px;">
                            <h4>Home Club</h4>
                            <p class="h3">2</p>
                        </div>

                        <!-- Match Info (vs) -->
                        <div class="col-1 text-center">
                            <h3>VS</h3>
                        </div>

                        <!-- Away Club Logo and Score -->
                        <div class="col-3 text-center">
                            <img src="away_club_logo.png" alt="Away Club Logo" class="img-fluid" style="max-width: 100px;">
                            <h4>Away Club</h4>
                            <p class="h3">1</p>
                        </div>
                    </div>
                </div>

            </div>

            <div class="tab-pane fade" id="actions" role="tabpanel" aria-labelledby="actions-tab">
                <h3>Actions</h3>
                <p>List of actions...</p>
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