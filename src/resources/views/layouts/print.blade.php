<!doctype html>
<html
    lang="{{$LOCALE}}"
    dir="{{$DIRECTION}}"
>
<head>
    @include('4myth-tools::partials.head')

    <link
        href="{{ asset("storage/vendor/4myth/pdf-style/app.css") }}"
        rel="stylesheet"
        type="text/css"
    >
    <link
        href="{{ asset("storage/vendor/4myth/pdf-style/app-{$DIRECTION}.css") }}"
        rel="stylesheet"
        type="text/css"
    >
    <link
        href="{{ asset("storage/vendor/4myth/fonts/fontawesome-free/css/all.css") }}"
        rel="stylesheet"
        type="text/css"
    >
    <style>
        .main-print-buttons {
            position: fixed;
            width: auto;
        }

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
    <script>

    </script>
</head>

<body>
<div class="d-print-none main-print-buttons {{ $ALIGN }}">
    <div class="">
        <a
            href="javascript:void(0);"
            class="btn btn-dark"
            onclick="printWindow()"
        >
            {!! __( 'global.print' ) !!}
        </a>

        {{--<a--}}
        {{--    href="javascript:void(0)"--}}
        {{--    onclick="window.goBack()"--}}
        {{--    class='btn btn-danger'--}}
        {{-->--}}
        {{--    {!! __( 'global.back' ) !!}--}}
        {{--</a>--}}
    </div>
</div>
@yield('print_content')
<script src="{{asset('storage/vendor/4myth/js/jquery/jquery.min.js')}}"></script>

<script>

    // window.addEventListener("message", (event) => {
    //   // alert(event.data)
    //   // Do we trust the sender of this message?
    //   // if (event.origin !== "http://example.com:8080") {
    //   //   return
    //   // }
    //
    //   // event.source is window.opener
    //   // event.data is "hello there!"
    //
    // }, false);

    window.arabicString = str => {
        try {
            if(!str.toString().trim()) {
                return str
            }
            // console.log(str);
            let nStr = str.toString().replace(/9/g, '??').replace(/8/g, '??').replace(/7/g, '??').replace(/6/g, '??').replace(/5/g, '??').replace(/4/g, '??').replace(/3/g, '??').replace(/2/g, '??').replace(/1/g, '??').replace(/0/g, '??')

            // Fix Hijri Date
            if(str.split('-').length === 3) {
                nStr = nStr.replace(/-/g, '/').replace(/\/??/g, '/')
            }
            // console.log(nStr);
            return nStr
        }
        catch(e) {

        }
        return str
    }
    window.printWindow = () => {
        if(!window.print) return
        // alert(window.print())
        const inputClassName = 'd-print-none'
        const className = 'span-print'
        $(`.${className}`).remove()
        $(':input.' + inputClassName).each(function() {
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
    window.goBack = function() {
        if(window.opener) {
            window.close()
        }
        else {
            location.href = '{{ redirect()->back()->getTargetUrl() }}'
        }
    }

    $(document).ready(function() {
        $('.arabic-string').each((i, v) => {

            let elm = $(v), e
            if(elm.is('input')) {
                elm.val(arabicString(elm.val()))
            }
            else {
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
