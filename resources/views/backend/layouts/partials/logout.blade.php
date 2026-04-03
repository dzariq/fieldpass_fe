@php
$usr = Auth::guard('admin')->user();
$club = $usr->clubs()->first();
$association = $usr->associations()->first();

if ($association) {
    $competitions = $association->competitions()->get();
} elseif ($club) {
    $competitions = collect();

    foreach ($usr->clubs as $userClub) {
        $competitions = $competitions->merge($userClub->competitions);
    }

    $competitions = $competitions->unique('id');
} else {
    // Superadmin / global admins: no org scope — dropdown still renders if they have competition.details
    $competitions = collect();
}

$defaultAvatar = asset('backend/assets/images/default-avatar.png');
// Always show the admin avatar in the header (matches Edit Admin page).
$headerAvatarSrc = !empty($usr->avatar) ? asset($usr->avatar) : $defaultAvatar;
@endphp

<header class="admin-header">
    <div class="header-container">
        <!-- Left Section - Competitions -->
        @if ($usr->can('competition.details'))
        <div class="header-left">
            <div class="competitions-dropdown">
                <button class="dropdown-trigger competitions-dropdown-trigger" type="button" data-toggle="dropdown" aria-expanded="false" title="{{ __('Switch competition') }}" aria-label="{{ __('Open competitions menu') }}">
                    <svg class="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <span class="dropdown-text dropdown-text--full d-none d-md-inline">{{ __('Competitions') }}</span>
                    <span class="dropdown-text dropdown-text--short d-inline d-md-none">{{ __('Competitions') }}</span>
                    <svg class="chevron-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="dropdown-menu competitions-menu">
                    <div class="dropdown-header">
                        <h6>Available Competitions</h6>
                    </div>
                    @forelse($competitions as $competition)
                    <a class="dropdown-item" href="{{ route('admin.competition.details',['id' => $competition->id]) }}">
                        <svg class="item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                        {{ $competition->name }}
                    </a>
                    @empty
                    <div class="dropdown-empty">
                        <p>No competitions available</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        @endif

        <!-- Right Section - User Profile -->
        <div class="header-right">
            <div class="user-profile-dropdown">
                <button class="profile-trigger" type="button" data-toggle="dropdown" aria-expanded="false">
                    <div class="profile-info">
                        <div class="profile-avatar">
                            <img src="{{ $headerAvatarSrc }}" alt="{{ $usr->name }}" class="avatar-img" />
                        </div>
                        <div class="profile-details">
                            @if($club)
                            <span class="profile-org">{{ $club->name }}</span>
                            @elseif($association)
                            <span class="profile-org">{{ $association->name }}</span>
                            @endif
                            <span class="profile-name">{{ Auth::guard('admin')->user()->name }}</span>
                        </div>
                    </div>
                    <svg class="chevron-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="dropdown-menu profile-menu">
                    <div class="dropdown-header">
                        <div class="header-avatar">
                            <img src="{{ $headerAvatarSrc }}" alt="{{ $usr->name }}" class="header-avatar-img" />
                        </div>
                        <div class="header-info">
                            <h6>{{ Auth::guard('admin')->user()->name }}</h6>
                            @if($club)
                            <p>{{ $club->name }}</p>
                            @elseif($association)
                            <p>{{ $association->name }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item logout-item" href="{{ route('admin.logout.submit') }}"
                        onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
                        <svg class="item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Log Out
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form id="admin-logout-form" action="{{ route('admin.logout.submit') }}" method="POST" style="display: none;">
        @csrf
    </form>
</header>

<style>
/* Prevent header bar overlap: space from main header-area and stacking */
.page-title-area {
    position: relative;
    z-index: 10;
}
.page-title-area .row.align-items-center {
    align-items: stretch;
    min-height: 0;
}
.admin-header {
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    position: relative;
    z-index: 100;
    margin-top: 0;
}
.header-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 24px;
    height: 64px;
    max-width: 100%;
    position: relative;
    z-index: 1;
}

/* Left Section - Competitions */
.header-left {
    display: flex;
    align-items: center;
}

.competitions-dropdown {
    position: relative;
}

.dropdown-trigger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 40px;
    padding: 10px 18px;
    background: linear-gradient(135deg, #5b67ea 0%, #764ba2 100%);
    color: #ffffff;
    border: 2px solid rgba(255, 255, 255, 0.45);
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.01em;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
    outline: none;
    box-shadow:
        0 4px 16px rgba(102, 126, 234, 0.45),
        0 1px 0 rgba(255, 255, 255, 0.25) inset;
}

.dropdown-trigger:hover {
    filter: brightness(1.06);
    transform: translateY(-1px);
    box-shadow:
        0 8px 22px rgba(118, 75, 162, 0.5),
        0 1px 0 rgba(255, 255, 255, 0.3) inset;
}

