@php
    $funcType = ($usePublicPath ?? !1) ? 'public_path' : 'asset'
@endphp
<!doctype html>
<html
    lang="{{$LOCALE}}"
    dir="{{$DIRECTION}}"
>
<head>
    @include('4myth-tools::partials.head')
    <link
        href="{{ $funcType("storage/vendor/4myth/pdf-style/app.css") }}"
        rel="stylesheet"
        type="text/css"
    >
    <link
        href="{{ $funcType("storage/vendor/4myth/pdf-style/app-{$DIRECTION}.css") }}"
        rel="stylesheet"
        type="text/css"
    >
    <style>
        body {
            font-family: 'main-font', 'Sans', monospace !important;
        }
    </style>
</head>
@section('title',$pageTitle)
<body>
<table class="table table-bordered table-condensed table-striped">
<tr class="text-center">
    <th colspan="{{count($headerItems)}}">{{$pageTitle}}</th>
</tr>
<tr>
@foreach($headerItems as $k => $headerItem)
    <th>{!! ($headerItem['text'] ?? ($headerItem['label'] ?? ($headerItem['field'] ?? ($headerItem['name'] ?? (is_array($headerItem) ? $k : $headerItem))))) !!}</th>
@endforeach
</tr>

@foreach($items as $itemKey => $item)
<tr>
    @foreach($headerItems as $k => $headerItem)
        <td>{!! ($item[ ($headerItem['value'] ?? '') ] ?? ($item[ ($headerItem['field'] ?? '') ] ?? ($item[ ($headerItem['name'] ?? '') ] ?? (is_array($headerItem) ? $k : $headerItem)))) !!}</td>
    @endforeach
</tr>
@endforeach
</table>
</body>
</html>