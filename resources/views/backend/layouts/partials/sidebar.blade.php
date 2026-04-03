<!-- sidebar menu area start -->
@php
$usr = Auth::guard('admin')->user();
@endphp
<div class="sidebar-menu">
    <div class="sidebar-header">
        <div class="logo">
            <a href="{{ route('admin.dashboard') }}">
                <h2 class="text-white">
                    <img class="avatar" alt="FieldPass" src="{{ asset('backend/assets/images/media/logo.png') }}" />
                </h2>
            </a>
        </div>
    </div>
    <div class="main-menu">
        <div class="menu-inner">
            <nav>
                <ul class="metismenu" id="menu">

                    <!-- DASHBOARD SECTION -->
                    @if ($usr->can('dashboard.view'))
                    <li class="active">
                        <a href="javascript:void(0)" aria-expanded="true">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                        <ul class="collapse">
                            <li class="{{ Route::is('admin.dashboard') ? 'active' : '' }}">
                                <a href="{{ route('admin.dashboard') }}">
                                    <i class="fas fa-chart-line"></i>
                                    Dashboard
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    <!-- MANAGEMENT SECTION -->
                    <li class="menu-section">
                        <span class="menu-section-text">MANAGEMENT</span>
                    </li>

                    <!-- ROLES & PERMISSIONS -->
                    @if ($usr->can('role.create') || $usr->can('role.view') || $usr->can('role.edit') || $usr->can('role.delete'))
                    <li>
                        <a href="javascript:void(0)" aria-expanded="true">
                            <i class="fas fa-shield-alt"></i>
                            <span>Roles & Permissions</span>
                        </a>
                        <ul class="collapse {{ Route::is('admin.roles.create') || Route::is('admin.roles.index') || Route::is('admin.roles.edit') || Route::is('admin.roles.show') ? 'in' : '' }}">
                            @if ($usr->can('role.view'))
                            <li class="{{ Route::is('admin.roles.index') || Route::is('admin.roles.edit') ? 'active' : '' }}">
                                <a href="{{ route('admin.roles.index') }}">
                                    <i class="fas fa-list"></i>
                                    All Roles
                                </a>
                            </li>
                            @endif
                            @if ($usr->can('role.create'))
                            <li class="{{ Route::is('admin.roles.create') ? 'active' : '' }}">
                                <a href="{{ route('admin.roles.create') }}">
                                    <i class="fas fa-plus"></i>
                                    Create Role
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <!-- ADMINS -->
                    @if ($usr->can('admin.create') || $usr->can('admin.view') || $usr->can('admin.edit') || $usr->can('admin.delete'))
                    <li>
                        <a href="javascript:void(0)" aria-expanded="true">
                            <i class="fas fa-users-cog"></i>
                            <span>Administrators</span>
                        </a>
                        <ul class="collapse {{ Route::is('admin.admins.create') || Route::is('admin.admins.index') || Route::is('admin.admins.edit') || Route::is('admin.admins.show') ? 'in' : '' }}">
                            @if ($usr->can('admin.view'))
                            <li class="{{ Route::is('admin.admins.index') || Route::is('admin.admins.edit') ? 'active' : '' }}">
                                <a href="{{ route('admin.admins.index') }}">
                                    <i class="fas fa-list"></i>
                                    All Admins
                                </a>
                            </li>
                            @endif
                            @if ($usr->can('admin.create'))
                            <li class="{{ Route::is('admin.admins.create') ? 'active' : '' }}">
                                <a href="{{ route('admin.admins.create') }}">
                                    <i class="fas fa-user-plus"></i>
                                    Create Admin
                                </a>
                            </li>
                            @endif
                            @if ($usr->can('club.create'))
                            <li>
                                <a href="{{ route('admin.players.bulk.form') }}">
                                    <i class="fa fa-file-upload"></i>
                                    <span>Bulk Upload Players</span>
                                </a>
                            </li>
                            @endif
                            @if ($usr->can('club.create'))
                            <li>
                                <a href="{{ route('admin.players.list') }}">
                                    <i class="fa fa-list"></i>
                                    <span>All Players</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <!-- FOOTBALL SECTION -->
                    <li class="menu-section">
                        <span class="menu-section-text">FOOTBALL MANAGEMENT</span>
                    </li>

                    <!-- ASSOCIATIONS -->
                    @if ($usr->can('association.create') || $usr->can('association.view') || $usr->can('association.edit') || $usr->can('association.delete'))
                    <li>
                        <a href="javascript:void(0)" aria-expanded="true">
                            <i class="fas fa-building"></i>
                            <span>Associations</span>
                        </a>
                        <ul class="collapse {{ Route::is('admin.associations.create') || Route::is('admin.associations.index') || Route::is('admin.associations.edit') || Route::is('admin.associations.show') ? 'in' : '' }}">
                            @if ($usr->can('association.view'))
                            <li class="{{ Route::is('admin.associations.index') || Route::is('admin.associations.edit') ? 'active' : '' }}">
                                <a href="{{ route('admin.associations.index') }}">
                                    <i class="fas fa-list"></i>
                                    All Associations
                                </a>
                            </li>
                            @endif
                            @if ($usr->can('association.create'))
                            <li class="{{ Route::is('admin.associations.create') ? 'active' : '' }}">
                                <a href="{{ route('admin.associations.create') }}">
                                    <i class="fas fa-plus"></i>
                                    Create Association
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <!-- CLUBS -->
                    @if (! $usr->hasRole('Club Manager') && ($usr->can('club.create') || $usr->can('club.view') || $usr->can('club.edit') || $usr->can('club.delete')))
                    <li>
                        <a href="javascript:void(0)" aria-expanded="true">
                            <i class="fas fa-shield-alt"></i>
                            <span>Clubs</span>
                        </a>
                        <ul class="collapse {{ Route::is('admin.clubs.create') || Route::is('admin.clubs.index') || Route::is('admin.clubs.edit') || Route::is('admin.clubs.show') ? 'in' : '' }}">
                            @if ($usr->can('club.view'))
                            <li class="{{ Route::is('admin.clubs.index') || Route::is('admin.clubs.edit') ? 'active' : '' }}">
                                <a href="{{ route('admin.clubs.index') }}">
                                    <i class="fas fa-list"></i>
                                    All Clubs
                                </a>
                            </li>
                            @endif
                            @if ($usr->can('club.create'))
                            <li class="{{ Route::is('admin.clubs.create') ? 'active' : '' }}">
                                <a href="{{ route('admin.clubs.create') }}">
                                    <i class="fas fa-plus"></i>
                                    Create Club
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <!-- PLAYERS -->
                    @if ($usr->can('players.create') || $usr->can('players.view') || $usr->can('players.edit') || $usr->can('players.delete'))
                    <li>
                        <a href="javascript:void(0)" aria-expanded="true">
                            <i class="fas fa-running"></i>
                            <span>Players</span>
                        </a>
                        <ul class="collapse {{ Route::is('admin.players.create') || Route::is('admin.players.index') || Route::is('admin.players.edit') || Route::is('admin.players.show') || Route::is('admin.player.lineup') ? 'in' : '' }}">
                            @if ($usr->can('players.view'))
                            <li class="{{ Route::is('admin.players.index') || Route::is('admin.players.edit') ? 'active' : '' }}">
                                <a href="{{ route('admin.players.index') }}">
                                    <i class="fas fa-list"></i>
                                    All Players
                                </a>
                            </li>
                            @endif
                            @if ($usr->can('players.create'))
                            <li class="{{ Route::is('admin.players.create') ? 'active' : '' }}">
                                <a href="{{ route('admin.players.create') }}">
                                    <i class="fas fa-user-plus"></i>
                                    Create Player
                                </a>
                            </li>
                            @endif
                            @if ($usr->can('players.create'))
                            <li class="{{ Route::is('admin.player.lineup') ? 'active' : '' }}">
                                <a href="{{ route('admin.player.lineup') }}">
                                    <i class="fas fa-users"></i>
                                    Player Lineup
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <!-- TRAINING -->
                    @if ($usr->can('training.create') || $usr->can('training.view') || $usr->can('training.delete'))
                    <li>
                        <a href="javascript:void(0)" aria-expanded="true">
                            <i class="fas fa-dumbbell"></i>
                            <span>Training</span>
                        </a>
                        <ul class="collapse {{ Route::is('admin.training.create') || Route::is('admin.training.index') || Route::is('admin.training.attributes.show') || Route::is('admin.training.show') ? 'in' : '' }}">
                            <li class="{{ Route::is('admin.training.attributes.show') ? 'active' : '' }}">
                                <a href="{{ route('admin.training.attributes.show') }}">
                                    <i class="fas fa-chart-bar"></i>
                                    Training Attributes
                                </a>
                            </li>
                            <li class="{{ Route::is('admin.training.show') ? 'active' : '' }}">
                                <a href="{{ route('admin.training.show') }}">
                                    <i class="fas fa-clipboard-list"></i>
                                    Training Management
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    <!-- COMPETITIONS SECTION -->
                    <li class="menu-section">
                        <span class="menu-section-text">COMPETITIONS & MATCHES</span>
                    </li>

                    <!-- COMPETITIONS -->
                    @if ($usr->can('competition.create') || $usr->can('competition.view') || $usr->can('competition.edit') || $usr->can('competition.delete'))
                    <li>
                        <a href="javascript:void(0)" aria-expanded="true">
                            <i class="fas fa-trophy"></i>
                            <span>Competitions</span>
                        </a>
                        <ul class="collapse {{ Route::is('admin.competitions.create') || Route::is('admin.competitions.index') || Route::is('admin.competitions.edit') || Route::is('admin.competitions.show') ? 'in' : '' }}">
                            @if ($usr->can('competition.view'))
                            <li class="{{ Route::is('admin.competitions.index') || Route::is('admin.competitions.edit') ? 'active' : '' }}">
                                <a href="{{ route('admin.competitions.index') }}">
                                    <i class="fas fa-list"></i>
                                    All Competitions
                                </a>
                            </li>
                            @endif
                            @if ($usr->can('competition.create'))
                            <li class="{{ Route::is('admin.competitions.create') ? 'active' : '' }}">
                                <a href="{{ route('admin.competitions.create') }}">
                                    <i class="fas fa-plus"></i>
                                    Create Competition
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <!-- MATCHES -->
                    @if (! $usr->hasRole('Club Manager') && ($usr->can('match.create') || $usr->can('match.view') || $usr->can('match.edit') || $usr->can('match.delete')))
                    <li>
                        <a href="javascript:void(0)" aria-expanded="true">
                            <i class="fas fa-futbol"></i>
                            <span>Matches</span>
                        </a>
                        <ul class="collapse {{ Route::is('admin.matches.create') || Route::is('admin.matches.index') || Route::is('admin.matches.edit') || Route::is('admin.matches.show') || Route::is('admin.match.checkin') ? 'in' : '' }}">
                            @if ($usr->can('match.view'))
                            <li class="{{ Route::is('admin.matches.index') || Route::is('admin.matches.edit') ? 'active' : '' }}">
                                <a href="{{ route('admin.matches.index') }}">
                                    <i class="fas fa-list"></i>
                                    All Matches
                                </a>
                            </li>
                            @endif
                            @if ($usr->can('match.create'))
                            <li class="{{ Route::is('admin.matches.create') ? 'active' : '' }}">
                                <a href="{{ route('admin.matches.create') }}">
                                    <i class="fas fa-plus"></i>
                                    Create Match
                                </a>
                            </li>
                            @endif
                            @if ($usr->can('match.create'))
                            <li class="{{ Route::is('admin.match.checkin') ? 'active' : '' }}">
                                <a href="{{ route('admin.match.checkin') }}">
                                    <i class="fas fa-check-circle"></i>
                                    Match Check-in
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <!-- COMPETITION INVITATIONS -->
                    @if ($usr->can('competition.manage_invites'))
                    <li>
                        <a href="javascript:void(0)" aria-expanded="true">
                            <i class="fas fa-envelope"></i>
                            <span>Competition Invitations</span>
                        </a>
                        <ul class="collapse {{ Route::is('admin.competition.invites.index') ? 'in' : '' }}">
                            <li class="{{ Route::is('admin.competition.invites.index') ? 'active' : '' }}">
                                <a href="{{ route('admin.competition.invites.index') }}">
                                    <i class="fas fa-inbox"></i>
                                    All Invitations
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    <li class="menu-section">
                        <span class="menu-section-text">FANTASY</span>
                    </li>

                    <!-- FANTASY -->
                    @if ($usr->can('competition.create') || $usr->can('competition.view') || $usr->can('competition.edit') || $usr->can('competition.delete'))
                    <li>
                        <a href="javascript:void(0)" aria-expanded="true">
                            <i class="fas fa-trophy"></i>
                            <span>Fantasy Football</span>
                        </a>
                        <ul class="collapse {{ Route::is('admin.fantasy.create') || Route::is('admin.fantasy.index') || Route::is('admin.fantasy.edit') || Route::is('admin.fantasy.show') ? 'in' : '' }}">
                            @if ($usr->can('competition.view'))
                            <li class="{{ Route::is('admin.fantasy.index') || Route::is('admin.fantasy.edit') ? 'active' : '' }}">
                                <a href="{{ route('admin.fantasy.index') }}">
                                    <i class="fas fa-list"></i>
                                    All Fantasy
                                </a>
                            </li>
                            @endif
                            @if ($usr->can('competition.create'))
                            <li class="{{ Route::is('admin.fantasy.create') ? 'active' : '' }}">
                                <a href="{{ route('admin.fantasy.create') }}">
                                    <i class="fas fa-plus"></i>
                                    Create Fantasy
                                </a>
                            </li>
                            @endif
                            @if ($usr->can('competition.create'))
                            <li class="{{ Route::is('admin.fantasy.create') ? 'active' : '' }}">
                                <a href="{{ route('admin.fantasy.points') }}">
                                    <i class="fas fa-plus"></i>
                                    Fantasy Points Settings
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    @endif

                </ul>
            </nav>
        </div>
    </div>
