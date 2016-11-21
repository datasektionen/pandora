<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <title>@yield('title')</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <style>@import url(https://fonts.googleapis.com/css?family=Lato:400,300,700,400italic,700italic,900);
            * {
                font-family: "Lato", sans-serif;
                line-height: 1.4;
            }
            body {
                margin:0;
                padding: 0;
            }
            a {
                color: #f0c;
            }
        </style>
    </head>
    <body>
        <div class="outer" style="background-color:#F7F7F7;margin:0;padding:0;border:0">
            <div class="main" style="max-width: 700px;margin:0 auto;padding:0;border:0">
                <div class="top" style="background-color:#EE2A7B;margin:0;padding:0;border:0;text-align:center;height:10px;">
                </div>
                <div class="content" style="background-color:#FFF;padding: 30px 30px;margin:0;border:0">
                    @yield('content')
                </div>
                <div class="footer" style="background-color:#EE2A7B;margin:0;padding:0;border:0;text-align:center;">
                    <img src="https://www.datasektionen.se/static/gfx/Skold_Vit_Final.png" style="height:100px;width:100px;margin:30px auto 0;text-align:center;"/>
                    <h1 style="color:#FFF;text-align:center;font-size:30px;height:30px;padding:0px 0 29px 0;-webkit-margin-before: 0px; -webkit-margin-after: 0px;margin:0;border:0">Konglig Datasektionen</h1>
                </div>
            </div>
        </div>
    </body>
</html>