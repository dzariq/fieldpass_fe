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
                            <li><strong style="color: #dc3545;">Before upload:</strong> select the <strong>club</strong> (from your association) that all players in this file will be assigned to.</li>
                            <li>
                                <strong style="color: #dc3545;">Compulsory fields (Required):</strong>
                                <ul style="margin-top: 5px;">
                                    <li>Club (dropdown on this page)</li>
                                    <li>Name</li>
                                    <li><strong>id_type</strong> — enter exactly <code>Malaysia IC</code> or <code>Foreign ID</code> (same as a dropdown choice)</li>
                                    <li>Identity number — must match the ID type (see below)</li>
                                    <li>Position (Goalkeeper, Defender, Midfielder, Forward)</li>
                                    <li><strong>market_value</strong> — whole number from <strong>40</strong> to <strong>150</strong></li>
                                </ul>
                            </li>
                            <li>
                                <strong>Malaysia IC:</strong> use format <code>XXXXXX-XX-XXXX</code> only (six digits, hyphen, two digits, hyphen, four digits); must not already exist in the system.
                            </li>
                            <li>
                                <strong>Foreign ID:</strong> free text (e.g. passport), max 50 characters; must not already exist in the system.
                            </li>
                            <li>
                                <strong>Optional fields:</strong> country_code, phone
                            </li>
                            <li><strong>Country Code:</strong> Enter phone country code (e.g., 60, 62, 65) - <em>optional</em></li>
                            <li><strong>Phone format:</strong> Enter numbers only without country code (e.g., 189932233) - <em>optional</em></li>
                            <li><strong>File format:</strong> CSV only, max size 2MB, max 100 players per upload</li>
                            <li>Players will be created with status <strong>ACTIVE</strong></li>
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

                    @if($clubs->isEmpty())
                    <div class="alert alert-warning">
                        <strong>No clubs available.</strong> You need at least one club under your association (or assigned to your account) before you can bulk upload players.
                    </div>
                    @endif

                    <!-- Upload Form -->
                    <form action="{{ route('admin.players.bulk.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf

                        <div class="form-group mb-4">
                            <label for="club_id"><strong>Club</strong> <span class="text-danger">*</span></label>
                            <select name="club_id" id="club_id" class="form-control form-control-lg" required {{ $clubs->isEmpty() ? 'disabled' : '' }}>
                                <option value="">— Select a club —</option>
                                @foreach($clubs as $c)
                                <option value="{{ $c->id }}" {{ old('club_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">All players in this upload will be linked to this club and given an active contract (start today, one year).</small>
                        </div>

                        <label for="fileInput" class="upload-area" id="uploadArea">
                            <i class="fa fa-cloud-upload-alt upload-icon"></i>
                            <h5>Click to browse CSV file</h5>
                            <p class="text-muted">Max size: 2MB</p>
                            <input type="file" name="file" id="fileInput" accept=".csv" style="display: none;" required>
                            <div class="file-name" id="fileName" style="display: none;"></div>
                        </label>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="uploadBtn" disabled {{ $clubs->isEmpty() ? 'disabled' : '' }}>
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

        var BULK_EXPECTED_HEADERS = ['name', 'id_type', 'identity_number', 'country_code', 'phone', 'position', 'market_value'];
        var BULK_MY_IC_RE = /^\d{6}-\d{2}-\d{4}$/;

        function stripBom(s) {
            if (!s || !s.length) return s;
            if (s.charCodeAt(0) === 0xFEFF) return s.slice(1);
            return s;
        }

        function parseCsvLine(line) {
            var result = [];
            var cur = '';
            var inQuotes = false;
            for (var i = 0; i < line.length; i++) {
                var c = line[i];
                if (c === '"') {
                    inQuotes = !inQuotes;
                    continue;
                }
                if (!inQuotes && c === ',') {
                    result.push(cur);
                    cur = '';
                    continue;
                }
                cur += c;
            }
            result.push(cur);
            return result.map(function (cell) { return cell.trim(); });
        }

        function validateBulkCsvText(text) {
            var errors = [];
            var lines = text.replace(/\r\n/g, '\n').replace(/\r/g, '\n').split('\n').filter(function (ln) {
                return ln.trim() !== '';
            });
            if (lines.length < 2) {
                errors.push('CSV must include a header row and at least one data row.');
                return errors;
            }
            var header = parseCsvLine(stripBom(lines[0])).map(function (h) {
                return h.replace(/^\uFEFF/, '').trim();
            });
            if (header.length !== BULK_EXPECTED_HEADERS.length) {
                errors.push('Wrong number of columns in header. Download the latest template.');
                return errors;
            }
            for (var h = 0; h < BULK_EXPECTED_HEADERS.length; h++) {
                if (header[h].toLowerCase() !== BULK_EXPECTED_HEADERS[h]) {
                    errors.push('Invalid header in column ' + (h + 1) + '. Expected "' + BULK_EXPECTED_HEADERS[h] + '", got "' + header[h] + '".');
                    return errors;
                }
            }
            for (var r = 1; r < lines.length; r++) {
                var row = parseCsvLine(lines[r]);
                if (row.every(function (c) { return c === ''; })) {
                    continue;
                }
                var rowNum = r + 1;
                var idType = (row[1] || '').trim().replace(/\s+/g, ' ').toLowerCase();
                var identity = (row[2] || '').replace(/^[\t']+/, '').trim();
                if (idType === 'malaysia ic' && !BULK_MY_IC_RE.test(identity)) {
                    errors.push('Row ' + rowNum + ': Malaysia IC must be XXXXXX-XX-XXXX.');
                }
                var mvRaw = (row[6] || '').replace(/^[\t']+/, '').trim();
                if (!/^\d+$/.test(mvRaw)) {
                    errors.push('Row ' + rowNum + ': market_value must be a whole number between 40 and 150.');
                } else {
                    var mv = parseInt(mvRaw, 10);
                    if (mv < 40 || mv > 150) {
                        errors.push('Row ' + rowNum + ': market_value must be between 40 and 150.');
                    }
                }
            }
            return errors;
        }

        $(document).ready(function() {
            var fileInput = document.getElementById('fileInput');
            var fileName = document.getElementById('fileName');
            var uploadBtn = document.getElementById('uploadBtn');
            var clubSelect = document.getElementById('club_id');
            var uploadForm = document.getElementById('uploadForm');

            function updateUploadButtonState() {
                var hasFile = fileInput.files && fileInput.files.length > 0;
                var hasClub = clubSelect && clubSelect.value !== '';
                uploadBtn.disabled = !hasFile || !hasClub || clubSelect.disabled;
            }

            if (clubSelect) {
                clubSelect.addEventListener('change', updateUploadButtonState);
            }

            // File selected event
            fileInput.addEventListener('change', function() {
                var file = this.files[0];
                if (file) {
                    if (!file.name.endsWith('.csv')) {
                        alert('Please select a CSV file only.');
                        this.value = '';
                        fileName.style.display = 'none';
                        updateUploadButtonState();
                        return;
                    }
                    fileName.textContent = 'Selected: ' + file.name;
                    fileName.style.display = 'block';
                    updateUploadButtonState();
                } else {
                    fileName.style.display = 'none';
                    updateUploadButtonState();
                }
            });

            updateUploadButtonState();

            uploadForm.addEventListener('submit', function(e) {
                e.preventDefault();
                if (!clubSelect.value) {
                    alert('Please select a club.');
                    return;
                }
                if (!fileInput.files || !fileInput.files.length) {
                    alert('Please select a CSV file to upload.');
                    return;
                }
                var file = fileInput.files[0];
                var form = uploadForm;
                var origHtml = uploadBtn.innerHTML;
                uploadBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Checking...';
                uploadBtn.disabled = true;
                var reader = new FileReader();
                reader.onload = function() {
                    var errs = validateBulkCsvText(reader.result);
                    if (errs.length) {
                        alert(errs.slice(0, 25).join('\n') + (errs.length > 25 ? '\n…' : ''));
                        uploadBtn.innerHTML = origHtml;
                        updateUploadButtonState();
                        return;
                    }
                    uploadBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';
                    form.submit();
                };
                reader.onerror = function() {
                    alert('Could not read the CSV file.');
                    uploadBtn.innerHTML = origHtml;
                    updateUploadButtonState();
                };
                reader.readAsText(file);
            });
        });

    })(jQuery);
</script>
@endsection