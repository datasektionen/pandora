<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Skapa bokning</title>

    <!-- Fonts -->
    <link href="//aurora.datasektionen.se" rel="stylesheet" type="text/css">
    <link href="/css/app.css" rel="stylesheet" type="text/css">
</head>
<body>
    <div id="methone-container-replace" style="margin-bottom: -50px;"></div>
    <div id="application" class="blue">
        <header>
            <div class="header-inner">
                <div class="row">
                    <div class="header-left col-md-2">
                        <a href="/">&laquo; Tillbaka</a>
                    </div>
                    <div class="col-md-8">
                        <h2>@yield('title', 'Skapa bokning')</h2>
                    </div>
                    <div class="header-right col-md-2">
                        {{--<span class="visible-lg-inline">Se p&aring;</span>--}}
                        @yield('status')
                        @yield('action-button')
                        {{--<a href="https://github.com/datasektionen/skywhale" class="primary-action">GitHub</a>--}}
                    </div>
                </div>
                <div class="clear"></div>
            </div>
        </header>
        <div id="content">
            @yield('content')
            <form method="POST" action="https://val.datasektionen.se/admin/elections/new" accept-charset="UTF-8"><input name="_token" type="hidden" value="sVsfvXBuhacjHgHJ0kOuSaKhB5cNOXyhP1cld4WO">
                <div class="form">
                    <div class="form-entry">
                        <span class="description">
                            Ändamål:
                        </span>
                        <div class="input">
                            <input placeholder="T.ex. &quot;Möte&quot;" name="name" type="text">
                        </div>
                    </div>

                    <div class="form-entry">
                        <span class="description">
                            Anledning till bokningstillfället:
                        </span>
                        <div class="input">
                            <textarea placeholder="T.ex. &quot;Projektgruppen måste ha ett möte.&quot;" class="textarea" name="description" cols="50" rows="5"></textarea>
                        </div>
                    </div>

                    <div class="form-entry">
                        <span class="description">
                            Börjar:
                        </span>
                        <div class="input">
                            <input name="opens" type="datetime-local">
                        </div>
                    </div>

                    <div class="form-entry">
                        <span class="description">
                            Slutar:
                        </span>
                        <div class="input">
                            <input name="nomination_stop" type="datetime-local">
                        </div>
                    </div>

                    <div class="form-entry">
                        <div class="input">
                            <input name="election" type="hidden" value="1">
                            <input type="submit" value="Lägg bokning">
                        </div>
                    </div>
                </div>
            </form>
            <div class="clear"></div>
        </div>
    </div>
        {{--
    <div id="top-bar-parent"></div>
    <div id="top-bar-push"></div>
    <div class="header">    
        <div class="center">
            @yield('action-button')
            <h1><span>@yield('title', 'Rubrik')</span></h1>
        </div>
    </div>
    <div class="wrapper">
        @include('includes.messages')
        @yield('content')
    </div>
    --}}
    
</body>
</html>
