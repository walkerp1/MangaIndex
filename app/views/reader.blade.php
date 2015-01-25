@extends('layout')

@section('body')
    <div id="reader" data-path="{{{ $path }}}" data-files="{{{ $files }}}">
        <div id="reader-page">
            <img src="/images/citrus/citrus_ch01_01.jpg" alt />
        </div>
    </div>
@show