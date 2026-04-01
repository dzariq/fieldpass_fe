@extends('backend.layouts.master')

@section('title')
Bulk Upload Players - Admin Panel
@endsection

@section('styles')
<style>
    .upload-area {
        border: 3px solid #dee2e6;
        border-radius: 10px;
        padding: 40px;
        text-align: center;
        background: #f8f9fa;
        cursor: pointer;
    }

    .upload-area:hover {
        border-color: #007bff;
        background: #e7f3ff;
    }

    .upload-icon {
        font-size: 4rem;
        color: #6c757d;
        margin-bottom: 1rem;
    }

    .file-name {
        margin-top: 1rem;
        font-weight: 600;
        color: #28a745;
    }

    .instructions {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .instructions h5 {
        color: #856404;
        margin-bottom: 15px;
    }

    .instructions ul {
        margin-bottom: 0;
        padding-left: 20px;
    }

    .instructions li {
        margin-bottom: 8px;
        color: #856404;
    }

    .error-list,
    .skipped-list {
        max-height: 300px;
        overflow-y: auto;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 15px;
    }

    .error-item,
    .skipped-item {
        padding: 5px 0;
        border-bottom: 1px solid #dee2e6;
    }

    .error-item:last-child,
    .skipped-item:last-child {
        border-bottom: none;
    }
</style>
@endsection

@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Bulk Upload Players</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.players.index') }}">All Players</a></li>
                    <li><span>Bulk Upload</span></li>
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
                    <h4 class="header-title">Bulk Upload Players</h4>

                    @include('backend.layouts.partials.messages')

                    <!-- Display Errors -->
                    @if(session('errors') && is_array(session('errors')))
                    <div class="alert alert-danger">
                        <h6><i class="fa fa-exclamation-triangle"></i> Errors Found:</h6>
                        <div class="error-list">
                            @foreach(session('errors') as $error)
                            <div class="error-item">{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Display Skipped -->
                    @if(session('skipped') && is_array(session('skipped')))
                    <div class="alert alert-warning">
                        <h6><i class="fa fa-info-circle"></i> Skipped Rows:</h6>
                        <div class="skipped-list">
                            @foreach(session('skipped') as $skip)
                            <div class="skipped-item">{{ $skip }}</div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Instructions -->
                    <div class="instructions">
                        <h5><i class="fa fa-info-circle"></i> Instructions</h5>
                        <ul>
                            <li><strong>Download the template</strong> first and fill in your player data</li>
                            <li>
                                <strong style="color: #dc3545;">Compulsory fields (Required):</strong>
                                <ul style="margin-top: 5px;">
                                    <li>Name</li>
                                    <li>Identity Number (IC/Passport)</li>
                                    <li>Position (Goalkeeper, Defender, Midfielder, Forward)</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Optional fields:</strong> email, country_code, phone, salary
                            </li>
                            <li><strong>Country Code:</strong> Enter phone country code (e.g., 60, 62, 65) - <em>optional</em></li>
                            <li><strong>Phone format:</strong> Enter numbers only without country code (e.g., 189932233) - <em>optional</em></li>
                            <li><strong>File format:</strong> CSV only, max size 2MB, max 100 players per upload</li>
                            <li>Players will be created with status <strong>INVITED</strong></li>
                            <li>Username will be auto-generated automatically</li>
                            <li>Duplicate IC numbers or phone numbers will be skipped</li>
                        </ul>
                    </div>

                    <!-- Download Template Button -->
                    <div class="text-center mb-4">
                        <a href="{{ route('admin.players.bulk.template') }}" class="btn btn-success btn-lg">
                            <i class="fa fa-download"></i> Download CSV Template
                        </a>
                    </div>

                    <hr class="my-4">

                    <!-- Upload Form -->
                    <form action="{{ route('admin.players.bulk.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf

                        <label for="fileInput" class="upload-area" id="uploadArea">
                            <i class="fa fa-cloud-upload-alt upload-icon"></i>
                            <h5>Click to browse CSV file</h5>
                            <p class="text-muted">Max size: 2MB</p>
                            <input type="file" name="file" id="fileInput" accept=".csv" style="display: none;" required>
                            <div class="file-name" id="fileName" style="display: none;"></div>
                        </label>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="uploadBtn" disabled>
                                <i class="fa fa-upload"></i> Upload Players
                            </button>
                            <a href="{{ route('admin.players.index') }}" class="btn btn-secondary btn-lg">
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
    (function($) {
        'use strict';

        $(document).ready(function() {
            var fileInput = document.getElementById('fileInput');
            var fileName = document.getElementById('fileName');
            var uploadBtn = document.getElementById('uploadBtn');

            // File selected event
            fileInput.addEventListener('change', function() {
                var file = this.files[0];
                if (file) {
                    if (!file.name.endsWith('.csv')) {
                        alert('Please select a CSV file only.');
                        this.value = '';
                        fileName.style.display = 'none';
                        uploadBtn.disabled = true;
                        return;
                    }
                    fileName.textContent = 'Selected: ' + file.name;
                    fileName.style.display = 'block';
                    uploadBtn.disabled = false;
                } else {
                    fileName.style.display = 'none';
                    uploadBtn.disabled = true;
                }
            });

            // Form submission
            document.getElementById('uploadForm').addEventListener('submit', function() {
                if (!fileInput.files || fileInput.files.length === 0) {
                    alert('Please select a CSV file to upload.');
                    return false;
                }
                uploadBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';
                uploadBtn.disabled = true;
            });
        });

    })(jQuery);
</script>
@endsection