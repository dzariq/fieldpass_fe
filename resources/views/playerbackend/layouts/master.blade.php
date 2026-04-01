<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>@yield('title', 'Laravel Role Player')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('playerbackend.layouts.partials.styles')
    @yield('styles')
</head>

<body>
    <!--[if lt IE 8]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->
    <!-- preloader area start -->
    <div id="preloader">
        <div class="loader"></div>
    </div>
    <!-- preloader area end -->
    <!-- page container area start -->
    <div class="page-container">

       @include('playerbackend.layouts.partials.sidebar')

        <!-- main content area start -->
        <div class="main-content">
            @include('playerbackend.layouts.partials.header')
            @yield('player-content')
        </div>
        <!-- main content area end -->
        @include('playerbackend.layouts.partials.footer')
    </div>
    <!-- page container area end -->

    @include('playerbackend.layouts.partials.offsets')
    @include('playerbackend.layouts.partials.scripts')
    @yield('scripts')
</body>

</html>
