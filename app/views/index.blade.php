@extends('layout')

@section('pageHeading')
    <h1>
        <?php foreach($breadcrumbs as $index => $crumb): ?>
            <a href="{{{ $crumb->getUrl() }}}">{{{ $crumb->getDisplayName() }}}</a>

            <?php if($index > 0): ?>
                <span class="slash">/</span>
            <?php endif; ?>
        <?php endforeach; ?>
        ({{{ count($children) }}})
    </h1>
@stop

@section('main')
    @parent

    <div class="container">
        <?php if(!$path->isRoot()): ?>
            <div id="back-nav">
                <a href="{{{ $path->getUrl().'/..' }}}">Back</a>
            </div>
        <?php endif; ?>

        <div class="index-container">
            <div class="table-outer">
                <table id="index-table" class="mobile-files-table">
                    <thead>
                        <tr>
                            <th>
                                <a class="order order-{{{ $orderDir }}} <?php if($orderMethod === 'name'): ?>active<?php endif; ?>" href="?{{{ http_build_query([ 'order' => 'name', 'dir' => $invOrderDir ]) }}}">Name</a>
                            </th>
                            <th class="size">
                                <a class="order order-{{{ $orderDir }}} <?php if($orderMethod === 'size'): ?>active<?php endif; ?>" href="?{{{ http_build_query([ 'order' => 'size', 'dir' => $invOrderDir ]) }}}">Size</a>
                            </th>
                            <th class="time">
                                <a class="order order-{{{ $orderDir }}} <?php if($orderMethod === 'time'): ?>active<?php endif; ?>" href="?{{{ http_build_query([ 'order' => 'time', 'dir' => $invOrderDir ]) }}}">Time</a>
                            </th>
                            <th class="tags"></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($children as $child): ?>
                            <tr data-record="{{{ $child->record->id }}}">
                                <td>
                                    <?php if($child->isDir): ?>
                                        <a href="{{{ $child->url }}}">{{{ $child->name }}}/</a>
                                    <?php else: ?>
                                        <a href="{{{ $child->url }}}">{{{ $child->name }}}</a>
                                    <?php endif; ?>

                                    <?php if(isset($child->record->series->groupedStaff) && count($child->record->series->groupedStaff) > 0): ?>
                                        <span class="inline-staff">
                                            <?php foreach($child->record->series->groupedStaff as $index => $staff): ?>
                                                <?php if($index > 0): ?>
                                                    /
                                                <?php endif; ?>

                                                <a href="{{{ Search::url($staff) }}}">{{{ $staff }}}</a>
                                            <?php endforeach; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    {{{ $child->size }}}
                                </td>
                                <td>
                                    {{{ DisplayTime::format($child->rawTime) }}}
                                </td>
                                <td>
                                    <?php if(isset($child->record->series->facets->genre)): ?>
                                        <?php foreach($child->record->series->facets->genre as $genre): ?>
                                            <a href="{{{ Search::url($genre, 'genre') }}}" class="tag">{{{ $genre }}}</a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(isset($child->record->locked) && !$child->record->locked): ?>
                                        <a class="report-link" href="#">Report</a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($child->canUseReader): ?>
                                        <a href="{{{ $child->readerUrl }}}" target="_blank">Read</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if(isset($path->record->series)): ?>
                <div class="manga-info-outer">
                    <div class="manga-info">
                        <h2>
                            <span class="title">{{{ $path->record->series->name }}}</span>
                            <span class="year">[{{{ $path->record->series->year }}}]</span><!--
                            -->&nbsp;<a href="{{{ $path->record->series->getExternalUrl() }}}" target="_blank" class="mu-link icon-link"></a>
                        </h2>
                        <p class="staff" title="Staff">
                            <?php foreach($groupedStaff as $index => $staff): ?>
                                <a href="{{{ Search::url($staff) }}}">{{{ $staff }}}</a>
                                <?php if(($index + 1) < count($groupedStaff)): ?>
                                    /
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </p>
                        <?php if($path->record->series->hasImage()): ?>
                            <img src="{{ $path->record->series->getImageUrl() }}" alt width="248" />
                        <?php endif; ?>

                        <?php if(count($genres) > 0): ?>
                            <h3>Genres</h3>
                            <div class="genres">
                                <?php foreach($genres as $genre): ?>
                                    <a class="tag" href="{{{ Search::url($genre, 'genre') }}}">{{{ $genre }}}</a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if(count($categories) > 0): ?>
                            <h3>Tags</h3>
                            <div class="genres">
                                <?php foreach($categories as $category): ?>
                                    <a class="tag tag-category" href="{{{ Search::url($category, 'category') }}}">{{{ $category }}}</a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if(count($relatedSeries) > 0): ?>
                            <h3>Related series</h3>
                            <ul>
                                <?php foreach($relatedSeries as $related): ?>
                                    <li><a href="{{{ $related->path->getUrl() }}}">{{{ $related->name }}}</a> ({{{ $related->type }}})
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <h4>Scanlated?</h4>
                        <span class="scanstatus">{{{ $path->record->series->scan_status }}}</span>

                        <?php if($user): ?>
                            <div>
                                <?php if($userIsWatching): ?>
                                    <a class="button active" id="watch-series" data-series="{{{ $path->record->series->id }}}">Watching</a>
                                <?php else: ?>
                                    <a class="button" id="watch-series" data-series="{{{ $path->record->series->id }}}">Watch series</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if(!$path->record->locked || ($user && $user->hasSuper())): ?>
        <div class="container" id="path-edit">
            <h2>Directory details</h2>

            <form action="/path/save" method="post">
                {{ Form::token() }}
                <input type="hidden" name="record" value="{{{ $path->record->id }}}">
                <input type="hidden" name="incomplete" value="0">
                <input type="hidden" name="locked" value="0">

                <div id="path-edit-info">
                    <?php if($path->record->series): ?>
                        <div class="field-row">
                            <button class="button" name="delete" value="1" id="delete-manga">Delete Manga data</button>
                        </div>

                        <?php if(($user && $user->hasSuper()) || $path->record->series->canUpdateMu()): ?>
                            <div class="field-row">
                                <button class="button" name="update" value="1">Update Manga data</button>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="field-row">
                            <label>MangaUpdates ID</label>
                            <input type="text" name="mu_id" class="input">
                        </div>
                    <?php endif; ?>

                    <?php if($user && $user->hasSuper()): ?>
                        <div class="field-row">
                            <label>
                                Lock directory
                                <input type="checkbox" name="locked" value="1" class="checkbox" <?php if($path->record->locked): ?>checked<?php endif; ?>>
                            </label>
                        </div>
                    <?php endif; ?>

                    <div class="field-row">
                        <button class="button">Save</button>
                    </div>
                </div>
                <div id="path-edit-comment">
                    <textarea name="comment" class="input" placeholder="Comments">{{{ $path->record->comment }}}</textarea>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <form id="report-form" method="post" action="{{{ URL::route('report') }}}">
        {{ Form::token() }}
    </form>

    <div class="template">
        <table>
            <tr class="report-row">
                <td colspan="5">
                    <div class="expand">
                        <h3>Report file</h3>
                        <textarea name="reason" placeholder="Report reason" class="input" required form="report-form" disabled></textarea>
                        <button class="button" form="report-form" disabled>Submit</button>
                        <a class="button button-report-cancel" href="#">Cancel</a>

                        <input type="hidden" name="record" form="report-form" value disabled>
                    </div>
                </td>
            </tr>
        </table>
    </div>
@stop