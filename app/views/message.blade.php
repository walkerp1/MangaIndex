@extends('layout')

@section('pageHeading')
    <h1>{{{ $title }}}</h1>
@stop

@section('main')
    @parent

    <div class="container">
        <p>
            {{{ $message }}}
        </p>
    </div>
@stop