
@extends('backend.layouts.master')

@section('title')
Dashboard Page - Admin Panel
@endsection

@section('styles')
<style>
    :root {
        --fp-card-radius: 14px;
        --fp-card-shadow: 0 10px 30px rgba(16, 24, 40, 0.08);
        --fp-border: 1px solid rgba(16, 24, 40, 0.10);
    }

    .fp-dashboard {
        padding: 14px 0 28px;
    }

    .fp-dashboard-header {
        background: #fff;
        border: var(--fp-border);
        border-radius: var(--fp-card-radius);
        box-shadow: var(--fp-card-shadow);
        padding: 14px;
        margin-bottom: 14px;
    }

    .fp-dashboard-header__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .fp-dashboard-title {
        margin: 0;
        font-weight: 700;
        font-size: 1.1rem;
        line-height: 1.2;
        color: #0f172a;
        letter-spacing: -0.01em;
    }

    .fp-dashboard-subtitle {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 0.9rem;
        line-height: 1.35;
    }

    .fp-breadcrumbs {
        margin: 10px 0 0;
        padding: 0;
        list-style: none;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        color: #64748b;
        font-size: 0.82rem;
    }
    .fp-breadcrumbs li {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .fp-breadcrumbs li + li:before {
        content: "•";
        color: rgba(100, 116, 139, 0.7);
        margin-right: 6px;
    }
    .fp-breadcrumbs a {
        color: #2563eb;
        text-decoration: none;
    }
    .fp-breadcrumbs a:hover {
        text-decoration: underline;
    }

    .fp-dashboard-header__actions {
        margin-left: auto;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .fp-dashboard-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 14px;
    }

    .fp-card {
        background: #fff;
        border: var(--fp-border);
        border-radius: var(--fp-card-radius);
        box-shadow: var(--fp-card-shadow);
    }

    .fp-card__body {
        padding: 14px;
    }

    .fp-card__title {
        margin: 0 0 8px;
        font-weight: 700;
        color: #0f172a;
        font-size: 1rem;
        letter-spacing: -0.01em;
    }

    .fp-empty {
        text-align: center;
        padding: 22px 14px;
    }
    .fp-empty__icon {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        margin: 0 auto 10px;
        background: rgba(37, 99, 235, 0.10);
        color: #2563eb;
        font-size: 22px;
    }
    .fp-empty h3 {
        margin: 0 0 6px;
        font-weight: 800;
        color: #0f172a;
        font-size: 1.05rem;
        letter-spacing: -0.01em;
    }
    .fp-empty p {
        margin: 0;
        color: #64748b;
        font-size: 0.92rem;
        line-height: 1.45;
    }

    /* Slightly tighter spacing on very small screens */
    @media (max-width: 360px) {
        .fp-dashboard-header,
        .fp-card__body {
            padding: 12px;
        }
    }

    /* Allow a 2-col layout on larger screens without breaking mobile */
    @media (min-width: 992px) {
        .fp-dashboard-grid {
            grid-template-columns: 1fr 1fr;
            align-items: start;
        }
    }
</style>
@endsection


@section('admin-content')

<div class="main-content-inner">
    <div class="fp-dashboard">
        <div class="fp-dashboard-header">
            <div class="fp-dashboard-header__top">
                <div>
                    <h1 class="fp-dashboard-title">Dashboard</h1>
                    @if(Auth::guard('admin')->user())
                        <p class="fp-dashboard-subtitle mb-0">
                            Welcome back, {{ Auth::guard('admin')->user()->name }}
                        </p>
                    @endif
                    <ul class="fp-breadcrumbs" aria-label="Breadcrumb">
                        <li><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li><span>Dashboard</span></li>
                    </ul>
                </div>
                <div class="fp-dashboard-header__actions">
                    @include('backend.layouts.partials.logout')
                </div>
            </div>
        </div>

    @if(Auth::guard('admin')->user())
        @can('association.create')
            <div class="fp-card mb-3">
                <div class="fp-card__body">
                    <div class="fp-card__title">Quick start</div>
                    <div class="text-muted" style="font-size: 0.92rem; line-height: 1.45;">
                        Use the menu to manage associations, clubs, competitions, and players.
                    </div>
                </div>
            </div>
        @else
            <div class="fp-card mb-3">
                <div class="fp-card__body">
                    <div class="fp-card__title">Quick start</div>
                    <div class="text-muted" style="font-size: 0.92rem; line-height: 1.45;">
                        Use the sidebar to open your association or club areas.
                    </div>
                </div>
            </div>
        @endcan
    @endif

    @include('backend.layouts.partials.messages')

    @if(Auth::guard('admin')->user() && Auth::guard('admin')->user()->can('association.create'))
        <div class="fp-dashboard-grid mb-3">
            <div class="fp-card">
                <div class="fp-card__body">
                    <div class="d-flex align-items-start justify-content-between" style="gap: 12px;">
                        <div>
                            <div class="fp-card__title">Demo Data</div>
                            <div class="text-muted" style="font-size: 0.92rem; line-height: 1.45;">
                                Status:
                                @if(!empty($demoEnabled))
                                    <strong class="text-success">Enabled</strong>
                                @else
                                    <strong class="text-muted">Disabled</strong>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            @if(!empty($demoEnabled))
                                <span class="badge badge-success">On</span>
                            @else
                                <span class="badge badge-secondary">Off</span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-3">
                        @if(!empty($demoEnabled))
                            <form method="POST" action="{{ route('admin.demo.disable') }}">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-block">
                                    Disable demo data (rollback)
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.demo.enable') }}">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-block">
                                    Enable demo data (populate)
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

        <div class="fp-card">
            <div class="fp-card__body">
                <div class="fp-empty">
                    <div class="fp-empty__icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <h3>Dashboard overview</h3>
                    @can('association.create')
                        <p>Your main dashboard content will appear here. Use the sidebar to open Association or Club areas for stats and competitions.</p>
                    @else
                        <p>Use the sidebar for the areas your role can access.</p>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection