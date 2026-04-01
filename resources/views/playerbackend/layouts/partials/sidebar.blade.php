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
                        <ul class="collapse">
                            <li class="{{ Route::is('player.details') ? 'active' : '' }}">
                                <a href="{{ route('player.details', ['id' => $usr->id]) }}">My Performance</a>
                            </li>
                        </ul>
                    </li>

                </ul>
            </nav>
        </div>
    </div>
</div>
<!-- sidebar menu area end -->