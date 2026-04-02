@if (session(\App\Http\Controllers\Backend\AdminImpersonationController::SESSION_IMPERSONATOR_KEY))
    <div class="alert alert-warning mb-0 rounded-0 border-0 border-bottom border-dark d-flex flex-wrap align-items-center justify-content-between px-3 py-2" role="alert" style="background: #fff3cd;">
        <span class="mb-1 mb-md-0 pr-2">
            <strong>Impersonation:</strong> you are using another admin&rsquo;s account. Actions use their roles and permissions.
        </span>
        <form action="{{ route('admin.impersonation.leave') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-dark">Return to my account</button>
        </form>
    </div>
@endif
