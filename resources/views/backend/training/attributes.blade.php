@extends('backend.layouts.master')

@section('title')
Training Attributes - Admin Panel
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .form-check-label {
        text-transform: capitalize;
    }
</style>
@endsection
@section('admin-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Training Attributes Configuration</h3>
                </div>
                
                <form id="attributesForm" action="{{ route('admin.training.attributes.submit') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @if(($clubId ?? null) === null)
                            <div class="alert alert-warning">
                                <strong>{{ __('No club linked') }}</strong>
                                <p class="mb-0">{{ __('Your admin account is not linked to any club in admin_club. Ask a super admin to assign you to a club before saving training attributes.') }}</p>
                            </div>
                        @endif

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

                        <div class="row mb-3">
                            <div class="col-12">
                                <button type="button" id="addAttribute" class="btn btn-success" @if(($clubId ?? null) === null) disabled @endif>
                                    <i class="fas fa-plus"></i> Add New Attribute
                                </button>
                                <small class="text-muted ml-2">Maximum 10 attributes allowed</small>
                            </div>
                        </div>

                        <div id="attributesContainer">
                            @forelse($attributes as $index => $attribute)
                                <div class="attribute-row mb-3" data-index="{{ $index }}">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-5">
                                                    <input type="hidden" name="attributes[{{ $index }}][id]" value="{{ $attribute->id }}">
                                                    <div class="form-group mb-0">
                                                        <label for="attribute_name_{{ $index }}">Attribute Name</label>
                                                        <input type="text" 
                                                               id="attribute_name_{{ $index }}"
                                                               name="attributes[{{ $index }}][name]" 
                                                               class="form-control @error('attributes.'.$index.'.name') is-invalid @enderror" 
                                                               value="{{ old('attributes.'.$index.'.name', $attribute->name) }}" 
                                                               placeholder="Enter attribute name"
                                                               required>
                                                        @error('attributes.'.$index.'.name')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-3">
                                                    <div class="form-group mb-0">
                                                        <label>Status</label>
                                                        <div class="custom-control custom-switch">
                                                            <input type="hidden" name="attributes[{{ $index }}][status]" value="inactive">
                                                            <input type="checkbox" 
                                                                   class="custom-control-input status-toggle" 
                                                                   id="status_{{ $index }}"
                                                                   name="attributes[{{ $index }}][status]"
                                                                   value="active"
                                                                   {{ old('attributes.'.$index.'.status', $attribute->status) === 'active' ? 'checked' : '' }}>
                                                            <label class="custom-control-label" for="status_{{ $index }}">
                                                                <span class="status-text">
                                                                    {{ old('attributes.'.$index.'.status', $attribute->status) === 'active' ? 'Active' : 'Inactive' }}
                                                                </span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                               
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="attribute-row mb-3" data-index="0">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-5">
                                                    <div class="form-group mb-0">
                                                        <label for="attribute_name_0">Attribute Name</label>
                                                        <input type="text" 
                                                               id="attribute_name_0"
                                                               name="attributes[0][name]" 
                                                               class="form-control" 
                                                               placeholder="Enter attribute name"
                                                               required>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-3">
                                                    <div class="form-group mb-0">
                                                        <label>Status</label>
                                                        <div class="custom-control custom-switch">
                                                            <input type="hidden" name="attributes[0][status]" value="inactive">
                                                            <input type="checkbox" 
                                                                   class="custom-control-input status-toggle" 
                                                                   id="status_0"
                                                                   name="attributes[0][status]"
                                                                   value="active"
                                                                   checked>
                                                            <label class="custom-control-label" for="status_0">
                                                                <span class="status-text">Active</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-danger btn-sm remove-attribute">
                                                        <i class="fas fa-trash"></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" @if(($clubId ?? null) === null) disabled @endif>
                            <i class="fas fa-save"></i> Save All Attributes
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let attributeIndex = {{ count($attributes) > 0 ? count($attributes) : 1 }};
    
    // Add new attribute
    document.getElementById('addAttribute').addEventListener('click', function() {
        const container = document.getElementById('attributesContainer');
        const currentCount = container.querySelectorAll('.attribute-row').length;
        
        if (currentCount >= 10) {
            alert('Maximum 10 attributes allowed');
            return;
        }
        
        const newRow = document.createElement('div');
        newRow.className = 'attribute-row mb-3';
        newRow.setAttribute('data-index', attributeIndex);
        
        newRow.innerHTML = `
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <div class="form-group mb-0">
                                <label for="attribute_name_${attributeIndex}">Attribute Name</label>
                                <input type="text" 
                                       id="attribute_name_${attributeIndex}"
                                       name="attributes[${attributeIndex}][name]" 
                                       class="form-control" 
                                       placeholder="Enter attribute name"
                                       required>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label>Status</label>
                                <div class="custom-control custom-switch">
                                    <input type="hidden" name="attributes[${attributeIndex}][status]" value="inactive">
                                    <input type="checkbox" 
                                           class="custom-control-input status-toggle" 
                                           id="status_${attributeIndex}"
                                           name="attributes[${attributeIndex}][status]"
                                           value="active"
                                           checked>
                                    <label class="custom-control-label" for="status_${attributeIndex}">
                                        <span class="status-text">Active</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-sm remove-attribute">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.appendChild(newRow);
        attributeIndex++;
        
        // Update add button state
        updateAddButtonState();
    });
    
    // Remove attribute
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-attribute')) {
            const row = e.target.closest('.attribute-row');
            const container = document.getElementById('attributesContainer');
            
            if (container.querySelectorAll('.attribute-row').length > 1) {
                row.remove();
                updateAddButtonState();
            } else {
                alert('At least one attribute is required');
            }
        }
    });
    
    // Status toggle functionality
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('status-toggle')) {
            const statusText = e.target.parentElement.querySelector('.status-text');
            const hiddenInput = e.target.parentElement.querySelector('input[type="hidden"]');
            
            if (e.target.checked) {
                statusText.textContent = 'Active';
                hiddenInput.disabled = true; // Disable hidden input when checkbox is checked
            } else {
                statusText.textContent = 'Inactive';
                hiddenInput.disabled = false; // Enable hidden input when checkbox is unchecked
            }
        }
    });
    
    // Initialize status toggles
    document.querySelectorAll('.status-toggle').forEach(function(toggle) {
        const statusText = toggle.parentElement.querySelector('.status-text');
        const hiddenInput = toggle.parentElement.querySelector('input[type="hidden"]');
        
        if (toggle.checked) {
            hiddenInput.disabled = true;
        }
    });
    
    function updateAddButtonState() {
        const container = document.getElementById('attributesContainer');
        const addButton = document.getElementById('addAttribute');
        const currentCount = container.querySelectorAll('.attribute-row').length;
        
        addButton.disabled = currentCount >= 10;
        
        if (currentCount >= 10) {
            addButton.classList.add('disabled');
        } else {
            addButton.classList.remove('disabled');
        }
    }
    
    // Initial state check
    updateAddButtonState();
});
</script>

<style>
.attribute-row .card {
    border-left: 4px solid #007bff;
}

.status-toggle:checked + .custom-control-label .status-text {
    color: #28a745;
    font-weight: bold;
}

.status-toggle:not(:checked) + .custom-control-label .status-text {
    color: #dc3545;
    font-weight: bold;
}

.btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
@endsection