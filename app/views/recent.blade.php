@extends('layout')

@section('pageHeading')
    <h1>
        Recent uploads
    </h1>
@stop


@section('main')
    @parent
    
    <div class="container">
        <div id="back-nav">
            <a href="/">Back</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Path</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($pathBuckets as $bucket): ?>
                    <tr>
                        <?php $path = $bucket['paths'][0]; ?>
                        <?php $parent = $bucket['parent']; ?>
                        <td>
                            <?php if(count($bucket['paths']) === 1): ?>
                                <a href="{{{ $path->getUrl() }}}">{{{ $path->getRelativeTop(2) }}}</a>
                            <?php else: ?>
                                <a href="{{{ $parent->getUrl() }}}">{{{ $path->getRelativeTop(2) }}}</a>

                                <?php if(count($bucket['paths']) > 1): ?>
                                    (+ {{{ count($bucket['paths']) - 1 }}} more files)
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>{{{ $path->getDisplayTime() }}}</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
@stop