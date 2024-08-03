<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{$LOCALE}}" dir="{{$DIRECTION}}">
<head>
@include('4myth-tools::partials.head')
@include('4myth-tools::partials.scripts')
@stack('styles')
</head>
<body class="{{ config('4myth-tools.font_family_class') }}" style="direction: {!! $DIRECTION !!}">
@yield('content')
@stack('scripts')
</body>
</html>
