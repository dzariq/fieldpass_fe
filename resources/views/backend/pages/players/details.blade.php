@extends('playerbackend.layouts.master')

@section('title')
{{ __('Player Detail - Player Dashboard') }}
@endsection

@section('styles')
<!-- Start datatable css -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.jqueryui.min.css">
@endsection

@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">{{ $player->name }}</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="index.html">Home</a></li>
                    <li><span>{{ __('Player Details') }}</span></li>
                </ul>
            </div>
        </div>
        <div class="col-sm-6 clearfix">
            @include('playerbackend.layouts.partials.logout')
        </div>
    </div>
</div>
<!-- page title area end -->

<div class="container mt-5">
    <h2 class="mb-4">Player Performance Overview</h2>

    <canvas id="performanceChart" height="100"></canvas>

    <h3 class="mt-5">Match History</h3>
    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Date</th>
                <th>Opponent</th>
                <th>Goals</th>
                <th>Assists</th>
                <th>Played</th>
            </tr>
        </thead>
        <tbody>
            @foreach($history as $match)
            <tr>
                <td>{{ $match['match_date'] }}</td>
                <td>{{ $match['opponent'] }}</td>
                <td>{{ $match['goals'] }}</td>
                <td>{{ $match['assists'] }}</td>
                <td>{{ $match['played'] ? 'Yes' : 'No' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
<script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.3/js/responsive.bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartCtx = document.getElementById('performanceChart').getContext('2d');
    const chartPlayer = new Chart(chartCtx, {
        type: 'line',
        data: {
            labels: @json($performanceData['labels']),
            datasets: [
                {
                    label: 'Goals',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    data: @json($performanceData['goals']),
                    fill: false
                },
                {
                    label: 'Assists',
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    data: @json($performanceData['assists']),
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: 'Monthly Performance'
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endsection