.dropdown-trigger:focus {
    box-shadow:
        0 0 0 3px rgba(102, 126, 234, 0.45),
        0 6px 20px rgba(102, 126, 234, 0.4);
}

.dropdown-trigger[aria-expanded="true"] {
    filter: brightness(1.05);
    box-shadow:
        0 0 0 3px rgba(255, 255, 255, 0.35),
        0 6px 18px rgba(0, 0, 0, 0.15);
}

.dropdown-icon,
.chevron-icon,
.item-icon {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

.dropdown-text {
    font-weight: 600;
}

/* Right Section - User Profile */
.header-right {
    display: flex;
    align-items: center;
}

.user-profile-dropdown {
    position: relative;
}

.profile-trigger {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 12px;
    background: transparent;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    outline: none;
}

.profile-trigger:hover {
    background: #f3f4f6;
}

.profile-trigger:focus {
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.profile-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.profile-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid #e5e7eb;
}

.avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-details {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    text-align: left;
}

.profile-org {
    font-size: 12px;
    color: #6b7280;
    font-weight: 500;
    line-height: 1.2;
}

.profile-name {
    font-size: 14px;
    color: #1f2937;
    font-weight: 600;
    line-height: 1.2;
}

/* Dropdown Menus */
.dropdown-menu {
    position: absolute;
    top: 100%;
    margin-top: 8px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    min-width: 200px;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1050;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.2s ease;
}

.dropdown-menu.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.competitions-menu {
    left: 0;
}

.profile-menu {
    right: 0;
}

.dropdown-header {
    padding: 16px;
    border-bottom: 1px solid #e5e7eb;
}

.dropdown-header h6 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.header-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    overflow: hidden;
    margin-bottom: 12px;
    border: 2px solid #e5e7eb;
}

.header-avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.header-info h6 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
}

.header-info p {
    margin: 0;
    font-size: 14px;
    color: #6b7280;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    color: #374151;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
}

.dropdown-item:hover {
    background: #f3f4f6;
    color: #1f2937;
    text-decoration: none;
}

.logout-item {
    color: #ef4444;
}

.logout-item:hover {
    background: #fef2f2;
    color: #dc2626;
}

.dropdown-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 8px 0;
}

.dropdown-empty {
    padding: 16px;
    text-align: center;
}

.dropdown-empty p {
    margin: 0;
    font-size: 14px;
    color: #6b7280;
    font-style: italic;
}

/* Responsive Design */
@media (max-width: 768px) {
    .header-container {
        padding: 0 16px;
        height: 56px;
    }
    
    .profile-details {
        display: none;
    }
    
    .dropdown-trigger {
        padding: 8px 12px;
        min-height: 40px;
        font-size: 13px;
    }
    
    .dropdown-menu {
        min-width: 180px;
    }
}

@media (max-width: 480px) {
    .header-container {
        padding: 0 12px;
    }
    
    .dropdown-trigger {
        padding: 8px 10px;
        gap: 6px;
    }
}

/* Animation for dropdown toggle */
.dropdown-trigger[aria-expanded="true"] .chevron-icon,
.profile-trigger[aria-expanded="true"] .chevron-icon {
    transform: rotate(180deg);
}

.chevron-icon {
    transition: transform 0.2s ease;
}

/* Focus styles for accessibility */
.dropdown-item:focus {
    outline: 2px solid #667eea;
    outline-offset: -2px;
    background: #f3f4f6;
}

/* Loading state */
.dropdown-item.loading {
    opacity: 0.6;
    pointer-events: none;
}
</style>

<script>
// Enhanced dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    // Handle dropdown toggles
    const dropdownTriggers = document.querySelectorAll('[data-toggle="dropdown"]');
    
    dropdownTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const menu = this.nextElementSibling;
            const isOpen = menu.classList.contains('show');
            
            // Close all other dropdowns
            document.querySelectorAll('.dropdown-menu.show').forEach(openMenu => {
                openMenu.classList.remove('show');
                openMenu.previousElementSibling.setAttribute('aria-expanded', 'false');
            });
            
            // Toggle current dropdown
            if (!isOpen) {
                menu.classList.add('show');
                this.setAttribute('aria-expanded', 'true');
            } else {
                menu.classList.remove('show');
                this.setAttribute('aria-expanded', 'false');
            }
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.competitions-dropdown') && !e.target.closest('.user-profile-dropdown')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
                menu.previousElementSibling.setAttribute('aria-expanded', 'false');
            });
        }
    });
    
    // Close dropdowns on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
                menu.previousElementSibling.setAttribute('aria-expanded', 'false');
                menu.previousElementSibling.focus();
            });
        }
    });
});
</script>