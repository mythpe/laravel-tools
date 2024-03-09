@php
$funcType = ($usePublicPath ?? !1) ? 'public_path' : 'asset';
@endphp
<!doctype html>
<html lang="{{$LOCALE}}" dir="{{$DIRECTION}}">
<head>
    @include('4myth-tools::partials.head')
    <link href="{{ $funcType("storage/vendor/4myth/pdf-style/app.css") }}" rel="stylesheet" type="text/css">
    <link href="{{ $funcType("storage/vendor/4myth/pdf-style/app-{$DIRECTION}.css") }}" rel="stylesheet" type="text/css">
    <link href="{{ $funcType("storage/vendor/4myth/fonts/fontawesome-free/css/all.css") }}" rel="stylesheet" type="text/css">
    @stack('styles')
</head>

<body>
@yield('content')
<script src="{{$funcType('storage/vendor/4myth/js/jquery/jquery.min.js')}}"></script>

@stack('scripts')
</body>
</html>
