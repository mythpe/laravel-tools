@php
    $usePublicPath = $usePublicPath ?? !1;
@endphp

<link href="{{ assetUrl("storage/vendor/4myth/style/app.css", !$usePublicPath) }}" rel="stylesheet" type="text/css">
<link href="{{ assetUrl("storage/vendor/4myth/style/app-{$DIRECTION}.css", !$usePublicPath) }}" rel="stylesheet" type="text/css">
<link href="{{ assetUrl("storage/vendor/4myth/fonts/fontawesome/css/all.css", !$usePublicPath) }}" rel="stylesheet" type="text/css">

@if(!($usePublicPath && strtolower(PHP_OS_FAMILY) == 'windows'))
    <script src="{{ assetUrl('storage/vendor/4myth/js/jquery/jquery.min.js', !$usePublicPath) }}"></script>
@endif
