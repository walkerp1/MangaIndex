@extends('layout')

@section('pageHeading')
    <h1>{{{ $title }}}</h1>
@stop

@section('main')
    @parent

    <div class="container">
        <div id="back-nav">
            <a href="/">Back</a>
        </div>

        <p>
            {{{ $message }}}
        </p>
    </div>
@stop