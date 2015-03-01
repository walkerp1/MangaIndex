@extends('layout')

@section('pageHeading')
    <h1>
        <a href="/">/</a> Reports ({{{ $reportsCount }}})
    </h1>
@stop


@section('main')
    @parent

    <div class="container">
        <?php if(count($reports) > 0): ?>
        <form method="post" action="{{{ URL::route('reportDismiss') }}}">
            {{ Form::token() }}
            
            <table id="reports-table">
                <thead>
                    <tr>
                        <th>Path</th>
                        <th>Reason</th>
                        <th>Time</th>
                        <th>Reported by</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($reports as $report): ?>
                        <?php $path = $report->getPath(); ?>
                        <tr>
                            <td>
                                <?php if(!$path->exists()): ?>
                                    {{{ $report->path }}}
                                    <span class="tag tag-red tag-cell">Deleted</span>
                                <?php elseif($path->isDir()): ?>
                                    <a href="{{{ $path->getUrl() }}}">{{{ $path->getRelative() }}}/</a>
                                <?php else: ?>
                                    <?php $parent = $path->getParent(); ?>
                                    <a href="{{{ $parent->getUrl() }}}#{{{ $path->getFilename() }}}">{{{ $path->getRelative() }}}</a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="report-reason">{{{ $report->reason }}}</span>
                            </td>
                            <td>
                                {{{ $report->getDisplayTime() }}}
                            </td>
                            <td>
                                <?php if($report->user): ?>
                                    {{{ $report->user->username }}}
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($user && $user->hasSuper()): ?>
                                    <button name="report" value="{{{ $report->id }}}" class="button-text">Dismiss</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p>
                    No reports to show!
                </p>
            <?php endif; ?>
        </form>

        {{ $reports->links() }}
    </div>
@stop