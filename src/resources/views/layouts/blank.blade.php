<!doctype html>
<html lang="{{$LOCALE}}" dir="{{$DIRECTION}}">
<head>
    @include('4myth-tools::partials.head')
    <link href="{{ asset("storage/vendor/4myth/pdf-style/app.css") }}" rel="stylesheet" type="text/css">
    <link href="{{ asset("storage/vendor/4myth/pdf-style/app-{$DIRECTION}.css") }}" rel="stylesheet" type="text/css">
    <link href="{{ asset("storage/vendor/4myth/fonts/fontawesome-free/css/all.css") }}" rel="stylesheet" type="text/css">
    @stack('styles')
</head>

<body>
@yield('content')
<script src="{{asset('storage/vendor/4myth/js/jquery/jquery.min.js')}}"></script>

@stack('scripts')
</body>
</html>
