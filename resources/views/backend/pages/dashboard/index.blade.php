
@extends('backend.layouts.master')

@section('title')
Dashboard Page - Admin Panel
@endsection


@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Dashboard</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li><span>Dashboard</span></li>
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
    @if(Auth::guard('admin')->user())
    <div class="dashboard-welcome">
        <h2>Welcome back, {{ Auth::guard('admin')->user()->name }}</h2>
        <p>Use the menu to manage associations, clubs, competitions, and players.</p>
    </div>
    @endif

    @if(Auth::guard('admin')->user() && Auth::guard('admin')->user()->can('association.create'))
    <div class="row">
        <div class="col-12 mt-3">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title">Demo Data</h4>
                    <p class="mb-3">
                        Status:
                        @if(!empty($demoEnabled))
                            <strong class="text-success">Enabled</strong>
                        @else
                            <strong class="text-muted">Disabled</strong>
                        @endif
                    </p>

                    @if(!empty($demoEnabled))
                        <form method="POST" action="{{ route('admin.demo.disable') }}">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                Disable demo data (rollback)
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.demo.enable') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                Enable demo data (populate)
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="dashboard-empty-state">
        <div class="empty-icon">
            <i class="fas fa-tachometer-alt"></i>
        </div>
        <h3>Dashboard overview</h3>
        <p>Your main dashboard content will appear here. Use the sidebar to open Association or Club dashboard for stats and competitions.</p>
    </div>
</div>
@endsection