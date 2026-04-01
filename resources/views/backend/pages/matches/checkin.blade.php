@extends('backend.layouts.master')

@section('title')
Match Checkin - Admin Panel
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
<!-- Ensure FontAwesome is loaded -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    .form-check-label {
        text-transform: capitalize;
    }
    
    .checkin-container {
        min-height: calc(100vh - 120px);
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2rem 0;
    }
    
    .checkin-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        height: 100%;
    }
    
    .qr-scanner-section {
        text-align: center;
        padding: 2rem;
    }
    
    .qr-title {
        color: #2d3748;
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 1rem;
        background: linear-gradient(45deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .qr-subtitle {
        color: #4a5568;
        font-size: 1.1rem;
        margin-bottom: 2rem;
    }
    
    #reader {
        margin: 0 auto;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    
    .checkin-stats {
        background: linear-gradient(45deg, #4CAF50, #45a049);
        color: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        text-align: center;
    }
    
    .stats-number {
        font-size: 2.5rem;
        font-weight: bold;
        display: block;
    }
    
    .stats-label {
        font-size: 1rem;
        opacity: 0.9;
    }
    
    .players-section {
        padding: 2rem;
        height: calc(100% - 4rem);
        display: flex;
        flex-direction: column;
    }
    
    .players-list-title {
        color: #2d3748;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .players-list-container {
        flex: 1;
        overflow-y: auto;
        max-height: 550px;
    }
    
    .player-item {
        background: #f8f9fa;
        border-left: 4px solid #e2e8f0;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        transition: all 0.3s ease;
        animation: slideInRight 0.5s ease;
    }
    
    .player-item:hover {
        transform: translateX(5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .player-item.checked-in {
        border-left-color: #4CAF50;
        background: #f0fdf4;
        box-shadow: 0 2px 8px rgba(76, 175, 80, 0.2);
    }
    
    .player-item.not-checked-in {
        border-left-color: #e2e8f0;
        background: #f8f9fa;
    }
    
    .player-item.goalkeeper {
        border-left-color: #ff9800;
    }
    
    .player-item.goalkeeper.checked-in {
        background: #fff8e1;
        border-left-color: #ff9800;
    }
    
    .player-item.substitute {
        border-left-color: #9c27b0;
    }
    
    .player-item.substitute.checked-in {
        background: #fce4ec;
        border-left-color: #9c27b0;
    }
    
    .player-name {
        color: #2d3748;
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 0.25rem;
    }
    
    .player-checkin-time {
        color: #4a5568;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .player-id-badge {
        background: #667eea;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .success-animation {
        animation: successPulse 1s ease;
    }
    
    .club-section {
        margin-bottom: 2rem;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        background: white;
    }
    
    .club-header {
        background: linear-gradient(45deg, #667eea, #764ba2);
        color: white;
        padding: 1rem 1.5rem;
        font-weight: 600;
        font-size: 1.1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .club-count {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.9rem;
    }
    
    .position-group {
        margin-bottom: 1rem;
    }
    
    .position-header {
        background: #f8f9fa;
        color: #495057;
        padding: 0.75rem 1rem;
        font-weight: 600;
        font-size: 0.95rem;
        border-bottom: 1px solid #e9ecef;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .position-players {
        padding: 0.5rem;
    }
    
    .position-badge {
        background: #667eea;
        color: white;
        padding: 0.2rem 0.5rem;
        border-radius: 10px;
        font-size: 0.75rem;
        font-weight: 500;
        margin-right: 0.5rem;
    }
    
    .position-badge.gk {
        background: #ff9800;
    }
    
    .position-badge.sub {
        background: #9c27b0;
    }
    
    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-left: 0.5rem;
    }
    
    .status-checked-in {
        background: #4CAF50;
        box-shadow: 0 0 8px rgba(76, 175, 80, 0.3);
    }
    
    .status-pending {
        background: #e2e8f0;
    }
    
    .summary-stats {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }
    
    .stat-card {
        background: linear-gradient(45deg, #667eea, #764ba2);
        color: white;
        border-radius: 10px;
        padding: 1rem;
        text-align: center;
        flex: 1;
        min-width: 120px;
    }
    
    .stat-number {
        font-size: 1.5rem;
        font-weight: bold;
        display: block;
        margin-bottom: 0.25rem;
    }
    
    .stat-label {
        font-size: 0.8rem;
        opacity: 0.9;
    }
    
    .checkin-badge {
        background: #4CAF50;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-block;
        margin-left: 0.5rem;
    }
    
    .pending-badge {
        background: #f59e0b;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-block;
        margin-left: 0.5rem;
    }
    
    @keyframes successPulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes checkinPulse {
        0% { background: #f0fdf4; transform: scale(1); }
        50% { background: #dcfce7; transform: scale(1.02); }
        100% { background: #f0fdf4; transform: scale(1); }
    }
    
    .player-item.just-checked-in {
        animation: checkinPulse 2s ease;
    }
    
    .empty-state {
        text-align: center;
        color: #718096;
        padding: 3rem 1rem;
    }
    
    .empty-state i {
        font-size: 4rem;
        color: #e2e8f0;
        margin-bottom: 1rem;
    }
    
    .refresh-button {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        border-radius: 50%;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .refresh-button:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(180deg);
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .checkin-container {
            padding: 1rem 0;
        }
        
        .qr-scanner-section, .players-section {
            padding: 1.5rem;
        }
        
        .qr-title {
            font-size: 1.5rem;
        }
        
        .stats-number {
            font-size: 2rem;
        }
        
        .summary-stats {
            flex-direction: column;
        }
        
        .players-list-container {
            max-height: 400px;
        }
    }
</style>
@endsection

@php
$usr = Auth::guard('admin')->user();
$adminObj = App\Models\Admin::with('associations.competitions')->find($usr->id);
@endphp

@section('admin-content')
<div class="checkin-container">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-6 mb-4">
                <div class="checkin-card">
                    <div class="qr-scanner-section">
                        <h2 class="qr-title">
                            📱 Match Check-In
                        </h2>
                        <p class="qr-subtitle">Scan player QR code to check in</p>
                        
                        <div class="checkin-stats">
                            <span class="stats-number" id="checkedInCount">0</span>
                            <span class="stats-label">Players Checked In</span>
                        </div>
                        
                        <div id="reader" style="width: 100%; max-width: 350px;"></div>
                        <div id="result" class="mt-3"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-7 col-md-6">
                <div class="checkin-card" style="position: relative;">
                    <button class="refresh-button" onclick="refreshData()" title="Refresh Data">
                        🔄
                    </button>
                    
                    <div class="players-section">
                        <!-- Summary Stats -->
                        <div class="summary-stats" id="summaryStats">
                            <div class="stat-card">
                                <span class="stat-number" id="totalRegistered">0</span>
                                <span class="stat-label">Registered</span>
                            </div>
                            <div class="stat-card">
                                <span class="stat-number" id="totalCheckedIn">0</span>
                                <span class="stat-label">Checked In</span>
                            </div>
                            <div class="stat-card">
                                <span class="stat-number" id="totalPending">0</span>
                                <span class="stat-label">Pending</span>
                            </div>
                        </div>
                        
                        <!-- Players List Header -->
                        <h3 class="players-list-title">
                            📋 Registered Players
                        </h3>
                        
                        <!-- Players List -->
                        <div class="players-list-container">
                            <div id="registeredPlayersList">
                                <div class="empty-state">
                                    📋
                                    <h5>Loading registered players...</h5>
                                    <p>Please wait while we fetch the player list</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/html5-qrcode@2.2.1/html5-qrcode.min.js"></script>
<script>
    const resultBox = document.getElementById('result');
    const registeredPlayersContainer = document.getElementById('registeredPlayersList');
    const checkedInCountElement = document.getElementById('checkedInCount');
    
    let checkedInPlayers = [];
    let registeredPlayers = [];

    function onScanSuccess(decodedText, decodedResult) {
        // Stop scanning temporarily to prevent multiple scans
        html5QrcodeScanner.stop().then(() => {
            showLoadingMessage();
            
            fetch("{{ route('admin.match.checkin_verify') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ token: decodedText })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage(data.message);
                    addPlayerToCheckedIn(data.player);
                    updateStats();
                    updatePlayerStatusInList(data.player.id, true);
                } else {
                    showErrorMessage(data.message);
                }
                
                // Restart scanner after 2 seconds
                setTimeout(() => {
                    restartScanner();
                }, 2000);
            })
            .catch(err => {
                showErrorMessage(`Failed to verify: ${err.message}`);
                setTimeout(() => {
                    restartScanner();
                }, 2000);
            });
        }).catch(err => {
            showErrorMessage(`Camera stop error: ${err.message}`);
        });
    }

    function showLoadingMessage() {
        resultBox.innerHTML = `
            <div class="alert alert-info">
                ⏳ Verifying player...
            </div>`;
    }

    function showSuccessMessage(message) {
        resultBox.innerHTML = `
            <div class="alert alert-success success-animation">
                ✅ ${message}
            </div>`;
    }

    function showErrorMessage(message) {
        resultBox.innerHTML = `
            <div class="alert alert-danger">
                ❌ ${message}
            </div>`;
    }

    function addPlayerToCheckedIn(player) {
        // Check if player already exists in the checked-in list
        if (checkedInPlayers.find(p => p.id === player.id)) {
            return;
        }
        
        checkedInPlayers.unshift(player); // Add to beginning of array
    }

    function loadRegisteredPlayers() {
        fetch("{{ route('admin.match.registered_players') }}", {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                registeredPlayers = data.players;
                renderRegisteredPlayers();
                updateStats();
            } else {
                registeredPlayersContainer.innerHTML = `
                    <div class="empty-state">
                        ❌
                        <h5>Failed to load players</h5>
                        <p>${data.message}</p>
                    </div>`;
            }
        })
        .catch(err => {
            console.error('Failed to load registered players:', err);
            registeredPlayersContainer.innerHTML = `
                <div class="empty-state">
                    ❌
                    <h5>Error loading players</h5>
                    <p>Please check your connection and try again</p>
                </div>`;
        });
    }

    function renderRegisteredPlayers() {
        if (registeredPlayers.length === 0) {
            registeredPlayersContainer.innerHTML = `
                <div class="empty-state">
                    📋
                    <h5>No registered players</h5>
                    <p>No players are registered for this match</p>
                </div>`;
            return;
        }

        // Group players by club
        const playersByClub = registeredPlayers.reduce((acc, player) => {
            const clubName = player.club_name || 'Unknown Club';
            if (!acc[clubName]) {
                acc[clubName] = [];
            }
            acc[clubName].push(player);
            return acc;
        }, {});

        let clubsHTML = '';
        
        Object.keys(playersByClub).forEach(clubName => {
            const clubPlayers = playersByClub[clubName];
            
            // Sort players by position
            const sortedPlayers = clubPlayers.sort((a, b) => {
                const positionA = getPositionType(a.position);
                const positionB = getPositionType(b.position);
                
                if (positionA !== positionB) {
                    const order = { 'gk': 0, 'player': 1, 'sub': 2 };
                    return order[positionA] - order[positionB];
                }
                
                return getPositionNumber(a.position) - getPositionNumber(b.position);
            });
            
            // Group by position type
            const positionGroups = {
                gk: sortedPlayers.filter(p => getPositionType(p.position) === 'gk'),
                player: sortedPlayers.filter(p => getPositionType(p.position) === 'player'),
                sub: sortedPlayers.filter(p => getPositionType(p.position) === 'sub')
            };

            const checkedInCount = clubPlayers.filter(p => isPlayerCheckedIn(p.id)).length;

            clubsHTML += `
                <div class="club-section">
                    <div class="club-header">
                        🏆 ${clubName}
                        <span class="club-count">${checkedInCount}/${clubPlayers.length} checked in</span>
                    </div>
                    <div class="club-players">
                        ${renderPositionGroup('🥅 Goalkeepers', positionGroups.gk, 'gk')}
                        ${renderPositionGroup('⚽ Players', positionGroups.player, 'player')}
                        ${renderPositionGroup('🔄 Substitutes', positionGroups.sub, 'sub')}
                    </div>
                </div>
            `;
        });

        registeredPlayersContainer.innerHTML = clubsHTML;
    }

    function renderPositionGroup(title, players, type) {
        if (players.length === 0) return '';
        
        const playersHTML = players.map(player => {
            const isCheckedIn = isPlayerCheckedIn(player.id);
            const statusClass = isCheckedIn ? 'checked-in' : 'not-checked-in';
            const statusIndicator = isCheckedIn ? 'status-checked-in' : 'status-pending';
            const statusBadge = isCheckedIn ? 'checkin-badge' : 'pending-badge';
            const statusText = isCheckedIn ? 'Checked In' : 'Pending';
            const statusIcon = isCheckedIn ? '✅' : '⏳';
            
            return `
                <div class="player-item ${type} ${statusClass}" id="player-${player.id}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="player-name">
                                <span class="position-badge ${type}">${formatPosition(player.position)}</span>
                                <span class="player-id-badge">#${player.identity_number || player.id}</span>
                                ${player.name}
                                <span class="status-indicator ${statusIndicator}"></span>
                                <span class="${statusBadge}">${statusText}</span>
                            </div>
                            ${isCheckedIn ? `
                                <div class="player-checkin-time">
                                    🕒 Checked in at ${formatDateTime(getCheckedInTime(player.id))}
                                </div>
                            ` : `
                                <div class="player-checkin-time">
                                    📝 Registered for match
                                </div>
                            `}
                        </div>
                        <div class="check-icon">
                            ${statusIcon}
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        return `
            <div class="position-group">
                <div class="position-header">${title} (${players.length})</div>
                <div class="position-players">
                    ${playersHTML}
                </div>
            </div>
        `;
    }

    function isPlayerCheckedIn(playerId) {
        return checkedInPlayers.some(p => p.id === playerId);
    }

    function getCheckedInTime(playerId) {
        const player = checkedInPlayers.find(p => p.id === playerId);
        return player ? player.checkin_at : null;
    }

    function updatePlayerStatusInList(playerId, isCheckedIn) {
        const playerElement = document.getElementById(`player-${playerId}`);
        if (playerElement) {
            // Add animation class
            playerElement.classList.add('just-checked-in');
            
            // Re-render the entire list to update status
            setTimeout(() => {
                renderRegisteredPlayers();
            }, 1000);
        }
    }

    function updateStats() {
        const totalRegistered = registeredPlayers.length;
        const totalCheckedIn = checkedInPlayers.length;
        const totalPending = totalRegistered - totalCheckedIn;
        
        document.getElementById('totalRegistered').textContent = totalRegistered;
        document.getElementById('totalCheckedIn').textContent = totalCheckedIn;
        document.getElementById('totalPending').textContent = Math.max(0, totalPending);
        document.getElementById('checkedInCount').textContent = totalCheckedIn;
    }

    function refreshData() {
        loadRegisteredPlayers();
        refreshCheckedInPlayers();
    }

    function refreshCheckedInPlayers() {
        fetch("{{ route('admin.match.checkin_list') }}", {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                checkedInPlayers = data.players;
                renderRegisteredPlayers(); // Re-render to update status
                updateStats();
            }
        })
        .catch(err => {
            console.error('Failed to refresh checked-in players list:', err);
        });
    }

    // Utility functions - Updated for new position format
    function getPositionType(position) {
        if (!position) return 'player';
        
        const pos = position.toLowerCase();
        if (pos === 'gk') return 'gk';
        if (pos.startsWith('sub')) return 'sub';
        if (pos.startsWith('player')) return 'player';
        
        return 'player';
    }

    function getPositionNumber(position) {
        if (!position) return 999;
        
        const pos = position.toLowerCase();
        if (pos === 'gk') return 0;
        
        // Extract number from Player1, Player2, Sub1, Sub2, etc.
        const match = pos.match(/(\d+)/);
        if (match) {
            const num = parseInt(match[1]);
            // For substitutes, add 100 to ensure they come after players
            if (pos.startsWith('sub')) {
                return 100 + num;
            }
            return num;
        }
        
        return 999;
    }

    function formatPosition(position) {
        if (!position) return 'P';
        
        const pos = position.toLowerCase();
        if (pos === 'gk') return 'GK';
        
        // Handle Player1, Player2, etc.
        if (pos.startsWith('player')) {
            const match = pos.match(/player(\d+)/);
            return match ? `P${match[1]}` : 'P';
        }
        
        // Handle Sub1, Sub2, etc.
        if (pos.startsWith('sub')) {
            const match = pos.match(/sub(\d+)/);
            return match ? `S${match[1]}` : 'S';
        }
        
        return position.toUpperCase();
    }

    function formatDateTime(datetime) {
        const date = new Date(datetime);
        return date.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }

    function restartScanner() {
        resultBox.innerHTML = '';
        html5QrcodeScanner.start(
            { facingMode: "environment" },
            {
                fps: 10,
                qrbox: 250
            },
            onScanSuccess
        ).catch(err => {
            showErrorMessage(`Camera access error: ${err.message}`);
        });
    }

    // Initialize scanner
    const html5QrcodeScanner = new Html5Qrcode("reader");
    
    // Start scanner on page load
    html5QrcodeScanner.start(
        { facingMode: "environment" },
        {
            fps: 10,
            qrbox: 250
        },
        onScanSuccess
    ).catch(err => {
        showErrorMessage(`Camera access error: ${err.message}`);
    });

    // Load data on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadRegisteredPlayers();
        refreshCheckedInPlayers();
    });
</script>
@endsection