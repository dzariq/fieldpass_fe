@extends('backend.layouts.master')

@section('title')
Fantasy Create - Admin Panel
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .form-check-label {
        text-transform: capitalize;
    }
    .matchweek-card {
        border-left: 4px solid #28a745;
        margin-bottom: 15px;
        background-color: #f8f9fa;
    }
    .matchweek-header {
        background-color: #28a745;
        color: white;
        padding: 10px 15px;
        font-weight: bold;
    }
    .remove-matchweek {
        float: right;
        cursor: pointer;
    }
</style>
@endsection

@php
$usr = Auth::guard('admin')->user();
$adminObj = App\Models\Admin::find($usr->id);
@endphp

@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">{{ __('Fantasy Timeline Create') }}</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><a href="{{ route('admin.fantasy.index') }}">{{ __('All Fantasy') }}</a></li>
                    <li><span>{{ __('Create Fantasy Timeline') }}</span></li>
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
        <!-- data table start -->
        <div class="col-12 mt-5">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title">{{ __('Create New Fantasy Timeline') }}</h4>
                    @include('backend.layouts.partials.messages')

                    <form action="{{ route('admin.fantasy.store') }}" method="POST">
                        @csrf

                        <!-- Competition Selection -->
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
    <label for="competition_id">
        <i class="fa fa-trophy"></i> {{ __('Competition') }} <span class="text-danger">*</span>
    </label>
    <select class="form-control select @error('competition_id') is-invalid @enderror" 
            id="competition_id" 
            name="competition_id" 
            required>
        <option value="">-- {{ __('Select Competition') }} --</option>
        @foreach ($competitions as $competition)
        <option value="{{ $competition->id }}" {{ old('competition_id') == $competition->id ? 'selected' : '' }}>
            {{ $competition->name }}
        </option>
        @endforeach
    </select>
    @error('competition_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
                            

                            <div class="form-group col-md-6 col-sm-12">
                                <label for="total_matchweeks">
                                    <i class="fa fa-calendar"></i> {{ __('Total Matchweeks') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                    class="form-control" 
                                    id="total_matchweeks" 
                                    name="total_matchweeks" 
                                    placeholder="Enter total matchweeks" 
                                    min="1" 
                                    max="50"
                                    value="{{ old('total_matchweeks', 22) }}" 
                                    required>
                                <small class="form-text text-muted">{{ __('How many matchweeks in this competition?') }}</small>
                            </div>
                        </div>

                        <hr>

                        <!-- Default Values for All Matchweeks -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fa fa-cog"></i> {{ __('Default Values (Applied to All Matchweeks)') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="default_transfer">
                                                <i class="fa fa-exchange"></i> {{ __('Default Transfer') }}
                                            </label>
                                            <input type="number" 
                                                class="form-control" 
                                                id="default_transfer" 
                                                value="2" 
                                                min="0">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="default_max_same_club">
                                                <i class="fa fa-users"></i> {{ __('Default Max Same Club') }}
                                            </label>
                                            <input type="number" 
                                                class="form-control" 
                                                id="default_max_same_club" 
                                                value="3" 
                                                min="0">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="default_credit">
                                                <i class="fa fa-money"></i> {{ __('Default Credit') }}
                                            </label>
                                            <input type="number" 
                                                step="0.01"
                                                class="form-control" 
                                                id="default_credit" 
                                                value="1000.00" 
                                                min="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="default_benchboost">
                                                <i class="fa fa-rocket"></i> {{ __('Default Bench Boost') }}
                                            </label>
                                            <input type="number" 
                                                class="form-control" 
                                                id="default_benchboost" 
                                                value="6" 
                                                min="0">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="default_wildcard">
                                                <i class="fa fa-star"></i> {{ __('Default Wildcard') }}
                                            </label>
                                            <input type="number" 
                                                class="form-control" 
                                                id="default_wildcard" 
                                                value="2" 
                                                min="0">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="default_triple">
                                                <i class="fa fa-trophy"></i> {{ __('Default Triple Captain') }}
                                            </label>
                                            <input type="number" 
                                                class="form-control" 
                                                id="default_triple" 
                                                value="4" 
                                                min="0">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-info btn-block" id="applyDefaults">
                                                <i class="fa fa-check"></i> {{ __('Apply to All') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Generate Matchweeks Button -->
                        <div class="text-center mb-4">
                            <button type="button" class="btn btn-success btn-lg" id="generateMatchweeks">
                                <i class="fa fa-plus-circle"></i> {{ __('Generate Matchweeks') }}
                            </button>
                        </div>

                        <!-- Matchweeks Container -->
                        <div id="matchweeksContainer"></div>

                        <div class="mt-4" id="submitButtons" style="display: none;">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa fa-save"></i> {{ __('Save Fantasy Timeline') }}
                            </button>
                            <a href="{{ route('admin.fantasy.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fa fa-times"></i> {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- data table end -->
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2();

        // Generate matchweeks
        $('#generateMatchweeks').on('click', function() {
            const totalMatchweeks = parseInt($('#total_matchweeks').val());
            
            if (!totalMatchweeks || totalMatchweeks < 1) {
                alert('Please enter a valid number of matchweeks');
                return;
            }

            const container = $('#matchweeksContainer');
            container.empty();

            for (let i = 1; i <= totalMatchweeks; i++) {
                const matchweekHtml = `
                    <div class="card matchweek-card" data-matchweek="${i}">
                        <div class="matchweek-header">
                            <i class="fa fa-calendar"></i> Matchweek ${i}
                            <span class="remove-matchweek" data-matchweek="${i}">
                                <i class="fa fa-times"></i>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>
                                            <i class="fa fa-exchange"></i> Transfer
                                        </label>
                                        <input type="number" 
                                            class="form-control transfer-input" 
                                            name="matchweeks[${i}][transfer]" 
                                            value="2" 
                                            min="0" 
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>
                                            <i class="fa fa-users"></i> Max Same Club
                                        </label>
                                        <input type="number" 
                                            class="form-control max-same-club-input" 
                                            name="matchweeks[${i}][max_same_club]" 
                                            value="3" 
                                            min="0" 
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>
                                            <i class="fa fa-money"></i> Credit
                                        </label>
                                        <input type="number" 
                                            step="0.01"
                                            class="form-control credit-input" 
                                            name="matchweeks[${i}][credit]" 
                                            value="1000.00" 
                                            min="0" 
                                            required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>
                                            <i class="fa fa-rocket"></i> Bench Boost
                                        </label>
                                        <input type="number" 
                                            class="form-control benchboost-input" 
                                            name="matchweeks[${i}][benchboost]" 
                                            value="6" 
                                            min="0" 
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>
                                            <i class="fa fa-star"></i> Wildcard
                                        </label>
                                        <input type="number" 
                                            class="form-control wildcard-input" 
                                            name="matchweeks[${i}][wildcard]" 
                                            value="2" 
                                            min="0" 
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>
                                            <i class="fa fa-trophy"></i> Triple Captain
                                        </label>
                                        <input type="number" 
                                            class="form-control triple-input" 
                                            name="matchweeks[${i}][triple]" 
                                            value="4" 
                                            min="0" 
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>
                                            <i class="fa fa-clock-o"></i> Cutoff Time
                                        </label>
                                        <input type="datetime-local" 
                                            class="form-control" 
                                            name="matchweeks[${i}][cutoff_time]">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.append(matchweekHtml);
            }

            $('#submitButtons').show();
        });

        // Apply default values to all matchweeks
        $('#applyDefaults').on('click', function() {
            const transfer = $('#default_transfer').val();
            const maxSameClub = $('#default_max_same_club').val();
            const credit = $('#default_credit').val();
            const benchboost = $('#default_benchboost').val();
            const wildcard = $('#default_wildcard').val();
            const triple = $('#default_triple').val();

            $('.transfer-input').val(transfer);
            $('.max-same-club-input').val(maxSameClub);
            $('.credit-input').val(credit);
            $('.benchboost-input').val(benchboost);
            $('.wildcard-input').val(wildcard);
            $('.triple-input').val(triple);

            alert('Default values applied to all matchweeks!');
        });

        // Remove matchweek
        $(document).on('click', '.remove-matchweek', function() {
            const matchweek = $(this).data('matchweek');
            if (confirm(`Are you sure you want to remove Matchweek ${matchweek}?`)) {
                $(`.matchweek-card[data-matchweek="${matchweek}"]`).remove();
            }
        });
    });
</script>
@endsection