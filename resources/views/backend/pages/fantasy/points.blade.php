@extends('backend.layouts.master')

@section('title')
{{ __('Fantasy Points - Admin Panel') }}
@endsection

@section('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css">

<style>
    .competition-badge {
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.938rem;
    }
    
    .badge-complete {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .badge-incomplete {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: center;
    }
</style>
@endsection

@section('admin-content')

<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">{{ __('Fantasy Points Management') }}</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><a href="{{ route('admin.fantasy.index') }}">{{ __('Fantasy') }}</a></li>
                    <li><span>{{ __('Points Rules') }}</span></li>
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
                    <h4 class="header-title float-left">{{ __('Fantasy Points Rules by Competition') }}</h4>
                    <p class="float-right mb-2">
                        @if (auth()->user()->can('fantasy.create'))
                            <a class="btn btn-primary text-white" href="{{ route('admin.fantasy.points.create') }}">
                                <i class="fa fa-plus"></i> {{ __('Create New Points Rules') }}
                            </a>
                        @endif
                    </p>
                    <div class="clearfix"></div>
                    
                    <div class="data-tables">
                        @include('backend.layouts.partials.messages')
                        
                        @if($competitionsWithPoints->count() > 0)
                            <table id="dataTable" class="text-center table table-striped">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="5%">{{ __('Sl') }}</th>
                                        <th width="30%">{{ __('Competition Name') }}</th>
                                        <th width="15%">{{ __('Positions') }}</th>
                                        <th width="20%">{{ __('Status') }}</th>
                                        <th width="15%">{{ __('Last Updated') }}</th>
                                        <th width="15%">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($competitionsWithPoints as $competition)
                                    <tr>
                                        <td>{{ $loop->index + 1 }}</td>
                                        <td class="text-left">
                                            <strong>{{ $competition->name }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ $competition->positions_count }} / 4 positions
                                            </span>
                                        </td>
                                        <td>
                                            @if($competition->positions_count == 4)
                                                <span class="competition-badge badge-complete">
                                                    ✓ Complete
                                                </span>
                                            @else
                                                <span class="competition-badge badge-incomplete">
                                                    ⚠ Incomplete
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::createFromTimestamp($competition->last_updated)->format('d M Y, h:i A') }}
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                    <a class="btn btn-success btn-sm" 
                                                       href="{{ route('admin.fantasy.points.edit', $competition->id) }}"
                                                       title="Edit Points">
                                                        <i class="fa fa-edit"></i> Edit
                                                    </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="alert alert-info text-center">
                                <i class="fa fa-info-circle fa-3x mb-3"></i>
                                <h5>No Fantasy Points Rules Found</h5>
                                <p>You haven't created any fantasy points rules yet.</p>
                                    <a href="{{ route('admin.fantasy.points.new') }}" class="btn btn-primary mt-3">
                                        <i class="fa fa-plus"></i> Create Your First Points Rules
                                    </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
<script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>

<script>
    if ($('#dataTable').length) {
        $('#dataTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[1, 'asc']],
            columnDefs: [
                { orderable: false, targets: [5] }
            ]
        });
    }
</script>
@endsection