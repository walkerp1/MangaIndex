@extends('layout')

@section('pageHeading')
    <h1>
        <a href="/">/</a> Series upload notifications ({{{ $notifyCount }}})
    </h1>
@stop


@section('main')
    @parent
    
    <div class="container">
        <?php if(count($watched) > 0 || count($notifications) > 0): ?>
            <h2>New uploads</h2>
            <?php if(count($notifications) === 0): ?>
                <p>
                    You have no new upload notifications!
                </p>
            <?php else: ?>
                <form method="post" action="{{{ URL::route('notificationDismiss') }}}">
                    {{ Form::token() }}
                    <table>
                        <thead>
                            <tr>
                                <th>Path</th>
                                <th>Uploaded</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($notifications as $notify): ?>
                                <?php $path = $notify->pathRecord->getPath(); ?>
                                <tr <?php if($notify->dismissed): ?>class="dismissed"<?php endif; ?>>
                                    <td><a href="{{{ $notify->getUrl() }}}">{{{ $path->getRelative() }}}</a></td>
                                    <td>{{{ $path->getDisplayTime() }}}</td>
                                    <td>
                                        <?php if(!$notify->dismissed): ?>
                                            <button name="notification" value="{{{ $notify->id }}}" class="button-text">Dismiss</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <?php if($notifyCount > 0): ?>
                            <tfoot>
                                <tr>
                                    <td colspan="2"></td>
                                    <td>
                                        <button id="dismiss-notify-all" name="all" value="true" class="button-text">Dismiss all</button>
                                    </td>
                                </tr>
                            </tfoot>
                        <?php endif; ?>
                    </table>
                </form>

                {{ $notifications->links() }}
            <?php endif; ?>
        <?php endif; ?>

        <h2>Watched series</h2>
        <?php if(count($watched) === 0): ?>
            <p>
                You have no series watched!<br/>
                <br/>
                Visit a directory that has a series assigned and click the "Watch series" button.
            </p>
        <?php else: ?>
            <form method="post" action="/user/notifications/watch">
                {{ Form::token() }}
                
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($watched as $series): ?>
                            <tr <?php if(count($series->pathRecords) !== 1): ?>class="has-paths"<?php endif; ?>>
                                <td>
                                    <?php if(count($series->pathRecords) === 1): ?>
                                        <?php $path = $series->pathRecords->first()->getPath(); ?>
                                        <a href="{{{ $path->getUrl() }}}">{{{ $series->name }}}</a>
                                    <?php else: ?>
                                        <a href="#" class="series-paths-trigger">{{{ $series->name }}}</a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button id="unwatch" name="series" value="{{{ $series->id }}}" class="button-text">Unwatch</button>
                                </td>
                            </tr>
                            <?php if(count($series->pathRecords) !== 1): ?>
                                <tr class="series-paths-expand">
                                    <td colspan="2">
                                        <div class="paths">
                                            <div class="inner">
                                                <?php foreach($series->pathRecords as $record): ?>
                                                    <?php $path = $record->getPath(); ?>
                                                    <a href="{{{ $path->getUrl() }}}">{{{ $path->getRelativeTop(2) }}}</a><br/>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        <?php endif; ?>
    </div>
@stop