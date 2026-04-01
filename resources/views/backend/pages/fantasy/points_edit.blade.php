@extends('backend.layouts.master')

@section('title')
Fantasy Points Edit - Admin Panel
@endsection

@section('styles')
<style>
    .points-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
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
        font-size: 1rem;
    }
    
    .event-icon {
        font-size: 1.2rem;
        margin-right: 5px;
    }
</style>
@endsection

@section('admin-content')

<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Edit Fantasy Points Rules</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.fantasy.index') }}">Fantasy</a></li>
                    <li><a href="{{ route('admin.fantasy.points') }}">Points</a></li>
                    <li><span>Edit</span></li>
                </ul>
            </div>
        </div>
        <div class="col-sm-6 clearfix">
            @include('backend.layouts.partials.logout')
        </div>
    </div>
</div>

<div class="main-content-inner">
    <div class="row">
        <div class="col-12 mt-5">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title">
                        Edit Fantasy Points Rules - {{ $competition->name }}
                    </h4>

                    @include('backend.layouts.partials.messages')

                    <form action="{{ route('admin.fantasy.points.update', $competition->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Competition (LOCKED) --}}
                        <div class="form-group">
                            <label>Competition</label>
                            <input type="text"
                                   class="form-control"
                                   value="{{ $competition->name }}"
                                   disabled>
                        </div>

                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> <strong>Editing Points Rules</strong>
                            <p class="mb-0">Update the points for each position. Use positive numbers for rewards and negative numbers for penalties.</p>
                        </div>

                        {{-- ================= POSITIVE POINTS ================= --}}
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
                                @foreach(['GK','DF','MF','ST'] as $i => $pos)
                                <tr>
                                    <td>
                                        <span class="position-badge position-{{ strtolower($pos) }}">{{ $pos }}</span>
                                        <input type="hidden" name="positions[{{ $i }}][position]" value="{{ $pos }}">
                                    </td>
                                    <td><input class="points-input" type="number" name="positions[{{ $i }}][score]"
                                        value="{{ old("positions.$i.score", $positions[$pos]->score ?? 0) }}" required></td>

                                    <td><input class="points-input" type="number" name="positions[{{ $i }}][assist]"
                                        value="{{ old("positions.$i.assist", $positions[$pos]->assist ?? 0) }}" required></td>

                                    <td><input class="points-input" type="number" name="positions[{{ $i }}][clean_sheet]"
                                        value="{{ old("positions.$i.clean_sheet", $positions[$pos]->clean_sheet ?? 0) }}" required></td>

                                    <td><input class="points-input" type="number" name="positions[{{ $i }}][pen_saved]"
                                        value="{{ old("positions.$i.pe_saved", $positions[$pos]->pen_saved ?? 0) }}" required></td>

                                    <td><input class="points-input" type="number" name="positions[{{ $i }}][played_60]"
                                        value="{{ old("positions.$i.played_60", $positions[$pos]->played_60 ?? 0) }}" required></td>

                                    <td><input class="points-input" type="number" name="positions[{{ $i }}][win_bonus]"
                                        value="{{ old("positions.$i.win_bonus", $positions[$pos]->win_bonus ?? 0) }}" required></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{-- ================= NEGATIVE POINTS ================= --}}
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
                                    <th><span class="event-icon">⏰</span> Played &lt;60 Min</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(['GK','DF','MF','ST'] as $i => $pos)
                                <tr>
                                    <td>
                                        <span class="position-badge position-{{ strtolower($pos) }}">{{ $pos }}</span>
                                    </td>

                                    <td><input class="points-input" type="number" name="positions[{{ $i }}][yellow]"
                                        value="{{ old("positions.$i.yellow", $positions[$pos]->yellow ?? 0) }}" required></td>

                                    <td><input class="points-input" type="number" name="positions[{{ $i }}][red]"
                                        value="{{ old("positions.$i.red", $positions[$pos]->red ?? 0) }}" required></td>

                                    <td><input class="points-input" type="number" name="positions[{{ $i }}][owngoal]"
                                        value="{{ old("positions.$i.owngoal", $positions[$pos]->owngoal ?? 0) }}" required></td>

                                    <td><input class="points-input" type="number" name="positions[{{ $i }}][pen_missed]"
                                        value="{{ old("positions.$i.pen_missed", $positions[$pos]->pen_missed ?? 0) }}" required></td>

                                    <td><input class="points-input" type="number" name="positions[{{ $i }}][concede]"
                                        value="{{ old("positions.$i.concede", $positions[$pos]->concede ?? 0) }}" required></td>

                                    <td><input class="points-input" type="number" name="positions[{{ $i }}][played_less_60]"
                                        value="{{ old("positions.$i.played_less_60", $positions[$pos]->played_less_60 ?? 0) }}" required></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-lg pr-5 pl-5">
                                <i class="fa fa-save"></i> Update Points Rules
                            </button>
                            <a href="{{ route('admin.fantasy.points') }}"
                               class="btn btn-secondary btn-lg pr-5 pl-5">
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
    // Visual feedback for negative values
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