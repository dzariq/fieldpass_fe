@extends('backend.layouts.master')

@section('title')
    {{ __('competition - Admin Panel') }}
@endsection

@section('styles')
    <!-- Start datatable css -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.jqueryui.min.css">
    <style>
        .price-badge {
            font-size: 16px;
            font-weight: 600;
            padding: 8px 12px;
        }
        .price-free {
            background-color: #d4edda;
            color: #155724;
        }
        .price-paid {
            background-color: #fff3cd;
            color: #856404;
        }
    </style>
@endsection

@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">{{ __('competitions') }}</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><span>{{ __('All Competitions') }}</span></li>
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
                    <h4 class="header-title float-left">{{ __('Competition Invitations') }}</h4>
                    <div class="clearfix"></div>
                    <div class="data-tables">
                        @include('backend.layouts.partials.messages')
                        <table id="dataTable" class="text-center">
                            <thead class="bg-light text-capitalize">
                                <tr>
                                    <th width="5%">{{ __('Sl') }}</th>
                                    <th width="15%">{{ __('Competition Name') }}</th>
                                    <th width="10%">{{ __('Price') }}</th>
                                    <th width="10%">{{ __('Status') }}</th>
                                    <th width="15%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                               @foreach ($invites as $competition)
                               <tr>
                                    <td>{{ $loop->index+1 }}</td>
                                    <td>{{ $competition->competition->name }}</td>
                                    <td>
                                        @php
                                            $price = $competition->competition->price ?? 0;
                                        @endphp
                                        @if($price > 0)
                                            <span class="badge price-paid price-badge">
                                                RM {{ number_format($price, 2) }}
                                            </span>
                                        @else
                                            <span class="badge price-free price-badge">
                                                FREE
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info mr-1">
                                            {{ $competition->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if (auth()->user()->can('competition.manage_invites'))
                                            @if($price > 2.00)
                                                <button 
                                                    type="button"
                                                    class="btn btn-success text-white approve-paid-btn" 
                                                    data-invite-id="{{ $competition->id }}"
                                                    data-competition-name="{{ $competition->competition->name }}"
                                                    data-price="{{ $price }}">
                                                    <i class="fa fa-credit-card"></i> Approve & Pay
                                                </button>
                                            @else
                                                <a class="btn btn-success text-white" href="{{ route('admin.competition.invites.approve', $competition->id) }}">
                                                    <i class="fa fa-check"></i> Approve
                                                </a>
                                            @endif
                                            <a class="btn btn-danger text-white" href="{{ route('admin.competition.invites.reject', $competition->id) }}">
                                                <i class="fa fa-times"></i> Reject
                                            </a>
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

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="paymentModalLabel">
                    <i class="fa fa-exclamation-triangle"></i> Payment Required
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="payment-form" method="POST" action="{{ route('admin.competition.invites.approve.payment') }}">
                @csrf
                <input type="hidden" name="invite_id" id="form-invite-id">
                <div class="modal-body">
                    <p class="lead">You are about to approve this competition:</p>
                    <div class="alert alert-info">
                        <strong>Competition:</strong> <span id="modal-competition-name"></span><br>
                        <strong>Registration Fee:</strong> RM <span id="modal-price"></span>
                    </div>
                    <p>
                        <i class="fa fa-info-circle"></i> Clicking "Proceed to Payment" will process the payment and approve your participation.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success" id="confirm-payment-btn">
                        <i class="fa fa-credit-card"></i> Proceed to Payment
                    </button>
                </div>
            </form>
        </div>
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
        $(document).ready(function() {
            // Initialize DataTable
            if ($('#dataTable').length) {
                $('#dataTable').DataTable({
                    responsive: true
                });
            }

            // Handle paid competition approval - Use event delegation for dynamically loaded content
            $(document).on('click', '.approve-paid-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const inviteId = $(this).data('invite-id');
                const competitionName = $(this).data('competition-name');
                const price = $(this).data('price');

                console.log('Button clicked:', inviteId, competitionName, price); // Debug log

                $('#form-invite-id').val(inviteId);
                $('#modal-competition-name').text(competitionName);
                $('#modal-price').text(parseFloat(price).toFixed(2));
                
                $('#paymentModal').modal('show');
            });

            // Show loading state on form submit
            $('#payment-form').on('submit', function(e) {
                const btn = $('#confirm-payment-btn');
                btn.prop('disabled', true);
                btn.html('<i class="fa fa-spinner fa-spin"></i> Processing...');
            });
        });
     </script>
@endsection