@php use Illuminate\Http\Resources\MissingValue; @endphp
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="{{$LOCALE}}" dir="{{$DIRECTION}}">
<head>
@include('4myth-tools::partials.head')
@include('4myth-tools::partials.scripts')
</head>
@section('title',$pageTitle)
<body class="{{ config('4myth-tools.font_family_class') }}" style="direction: {!! $DIRECTION !!}">
<table class="table table-bordered table-condensed table-striped">
<tr class="text-center">
<th colspan="{{count($headerItems)}}">{{$pageTitle}}</th>
</tr>
<tr>
@foreach($headerItems as $k => $headerItem)
@php if (!is_array($headerItem)){ $headerItem = [$headerItem]; } @endphp
<th>
@foreach(['label', 'text', 'field', 'name'] as $hk)
@isset($headerItem[$hk])
{!! $headerItem[$hk] ?: '' !!}
@break
@endif
@endforeach
</th>
@endforeach
</tr>
@foreach($items as $itemKey => $item)
<tr>
@foreach($headerItems as $headerItem)
@php if (!is_array($headerItem)){ $headerItem = [$headerItem]; } @endphp
<td>
@foreach(['field','name','value'] as $hk)
@isset($headerItem[$hk])
@isset($item[$headerItem[$hk]])
@if(!$item[$headerItem[$hk]] instanceof MissingValue)
{!! $item[$headerItem[$hk]] ?: '' !!}
@endif
@break
@endif
@endif
@endforeach
</td>
@endforeach
</tr>
@endforeach
</table>
</body>
</html>