</div>
<!-- sidebar menu area end -->

<style>
    /* Professional Sidebar Styling */
    .sidebar-menu {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 2px 0 15px rgba(0, 0, 0, 0.1);
    }

    .sidebar-header .logo h2 {
        font-weight: 600;
        letter-spacing: 1px;
    }

    .sidebar-header .logo .user-thumb {
        width: 35px;
        height: 35px;
        border-radius: 8px;
        margin-right: 10px;
    }

    /* Menu Sections */
    .menu-section {
        margin: 20px 0 10px 0;
        padding: 0 20px;
    }

    .menu-section-text {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: rgba(255, 255, 255, 0.6);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 5px;
        display: block;
    }

    /* Main Menu Items */
    .metismenu>li>a {
        padding: 12px 20px;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 500;
        border-radius: 0;
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
    }

    .metismenu>li>a:hover,
    .metismenu>li.active>a {
        background: rgba(255, 255, 255, 0.1);
        color: #ffffff;
        border-left: 3px solid #ffffff;
        box-shadow: inset 0 0 20px rgba(255, 255, 255, 0.1);
    }

    .metismenu>li>a i {
        width: 20px;
        text-align: center;
        margin-right: 12px;
        font-size: 16px;
    }

    /* Submenu Items */
    .metismenu ul li a {
        padding: 10px 20px 10px 50px;
        color: rgba(255, 255, 255, 0.8);
        font-size: 13px;
        font-weight: 400;
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
    }

    .metismenu ul li a:hover,
    .metismenu ul li.active a {
        background: rgba(255, 255, 255, 0.08);
        color: #ffffff;
        border-left: 3px solid rgba(255, 255, 255, 0.5);
        padding-left: 53px;
    }

    .metismenu ul li a i {
        width: 16px;
        text-align: center;
        margin-right: 8px;
        font-size: 12px;
        opacity: 0.8;
    }

    /* Collapse Animation */
    .metismenu .collapse.in {
        background: rgba(0, 0, 0, 0.1);
        border-radius: 0 0 8px 8px;
        margin-bottom: 5px;
    }

    /* Active State Improvements */
    .metismenu>li.active {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        margin: 2px 10px;
    }

    .metismenu>li.active>a {
        border-radius: 8px;
        background: transparent;
        border-left: none;
    }

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

    /* Scrollbar Styling */
    .main-menu::-webkit-scrollbar {
        width: 4px;
    }

    .main-menu::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
    }

    .main-menu::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 2px;
    }

    .main-menu::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.5);
    }
</style>