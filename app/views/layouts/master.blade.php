<!DOCTYPE html>
<html>
    <head>
        <title>
@section('title')
            RoseHosting.com Testing
            @show
                </title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!-- CSS are placed here -->
        {{ HTML::style('assets/css/bootstrap.css') }}
        <style>
            @section('styles')
            body {
                padding-top: 60px;
            }
            @show
            </style>
        {{ HTML::style('assets/css/bootstrap-responsive.css') }}

    </head>

    <body>
        <!-- Navbar -->
        <div class="navbar navbar-inverse navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">
                    <!-- .btn-navbar is used as the toggle for collapsed navbar content -->
                    <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>

                    <a class="brand" href="#">Laravel</a>

                    <!-- Everything you want hidden at 940px or less, place within here -->
                    <div class="nav-collapse collapse">
                        <ul class="nav">
                            <li><a href="{{{ URL::to('') }}}">Home</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Container -->
        <div class="container">

            <!-- Content -->
            @yield('content')

                    </div>

        <!-- Scripts are placed here -->
        {{ HTML::script('js/jquery-1.10.1.min.js') }}
        {{ HTML::script('js/bootstrap/bootstrap.min.js') }}

    </body>
</html>