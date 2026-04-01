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
    }
</style>