@extends('backend.layouts.master')

@section('title')
Fantasy Points Create - Admin Panel
@endsection

@section('styles')
<style>
    .points-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .points-table th,
    .points-table td {
        padding: 12px;
        text-align: center;
        border: 1px solid #e5e7eb;
    }
    
    .points-table th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
    }
    
    .points-table tr:hover {
        background-color: #f9fafb;
    }
    
    .position-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.875rem;
    }
    
    .position-gk { background: #dbeafe; color: #1e40af; }
    .position-df { background: #dcfce7; color: #166534; }
    .position-mf { background: #fef3c7; color: #92400e; }
    .position-st { background: #fee2e2; color: #991b1b; }
    
    .points-input {
        width: 70px;
        text-align: center;
        padding: 6px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-weight: 600;
    }
    
    .points-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .positive-points {
        background-color: #f0fdf4;
    }
    
    .negative-points {
        background-color: #fef2f2;
    }
    
    .section-header {
        background: #f3f4f6;
        padding: 12px;
        font-weight: 600;
        color: #374151;
        border: 1px solid #e5e7eb;
        border-bottom: none;
        margin-top: 20px;
    }
    
    .event-icon {
        font-size: 1.2rem;
        margin-right: 5px;
    }
</style>
@endsection

@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Create Fantasy Points Rules</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.fantasy.index') }}">Fantasy</a></li>
                    <li><a href="{{ route('admin.fantasy.points') }}">Points</a></li>
                    <li><span>Create</span></li>
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
    <div class="row">
        <div class="col-12 mt-5">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title">Create Fantasy Points Rules - All Positions</h4>
                    @include('backend.layouts.partials.messages')

                    <form action="{{ route('admin.fantasy.points.store') }}" method="POST">
                        @csrf

                        <!-- Competition Selection -->
                        <div class="form-group">
                            <label for="competition_id">Competition <span class="text-danger">*</span></label>
                            <select class="form-control" id="competition_id" name="competition_id" required>
                                <option value="">Select Competition</option>
                                @foreach($competitions as $comp)
                                    <option value="{{ $comp->id }}" {{ old('competition_id') == $comp->id ? 'selected' : '' }}>
                                        {{ $comp->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> <strong>Instructions:</strong>
                            <ul class="mb-0">
                                <li>Set points for each position (GK, DF, MF, ST)</li>
                                <li>Use <strong>positive</strong> numbers for rewards (e.g., +5 for goal)</li>
                                <li>Use <strong>negative</strong> numbers for penalties (e.g., -2 for yellow card)</li>
                            </ul>
                        </div>

                        <!-- POSITIVE POINTS SECTION -->
                        <div class="section-header">
                            <span class="event-icon">✅</span> Positive Points (Rewards)
                        </div>
                        
                        <table class="points-table positive-points">
                            <thead>
                                <tr>
                                    <th>Position</th>
                                    <th><span class="event-icon">⚽</span> Goal</th>
                                    <th><span class="event-icon">🎯</span> Assist</th>
                                    <th><span class="event-icon">🛡️</span> Clean Sheet</th>
                                    <th><span class="event-icon">🧤</span> Penalty Saved</th>
                                    <th><span class="event-icon">⏱️</span> Played 60+ Min</th>
                                    <th><span class="event-icon">🏆</span> Win Bonus</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- GK Row -->
                                <tr>
                                    <td>
                                        <span class="position-badge position-gk">GK</span>
                                        <input type="hidden" name="positions[0][position]" value="GK">
                                    </td>
                                    <td><input type="number" class="points-input" name="positions[0][goal]" value="{{ old('positions.0.goal', 7) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[0][assist]" value="{{ old('positions.0.assist', 5) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[0][clean_sheet]" value="{{ old('positions.0.clean_sheet', 4) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[0][penalty_saved]" value="{{ old('positions.0.penalty_saved', 5) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[0][minutes_played_60]" value="{{ old('positions.0.minutes_played_60', 2) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[0][win_bonus]" value="{{ old('positions.0.win_bonus', 0) }}" required></td>
                                </tr>

                                <!-- DF Row -->
                                <tr>
                                    <td>
                                        <span class="position-badge position-df">DF</span>
                                        <input type="hidden" name="positions[1][position]" value="DF">
                                    </td>
                                    <td><input type="number" class="points-input" name="positions[1][goal]" value="{{ old('positions.1.goal', 6) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[1][assist]" value="{{ old('positions.1.assist', 3) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[1][clean_sheet]" value="{{ old('positions.1.clean_sheet', 4) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[1][penalty_saved]" value="{{ old('positions.1.penalty_saved', 0) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[1][minutes_played_60]" value="{{ old('positions.1.minutes_played_60', 2) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[1][win_bonus]" value="{{ old('positions.1.win_bonus', 0) }}" required></td>
                                </tr>

                                <!-- MF Row -->
                                <tr>
                                    <td>
                                        <span class="position-badge position-mf">MF</span>
                                        <input type="hidden" name="positions[2][position]" value="MF">
                                    </td>
                                    <td><input type="number" class="points-input" name="positions[2][goal]" value="{{ old('positions.2.goal', 5) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[2][assist]" value="{{ old('positions.2.assist', 3) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[2][clean_sheet]" value="{{ old('positions.2.clean_sheet', 1) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[2][penalty_saved]" value="{{ old('positions.2.penalty_saved', 0) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[2][minutes_played_60]" value="{{ old('positions.2.minutes_played_60', 2) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[2][win_bonus]" value="{{ old('positions.2.win_bonus', 0) }}" required></td>
                                </tr>

                                <!-- ST Row -->
                                <tr>
                                    <td>
                                        <span class="position-badge position-st">ST</span>
                                        <input type="hidden" name="positions[3][position]" value="ST">
                                    </td>
                                    <td><input type="number" class="points-input" name="positions[3][goal]" value="{{ old('positions.3.goal', 4) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[3][assist]" value="{{ old('positions.3.assist', 3) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[3][clean_sheet]" value="{{ old('positions.3.clean_sheet', 0) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[3][penalty_saved]" value="{{ old('positions.3.penalty_saved', 0) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[3][minutes_played_60]" value="{{ old('positions.3.minutes_played_60', 2) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[3][win_bonus]" value="{{ old('positions.3.win_bonus', 0) }}" required></td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- NEGATIVE POINTS SECTION -->
                        <div class="section-header">
                            <span class="event-icon">❌</span> Negative Points (Penalties)
                        </div>

                        <table class="points-table negative-points">
                            <thead>
                                <tr>
                                    <th>Position</th>
                                    <th><span class="event-icon">🟨</span> Yellow Card</th>
                                    <th><span class="event-icon">🟥</span> Red Card</th>
                                    <th><span class="event-icon">⚠️</span> Own Goal</th>
                                    <th><span class="event-icon">❌</span> Penalty Missed</th>
                                    <th><span class="event-icon">⚽</span> Concede Goal</th>
                                    <th><span class="event-icon">⏰</span> Played < 60 Min</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- GK Row -->
                                <tr>
                                    <td><span class="position-badge position-gk">GK</span></td>
                                    <td><input type="number" class="points-input" name="positions[0][yellow_card]" value="{{ old('positions.0.yellow_card', -1) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[0][red_card]" value="{{ old('positions.0.red_card', -3) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[0][own_goal]" value="{{ old('positions.0.own_goal', -2) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[0][penalty_missed]" value="{{ old('positions.0.penalty_missed', -2) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[0][concede_goal]" value="{{ old('positions.0.concede_goal', -1) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[0][played_less_60]" value="{{ old('positions.0.played_less_60', 1) }}" required></td>
                                </tr>

                                <!-- DF Row -->
                                <tr>
                                    <td><span class="position-badge position-df">DF</span></td>
                                    <td><input type="number" class="points-input" name="positions[1][yellow_card]" value="{{ old('positions.1.yellow_card', -1) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[1][red_card]" value="{{ old('positions.1.red_card', -3) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[1][own_goal]" value="{{ old('positions.1.own_goal', -2) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[1][penalty_missed]" value="{{ old('positions.1.penalty_missed', -2) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[1][concede_goal]" value="{{ old('positions.1.concede_goal', -1) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[1][played_less_60]" value="{{ old('positions.1.played_less_60', 1) }}" required></td>
                                </tr>

                                <!-- MF Row -->
                                <tr>
                                    <td><span class="position-badge position-mf">MF</span></td>
                                    <td><input type="number" class="points-input" name="positions[2][yellow_card]" value="{{ old('positions.2.yellow_card', -1) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[2][red_card]" value="{{ old('positions.2.red_card', -3) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[2][own_goal]" value="{{ old('positions.2.own_goal', -2) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[2][penalty_missed]" value="{{ old('positions.2.penalty_missed', -2) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[2][concede_goal]" value="{{ old('positions.2.concede_goal', 0) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[2][played_less_60]" value="{{ old('positions.2.played_less_60', 1) }}" required></td>
                                </tr>

                                <!-- ST Row -->
                                <tr>
                                    <td><span class="position-badge position-st">ST</span></td>
                                    <td><input type="number" class="points-input" name="positions[3][yellow_card]" value="{{ old('positions.3.yellow_card', -1) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[3][red_card]" value="{{ old('positions.3.red_card', -3) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[3][own_goal]" value="{{ old('positions.3.own_goal', -2) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[3][penalty_missed]" value="{{ old('positions.3.penalty_missed', -2) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[3][concede_goal]" value="{{ old('positions.3.concede_goal', 0) }}" required></td>
                                    <td><input type="number" class="points-input" name="positions[3][played_less_60]" value="{{ old('positions.3.played_less_60', 1) }}" required></td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-lg pr-5 pl-5">
                                <i class="fa fa-save"></i> Save All Points Rules
                            </button>
                            <a href="{{ route('admin.fantasy.points') }}" class="btn btn-secondary btn-lg pr-5 pl-5">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Optional: Add visual feedback for negative values
    document.querySelectorAll('.points-input').forEach(input => {
        input.addEventListener('input', function() {
            if (parseInt(this.value) < 0) {
                this.style.color = '#dc2626';
                this.style.fontWeight = '700';
            } else {
                this.style.color = '#059669';
                this.style.fontWeight = '700';
            }
        });
        
        // Trigger on page load
        input.dispatchEvent(new Event('input'));
    });
</script>
@endsection