<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="robots" content="noindex">
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
    <link rel="apple-touch-icon" sizes="57x57" href="/logos/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/logos/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/logos/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/logos/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/logos/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/logos/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/logos/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/logos/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/logos/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/logos/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/logos/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/logos/favicon-16x16.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="msapplication-TileColor" content="#039BE5">
    <meta name="msapplication-TileImage" content="/logos/ms-icon-144x144.png">

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
                {str: "Hem", href: "/"},
                    @if(Auth::check())
                {
                    str: "Mina bokningar", href: "/user"
                },
                    @endif
                    @if(Auth::check() && Auth::user()->isManager())
                {
                    str: "Administrera", href: "/admin"
                },
                @endif
            ]
        }
        $(document).ready(function () {
            $('.datepicker').datepicker({dateFormat: 'yy-mm-dd'});
            $('.timepicker').timepicker({timeFormat: 'H:i'});
        });
    </script>
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
    <script async src="//methone.datasektionen.se/bar.js"></script>
</body>
</html>
