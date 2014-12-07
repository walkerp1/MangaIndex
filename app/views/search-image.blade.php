@extends('layout')

@section('pageHeading')
    <h1>
        Search image
    </h1>
@stop


@section('main')
    @parent
    
    <div class="container">
        <div id="back-nav">
            <a href="/">Back</a>
        </div>

        <p>
            {{{ $imagesCount }}} images in database.
        </p>

        <form method="post" action="{{{ URL::Route('searchImageSubmit') }}}" enctype="multipart/form-data" id="search-image-form">
            {{ Form::token() }}

            <input type="text" name="url" class="input" placeholder="Image URL"><br/>
            <br/>

            <input type="file" name="file" class="file"><br/>
            <br/>

            <button class="button">Submit</button>
        </form>

        <?php if(isset($searched) && $searched): ?>
            <div id="image-search-results">
                <h2>Search results</h2>

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
                                    <td>
                                        <a href="{{{ $path->getUrl() }}}">{{{ $path->getRelative() }}}</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>
                        No results found.
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
@stop