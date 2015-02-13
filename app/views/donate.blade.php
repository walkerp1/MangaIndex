@extends('layout')

@section('pageHeading')
    <h1>
        <a href="/">/</a> Donate
    </h1>
@stop


@section('main')
    <div class="container">
        <p>
            If you like this site and would like to help support it, please consider donating via one of the things below. Hosting, backup storage, SSL and domain costs average ~£40 total a month currently.
        </p>

        <div id="donate-array">
            <a href="https://flattr.com/thing/edbe9bf2e2f95a9814a48307bf493f33" target="_blank" class="button">Flattr</a>

            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                <input type="hidden" name="cmd" value="_s-xclick">
                <input type="hidden" name="hosted_button_id" value="KUZGX59GLPFJ2">
                <button class="button" name="submit" value="">PayPal</button>
            </form>
        </div>
    </div>
@stop