@extends('backend.layouts.master')

@section('title')
    {{ __('Players - Admin Panel') }}
@endsection

@section('styles')
    <!-- Start datatable css -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.jqueryui.min.css">
    <style>
        .position-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 12px;
        }
        .position-goalkeeper {
            background-color: #ffc107;
            color: #000;
        }
        .position-defender {
            background-color: #28a745;
            color: #fff;
        }
        .position-midfielder {
            background-color: #17a2b8;
            color: #fff;
        }
        .position-forward {
            background-color: #dc3545;
            color: #fff;
        }
    </style>
@endsection

@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">{{ __('Players') }}</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><span>{{ __('All Players') }}</span></li>
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
                    <h4 class="header-title float-left">{{ __('Players') }}</h4>
                    <p class="float-right mb-2">
                        @if (auth()->user()->can('players.edit'))
                            <a class="btn btn-primary text-white" href="{{ route('admin.players.create') }}">
                                {{ __('Create New Player') }}
                            </a>
                        @endif
                    </p>
                    <div class="clearfix"></div>
                    <div class="data-tables">
                        @include('backend.layouts.partials.messages')
                        <table id="dataTable" class="text-center">
                            <thead class="bg-light text-capitalize">
                                <tr>
                                    <th width="5%">{{ __('Sl') }}</th>
                                    <th width="15%">{{ __('Name') }}</th>
                                    <th width="20%">{{ __('ID') }}</th>
                                    <th width="8%">{{ __('Jersey Number') }}</th>
                                    <th width="10%">{{ __('Phone') }}</th>
                                    <th width="8%">{{ __('Position') }}</th>
                                    <th width="15%">{{ __('Clubs') }}</th>
                                    <th width="8%">{{ __('Status') }}</th>
                                    <th width="10%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                               @foreach ($players as $player)
                               <tr>
                                    <td>{{ $loop->index+1 }}</td>
                                    <td>
                                        <a href="{{ route('player.details', ['id' => $player->id]) }}">
                                            {{ $player->name }}
                                        </a>
                                    </td>
                                    <td>{{ $player->identity_number }}</td>
                                    <td>{{ $player->jersey_number }}</td>
                                    <td>
                                        @if($player->country_code && $player->phone)
                                            +{{ str_replace('+', '', $player->country_code) }}{{ $player->phone }}
                                        @elseif($player->phone)
                                            {{ $player->phone }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($player->position)
                                            @php
                                                $positionClass = 'position-' . strtolower($player->position);
                                            @endphp
                                            <span class="badge position-badge {{ $positionClass }}">
                                                {{ $player->position }}
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @foreach ($player->clubs as $club)
                                            <span class="badge badge-info mr-1">
                                                {{ $club->name }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <span class="badge badge-info mr-1">
                                            {{ $player->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if (auth()->user()->can('players.edit'))
                                            <a class="btn btn-success text-white btn-sm" href="{{ route('admin.players.edit', $player->id) }}">Edit</a>
                                        @endif
                                        
                                        @if (auth()->user()->can('players.delete'))
                                        <a class="btn btn-danger text-white btn-sm" href="javascript:void(0);"
                                        onclick="event.preventDefault(); if(confirm('Are you sure you want to delete?')) { document.getElementById('delete-form-{{ $player->id }}').submit(); }">
                                            {{ __('Delete') }}
                                        </a>

                                        <form id="delete-form-{{ $player->id }}" action="{{ route('admin.players.destroy', $player->id) }}" method="POST" style="display: none;">
                                            @method('DELETE')
                                            @csrf
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                               @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- data table end -->
    </div>
</div>
@endsection

@section('scripts')
     <!-- Start datatable js -->
     <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
     <script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
     <script src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>
     <script src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>
     <script src="https://cdn.datatables.net/responsive/2.2.3/js/responsive.bootstrap.min.js"></script>
     
     <script>
        if ($('#dataTable').length) {
            $('#dataTable').DataTable({
                responsive: true
            });
        }
     </script>
@endsection