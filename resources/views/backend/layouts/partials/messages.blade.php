@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Success!</strong> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Validation Errors!</strong>
        <ul style="margin-bottom: 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if (session('upload_errors'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>Upload Errors!</strong>
        <ul style="margin-bottom: 0; padding-left: 20px;">
            @foreach (session('upload_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if (session('upload_alert'))
    {{-- Same message as validation alert, plus a one-time browser popup for easy copy/paste on production --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            try {
                alert(@json(session('upload_alert')));
            } catch (e) {}
        });
    </script>
@endif

@if (session('otp_verify_alert'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            try {
                alert(@json(session('otp_verify_alert')));
            } catch (e) {}
        });
    </script>
@endif

@if (session('skipped'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <strong>Skipped Records!</strong>
        <ul style="margin-bottom: 0; padding-left: 20px;">
            @foreach (session('skipped') as $skip)
                <li>{{ $skip }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif