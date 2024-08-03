<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="author" content="{!! $META_AUTHOR !!}">
<meta name="description" content="{!! $META_DESCRIPTION !!}">
<meta name="keywords" content="{!! $META_KEYWORDS !!}">
<meta name="csrf-token" content="{!! csrf_token() !!}">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<title>{!! $APP_NAME !!}@hasSection("title")
        - @yield("title")
    @endif @hasSection("extra_title")
        - @yield("extra_title")
    @endif</title>
