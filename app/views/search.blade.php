@extends('layout')

@section('pageHeading')
    <h1>
        Search results: {{{ $keyword }}}
    </h1>
@stop


@section('main')
    @parent
    
    <div class="container">
        <div id="back-nav">
            <a href="/">Back</a>
        </div>

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