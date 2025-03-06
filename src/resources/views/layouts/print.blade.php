<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{$LOCALE}}" dir="{{$DIRECTION}}">
<head>
@include('4myth-tools::partials.head')
@include('4myth-tools::partials.scripts')
<style>
.main-print-buttons {
z-index: 99999;
position: fixed;
width: auto;
}
+
.main-print-buttons.left {
left: 0;
right: auto;
}

.main-print-buttons.right {
right: 0;
left: auto;
}
</style>
@stack('styles')
</head>
<body class="{{ config('4myth-tools.font_family_class') }}" style="direction: {!! $DIRECTION !!}">
<div class="d-print-none main-print-buttons {{ $ALIGN }}">
<div class=""><a href="javascript:void(0);" class="btn btn-dark" onclick="printWindow()">{!! __( 'labels.print' ) !!}</a></div>
</div>
@yield('print_content')
<script>
window.arabicString = str => {
try {
if (!str.toString().trim()) {
return str
}
// console.log(str);
let nStr = str.toString().replace(/9/g, '٩').replace(/8/g, '٨').replace(/7/g, '٧').replace(/6/g, '٦').replace(/5/g, '٥').replace(/4/g, '٤').replace(/3/g, '٣').replace(/2/g, '٢').replace(/1/g, '١').replace(/0/g, '٠')

// Fix Hijri Date
if (str.split('-').length === 3) {
nStr = nStr.replace(/-/g, '/').replace(/\/٠/g, '/')
}
// console.log(nStr);
return nStr
} catch (e) {

}
return str
}
window.printWindow = () => {
if (!window.print) return
// alert(window.print())
const inputClassName = 'd-print-none'
const className = 'span-print'
$(`.${className}`).remove()
$(':input.' + inputClassName).each(function () {
const elm = $(this)
const span = $('<span></span>')
span.attr('class', className)
span.html(elm.is('select') ? elm.find('option:selected').text() : elm.val().toString().replace(/\n/g, '<br />'))
span.insertAfter(elm)
})

// $(document).ready(function() {
window.print()
$(`.${className}`).remove()
//   $('.arabic-string').each((i, v) => {
//     let s = arabicString($(v).text());
//   });
// });
}
window.goBack = function () {
if (window.opener) {
window.close()
} else {
location.href = '{{ redirect()->back()->getTargetUrl() }}'
}
}

$(document).ready(function () {
$('.arabic-string').each((i, v) => {

let elm = $(v), e
if (elm.is('input')) {
elm.val(arabicString(elm.val()))
} else {
elm.text(arabicString(elm.text()))
}
// (e = elm.text()) &&
// elm.text(arabicString(e));
})
})
</script>
@stack('scripts')
</body>
</html>
