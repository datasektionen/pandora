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

    <script type="text/javascript" src="/js/jquery.js"></script>
    <script type="text/javascript">
    window.tbaas_conf = {
        system_name: "bokning",
        target_id: "methone-container-replace",
        primary_color: "#42a5f5",
        secondary_color: "#ffffff",
        bar_color: "#039BE5",
        @if(Auth::guest())
        login_text: "Logga in",
        login_href: "/login",
        @else
        login_text: "Logga ut",
        login_href: "/logout",
        @endif

        topbar_items: [
        { str: "Hem", href: "/" },
        @if(Auth::check() && Auth::user()->isAdmin())
            { str: "Administrera", href: "/admin" },
        @endif
        ]
    }
    </script>
    <script async src="//methone.datasektionen.se"></script>
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