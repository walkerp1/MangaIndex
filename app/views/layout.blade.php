<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width" />
    <meta name="mobile-web-app-capable" content="yes">

    <title>@if(isset($pageTitle) && !empty($pageTitle)) {{{ $pageTitle }}} - @endif/a/ manga</title>

    <link rel="icon" type="image/png" href="{{{ URL::to('img/icon.png') }}}">

    {{ Minify::stylesheet(array($stylesheets, $additionalStylesheets)) }}
</head>
<body>
    @section('body')
        @yield('pageHeading')

        <div class="search-container">
            <form method="get" action="{{{ URL::route('search') }}}">
                <input type="text" name="q" placeholder="Search" class="input" id="search-input" required />
                <button class="button">Search</button>
            </form>

            <div class="mobile-break">
                <a href="{{{ URL::route('recent') }}}" class="button">Recent uploads</a>

                @if($user)
                    <a href="{{{ URL::route('notifications') }}}" class="button">
                        Notifications

                        @if(isset($notifyCount) && $notifyCount > 0)
                            <span class="notify-label">{{{ $notifyCount }}}</span>
                        @endif
                    </a>
                @endif

                <a href="{{{ URL::route('reports') }}}" class="button">
                    Reports

                    @if(isset($reportsCount) && $reportsCount > 0)
                        <span class="notify-label">{{{ $reportsCount }}}</span>
                    @endif
                </a>

                <a class="button" href="{{{ URL::route('donate') }}}">Donate</a>
            </div>
        </div> 

        @if(Session::has('error'))
            <div class="message message-error">{{{ Session::get('error') }}}</div>
        @endif

        @if(Session::has('success'))
            <div class="message message-success">{{{ Session::get('success') }}}</div>
        @endif

        @section('main')
            <div id="loli-madokai-container">
                <div id="loli-madokami"></div>
            </div>
        @show

        <footer>
            {{{ $statTotalSize }}} used<br/>
            #madokami @ rizon<br/>
            <a href="https://fufufu.moe/a/?cache" target="_blank">fufufu.moe</a>
        </footer>
    @show

    {{ Minify::javascript(array($javascripts, $additionalJavascripts)) }}

    @if(isset($gaId))
        <script>
          (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
          (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
          m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
          })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

          ga('create', '{{{ $gaId }}}', 'auto');
          ga('send', 'pageview');
        </script>
    @endif
</body>
</html>