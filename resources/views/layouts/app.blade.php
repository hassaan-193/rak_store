<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.header')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    @include('partials.preloader')
    @include('partials.navbar')
    @include('partials.sidebar')

    <div class="content-wrapper">
        @yield('content')
    </div>

    @include('partials.footer')

</div>

<!-- Add necessary JavaScript files here -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
