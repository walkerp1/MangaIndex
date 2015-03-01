@extends('layout')

@section('pageHeading')
    <h1>
        <a href="/">/</a> Recent uploads
    </h1>
@stop


@section('main')
    @parent

    <div class="container">
        <table class="mobile-files-table">
            <thead>
                <tr>
                    <th>Path</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pathBuckets as $bucket)
                    <tr>
                        <?php $path = $bucket['paths'][0]; ?>
                        <?php $parent = $bucket['parent']; ?>
                        <td>
                            @if(count($bucket['paths']) === 1)
                                <a href="{{{ $path->getUrl() }}}">{{{ $path->getRelativeTop(2) }}}</a>
                            @else
                                <a href="{{{ $parent->getUrl() }}}">{{{ $path->getRelativeTop(2) }}}</a>

                                @if(count($bucket['paths']) > 1)
                                    (+ {{{ count($bucket['paths']) - 1 }}} more files)
                                @endif
                            @endif
                        </td>
                        <td>{{{ $path->getDisplayTime() }}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@stop