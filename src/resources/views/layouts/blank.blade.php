<!doctype html>
<html lang="{{$LOCALE}}" dir="{{$DIRECTION}}">
<head>
@include('4myth-tools::partials.head')
@include('4myth-tools::partials.scripts')
@stack('styles')
</head>
<body class="{{ config('4myth-tools.font_family_class') }}">
@yield('content')
@stack('scripts')
</body>
</html>
