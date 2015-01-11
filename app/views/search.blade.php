@extends('layout')

@section('pageHeading')
    <h1>
        <a href="/">/</a> Search results<?php if($keyword): ?>:<?php endif; ?> {{{ $keyword }}} ({{{ $count }}})
    </h1>
@stop


@section('main')
    @parent
    
    <div class="container">
        <?php if(count($paths) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Path</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($paths as $path): ?>
                        <tr>
                            <td><a href="{{{ $path->getUrl() }}}">{{{ $path->getRelative() }}}</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No results found</p>
        <?php endif; ?>
    </div>
@stop