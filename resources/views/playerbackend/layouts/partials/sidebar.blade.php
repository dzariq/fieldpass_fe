 <!-- sidebar menu area start -->
 @php
     $usr = Auth::guard('player')->user();

 @endphp
 <div class="sidebar-menu">
    <div class="sidebar-header">
        <div class="logo">
            <a href="{{ route('admin.dashboard') }}">
                <h2 class="text-white">Player</h2> 
            </a>
        </div>
    </div>
    <div class="main-menu">
        <div class="menu-inner">
            <nav>
                <ul class="metismenu" id="menu">

                    <li class="active">
                        <a href="javascript:void(0)" aria-expanded="true"><i class="ti-dashboard"></i><span>dashboard</span></a>
                        <ul class="collapse">
                            <li class="{{ Route::is('player.dashboard') ? 'active' : '' }}"><a href="{{ route('player.dashboard') }}">Dashboard</a></li>
                        </ul>
                    </li>

                </ul>
            </nav>
        </div>
    </div>
</div>
<!-- sidebar menu area end -->

<style>
    /* Mobile: sidebar hidden when collapsed, visible when nav-btn opens it */
    @media (max-width: 992px) {
        .sidebar-menu {
            transition: transform 0.3s ease, left 0.3s ease;
            z-index: 999;
        }
        .page-container.sbar_collapsed .sidebar-menu {
            transform: translateX(-100%);
            left: 0 !important;
        }
        .page-container:not(.sbar_collapsed) .sidebar-menu {
            transform: translateX(0);
            left: 0 !important;
        }
    }
</style>