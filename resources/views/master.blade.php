<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Hem') - Bokningssystemet på Datasektionen</title>

    <!-- Fonts -->
    <link href="//aurora.datasektionen.se" rel="stylesheet" type="text/css">
    <link href="/css/app.css" rel="stylesheet" type="text/css">
    <link href="/css/jquery-ui.css" rel="stylesheet" type="text/css">
    <link href="/css/jquery.timepicker.css" rel="stylesheet" type="text/css">
    <meta name="theme-color" content="#039BE5">
    
    <script type="text/javascript" src="/js/jquery.js"></script>
    <script type="text/javascript" src="/js/jquery-ui.js"></script>
    <script type="text/javascript" src="/js/jquery.timepicker.js"></script>
    <script type="text/javascript">
    window.methone_conf = {
        system_name: "pandora",
        color_scheme: "blue",

        @if(Auth::guest())
        login_text: "Logga in",
        login_href: "/login",
        @else
        login_text: "Logga ut",
        login_href: "/logout",
        @endif

        links: [
        { str: "Hem", href: "/" },
        @if(Auth::check() && Auth::user()->isSomeAdmin())
            { str: "Administrera", href: "/admin" },
        @endif
        ]
    }
    $(document).ready(function () {
        $('.datepicker').datepicker({ dateFormat: 'yy-mm-dd'});
        $('.timepicker').timepicker({ timeFormat: 'H:i' });
    });
    </script>
    <script async src="//methone.datasektionen.se/bar.js"></script>
    @yield('head-js')
</head>
<body>
    <div id="methone-container-replace"></div>
    <div id="application" class="blue">
        <header>
            <div class="header-inner">
                <div class="row">
                    <div class="header-left col-md-2">
                        @yield('header-left')
                    </div>
                    <div class="col-md-8">
                        <h2>@yield('title', 'Bokningar för lokal: Mötesrummet')</h2>
                    </div>
                    <div class="header-right col-md-2">
                        @yield('action-button')
                    </div>
                </div>
                <div class="clear"></div>
            </div>
        </header>
        <div id="content">
            @include('includes.messages')
            @yield('content')
        </div>
    </div>
</body>
</html>