@php
    $headerClubForManager = null;
    $adminHeader = Auth::guard('admin')->user();
    if ($adminHeader && $adminHeader->hasRole('Club Manager')) {
        $headerClubForManager = $adminHeader->clubs()->orderBy('name')->first();
    }
@endphp
<!-- header area start -->
<div class="header-area">
    <div class="row align-items-center">
        <!-- nav and search button -->
        <div class="col-md-6 col-sm-8 clearfix header-left-with-nav">
            <button type="button" class="nav-btn" aria-label="Toggle sidebar">
                <span></span>
                <span></span>
                <span></span>
            </button>
            @if ($headerClubForManager && $adminHeader->can('club.edit'))
                <div class="header-club-manager-block">
                    <a href="{{ route('admin.clubs.edit', $headerClubForManager->id) }}" class="header-club-manager-brand" title="{{ __('Edit club profile') }}">
                        <img
                            src="{{ $headerClubForManager->avatar ? asset($headerClubForManager->avatar) : asset('backend/assets/images/default-avatar.png') }}"
                            alt="{{ $headerClubForManager->name }}"
                            class="header-club-logo"
                            width="44"
                            height="44"
                        >
                        <span class="header-club-name d-none d-md-inline">{{ $headerClubForManager->name }}</span>
                    </a>
                    <a href="{{ route('admin.clubs.edit', $headerClubForManager->id) }}" class="btn btn-sm btn-primary header-club-edit-btn">
                        {{ __('Edit club profile') }}
                    </a>
                </div>
            @endif
        </div>
        <!-- profile info & task notification -->
        <div class="col-md-6 col-sm-4 clearfix">
            <ul class="notification-area pull-right">
                <li id="full-view"><i class="ti-fullscreen"></i></li>
                <li id="full-view-exit"><i class="ti-zoom-out"></i></li>
                <li class="dropdown">
                    <i class="ti-bell dropdown-toggle" data-toggle="dropdown">
                        <span>{{count(auth()->user()->unreadNotifications)}}</span>
                    </i>
                    <div class="dropdown-menu bell-notify-box notify-box">
                        <span class="notify-title">You have {{count(auth()->user()->unreadNotifications)}} new notifications
                            <a href="{{ route('admin.notifications') }}">view all</a>
                        </span>
                        <div class="nofity-list">
                            <ul>
                                @foreach (auth()->user()->unreadNotifications as $key=>$notification)
                                    @if($key > 5)
                                        break;
                                    @endif
                                    <li>
                                    <a onclick="markNotificationAsRead('{{ $notification->id }}', '{{ $notification->data['url'] }}')" href="javascript:void(0);" class="notify-item">
                                    <div class="notify-thumb"><i class="ti-key btn-danger"></i></div>
                                            <div class="notify-text">
                                                <p>{{ $notification->data['message'] }}</p>
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>

                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- header area end -->
<style>
    /* Prevent overlap: main header bar stays in flow, doesn't cover page-title-area */
    .main-content > .header-area {
        position: relative;
        z-index: 50;
        margin-bottom: 0;
    }
    .main-content > .page-title-area {
        margin-top: 0;
        padding-top: 0;
    }
    /* Ensure burger menu is always visible and tappable */
    .header-area .nav-btn {
        display: inline-block;
        min-width: 44px;
        min-height: 44px;
        padding: 10px;
        border: none;
        background: transparent;
        vertical-align: middle;
    }
    .header-area .nav-btn span {
        display: block !important;
        width: 22px;
        height: 2px;
        background: #303641 !important;
        margin: 4px 0;
    }
    .header-left-with-nav {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem 0.75rem;
    }
    .header-club-manager-block {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem 0.75rem;
        margin-left: 0.25rem;
        min-width: 0;
    }
    .header-club-manager-brand {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        color: #303641;
        font-weight: 600;
        font-size: 0.9rem;
        max-width: min(220px, 42vw);
    }
    .header-club-manager-brand:hover {
        color: #5768ad;
        text-decoration: none;
    }
    .header-club-logo {
        width: 44px;
        height: 44px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid rgba(48, 54, 65, 0.12);
        flex-shrink: 0;
        background: #fff;
    }
    .header-club-name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .header-club-edit-btn {
        white-space: nowrap;
    }
    @media (max-width: 576px) {
        .header-club-edit-btn {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    }
</style>