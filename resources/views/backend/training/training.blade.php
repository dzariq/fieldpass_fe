@extends('backend.layouts.master')

@section('title')
Player Training Management - Admin Panel
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .form-check-label {
        text-transform: capitalize;
    }
    
    .player-card {
        border-left: 4px solid #007bff;
        transition: all 0.3s ease;
    }
    
    .player-card.overdue {
        border-left-color: #dc3545;
        background-color: #fff5f5;
    }
    
    .overdue-badge {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    
    .training-status {
        font-size: 0.875rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
    }
    
    .select2-container {
        width: 100% !important;
    }
    
    .current-training {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
    }
</style>
@endsection

@section('admin-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Player Training Management</h3>
                    <div>
                        <a href="{{ route('admin.training.attributes.show') }}" class="btn btn-secondary">
                            <i class="fas fa-cog"></i> Manage Attributes
                        </a>
                    </div>
                </div>
                
                <form id="playerTrainingForm" action="{{ route('admin.training.submit') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                {{ session('error') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <h6>Please fix the following errors:</h6>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(isset($trainingAttributes) && $trainingAttributes->isEmpty())
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle"></i> No Training Attributes Found</h6>
                                <p class="mb-0">Please <a href="{{ route('admin.training.attributes') }}">configure training attributes</a> first before managing player trainings.</p>
                            </div>
                        @elseif(isset($players) && $players->isEmpty())
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> No Players Found</h6>
                                <p class="mb-0">No players found for your club. Please add players first.</p>
                            </div>
                        @elseif(isset($players) && isset($trainingAttributes))
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h5>Players Overview</h5>
                                    <p class="text-muted">Manage training assignments and scores for all players</p>
                                </div>
                            </div>

                            <div id="playersContainer">
                                @foreach($players as $index => $player)
                                    @php
                                        $isOverdue = isset($overdueTrainings) ? $overdueTrainings->contains($player->id) : false;
                                        $playerTrainings = isset($currentTrainings) ? $currentTrainings->get($player->id, collect()) : collect();
                                    @endphp
                                    
                                    <div class="player-card card mb-4 {{ $isOverdue ? 'overdue' : '' }}" data-player-id="{{ $player->id }}">
                                        <div class="card-header">
                                            <div class="d-flex align-items-center">
                                                <h6 class="mb-0">
                                                    <span class="badge badge-primary mr-2">#{{ $player->identity_number }}</span>
                                                    {{ $player->name }}
                                                    @if($isOverdue)
                                                        <span class="badge badge-danger overdue-badge ml-2">
                                                            <i class="fas fa-exclamation-triangle"></i> Training Overdue
                                                        </span>
                                                    @endif
                                                </h6>
                                                <button type="button" class="btn btn-sm btn-success ml-auto add-training-btn" data-player-id="{{ $player->id }}">
                                                    <i class="fas fa-plus"></i> Add Training
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="card-body">
                                            <!-- Current Trainings -->
                                            @if($playerTrainings->isNotEmpty())
                                                <h6>Current Trainings:</h6>
                                                @foreach($playerTrainings as $training)
                                                    <div class="current-training">
                                                        <div class="row align-items-center">
                                                            <div class="col-md-3">
                                                                <strong>{{ $training->trainingAttribute->name ?? 'N/A' }}</strong>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <small class="text-muted">
                                                                    {{ $training->start_date->format('M d, Y') }} - 
                                                                    {{ $training->end_date->format('M d, Y') }}
                                                                </small>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <span class="badge badge-{{ $training->getStatusColor() }} training-status">
                                                                    {{ ucfirst($training->getStatus()) }}
                                                                </span>
                                                            </div>
                                                            <div class="col-md-2">
                                                                @if($training->score)
                                                                    <span class="badge badge-success">{{ $training->score }}/100</span>
                                                                @else
                                                                    <span class="text-muted">No Score</span>
                                                                @endif
                                                            </div>
                                                            <div class="col-md-2">
                                                                @if($training->isOverdue() || $training->isCompleted())
                                                                    <button type="button" class="btn btn-sm btn-warning edit-score-btn" 
                                                                            data-training-id="{{ $training->id }}"
                                                                            data-player-id="{{ $player->id }}"
                                                                            data-attribute-id="{{ $training->training_attribute_id }}"
                                                                            data-current-score="{{ $training->score }}">
                                                                        <i class="fas fa-edit"></i> Score
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @if($training->message)
                                                            <div class="mt-2">
                                                                <small class="text-muted"><strong>Note:</strong> {{ $training->message }}</small>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="text-muted mb-0">No training assigned yet.</p>
                                            @endif
                                            
                                            <!-- Training Forms Container -->
                                            <div class="training-forms-container mt-3" id="training-forms-{{ $player->id }}">
                                                <!-- Dynamic training forms will be added here -->
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Loading Data...</h6>
                                <p class="mb-0">Please wait while we load the training data.</p>
                            </div>
                        @endif
                    </div>
                    
                    @if(isset($trainingAttributes) && isset($players) && !$trainingAttributes->isEmpty() && !$players->isEmpty())
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save All Training Data
                            </button>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary ml-2">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Training Form Template -->
<template id="training-form-template">
    <div class="training-form card border-info mb-3">
        <div class="card-body">
            <div class="row">
                <input type="hidden" name="player_trainings[INDEX][player_id]" value="PLAYER_ID">
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Training Attribute</label>
                        <select name="player_trainings[INDEX][training_attribute_id]" class="form-control training-attribute-select" required>
                            <option value="">Select Attribute</option>
                            @if(isset($trainingAttributes))
                                @foreach($trainingAttributes as $attr)
                                    <option value="{{ $attr->id }}">{{ $attr->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="player_trainings[INDEX][start_date]" class="form-control" required>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" name="player_trainings[INDEX][end_date]" class="form-control" required>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Score (0-100)</label>
                        <input type="number" name="player_trainings[INDEX][score]" class="form-control" min="0" max="100" step="0.01" placeholder="Optional">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="player_trainings[INDEX][message]" class="form-control" rows="1" placeholder="Optional notes"></textarea>
                    </div>
                </div>
                
                <div class="col-md-1">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-danger btn-block remove-training-btn">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let formIndex = 0;
    
    // Add training form
    document.addEventListener('click', function(e) {
        if (e.target.closest('.add-training-btn')) {
            const playerId = e.target.closest('.add-training-btn').dataset.playerId;
            addTrainingForm(playerId);
        }
        
        // Remove training form
        if (e.target.closest('.remove-training-btn')) {
            e.target.closest('.training-form').remove();
        }
        
        // Edit score button
        if (e.target.closest('.edit-score-btn')) {
            const btn = e.target.closest('.edit-score-btn');
            const playerId = btn.dataset.playerId;
            const attributeId = btn.dataset.attributeId;
            const currentScore = btn.dataset.currentScore;
            
            editScore(playerId, attributeId, currentScore);
        }
    });
    
    function addTrainingForm(playerId) {
        const container = document.getElementById(`training-forms-${playerId}`);
        const template = document.getElementById('training-form-template');
        const clone = template.content.cloneNode(true);
        
        // Replace placeholders
        const html = clone.firstElementChild.outerHTML
            .replace(/INDEX/g, formIndex)
            .replace(/PLAYER_ID/g, playerId);
        
        container.insertAdjacentHTML('beforeend', html);
        
        // Initialize Select2 for the new form
        const newForm = container.lastElementChild;
        $(newForm).find('.training-attribute-select').select2({
            placeholder: 'Select Training Attribute',
            allowClear: true
        });
        
        // Set default dates (today and 30 days from now)
        const today = new Date().toISOString().split('T')[0];
        const futureDate = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        
        newForm.querySelector('input[name*="start_date"]').value = today;
        newForm.querySelector('input[name*="end_date"]').value = futureDate;
        
        formIndex++;
    }
    
    function editScore(playerId, attributeId, currentScore) {
        // Add a form to edit existing training score
        const container = document.getElementById(`training-forms-${playerId}`);
        const template = document.getElementById('training-form-template');
        const clone = template.content.cloneNode(true);
        
        // Replace placeholders
        const html = clone.firstElementChild.outerHTML
            .replace(/INDEX/g, formIndex)
            .replace(/PLAYER_ID/g, playerId);
        
        container.insertAdjacentHTML('beforeend', html);
        
        const newForm = container.lastElementChild;
        
        // Pre-fill the form for editing
        newForm.querySelector('select[name*="training_attribute_id"]').value = attributeId;
        newForm.querySelector('input[name*="score"]').value = currentScore || '';
        newForm.querySelector('input[name*="score"]').focus();
        
        // Make the form look different for editing
        newForm.classList.add('border-warning');
        newForm.querySelector('.card-body').insertAdjacentHTML('afterbegin', 
            '<div class="alert alert-warning alert-sm mb-3"><i class="fas fa-edit"></i> Editing existing training score</div>'
        );
        
        // Initialize Select2
        $(newForm).find('.training-attribute-select').select2({
            placeholder: 'Select Training Attribute',
            allowClear: true
        });
        
        formIndex++;
    }
    
    // Form validation
    document.getElementById('playerTrainingForm').addEventListener('submit', function(e) {
        const forms = document.querySelectorAll('.training-form');
        if (forms.length === 0) {
            alert('Please add at least one training assignment.');
            e.preventDefault();
            return false;
        }
        
        // Validate each form
        let hasErrors = false;
        forms.forEach(function(form) {
            const attribute = form.querySelector('select[name*="training_attribute_id"]').value;
            const startDate = form.querySelector('input[name*="start_date"]').value;
            const endDate = form.querySelector('input[name*="end_date"]').value;
            
            if (!attribute || !startDate || !endDate) {
                hasErrors = true;
                form.classList.add('border-danger');
            } else {
                form.classList.remove('border-danger');
            }
            
            if (new Date(endDate) < new Date(startDate)) {
                hasErrors = true;
                form.classList.add('border-danger');
                form.querySelector('input[name*="end_date"]').classList.add('is-invalid');
            }
        });
        
        if (hasErrors) {
            alert('Please fix the highlighted errors before submitting.');
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endsection