<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="author" content="{!! $META_AUTHOR !!}">
<meta name="description" content="{!! $META_DESCRIPTION !!}">
<meta name="keywords" content="{!! $META_KEYWORDS !!}">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{!! csrf_token() !!}">
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui" name="viewport">
<title>{!! $APP_NAME !!}@hasSection("title")
        - @yield("title")
    @endif @hasSection("extra_title")
        - @yield("extra_title")
    @endif</title>
