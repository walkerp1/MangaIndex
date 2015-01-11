@extends('layout')

@section('pageHeading')
    <h1>
        Reports
    </h1>
@stop


@section('main')
    @parent
    
    <div class="container">
        <p>
            FTP access is open to everyone, please feel free to action any of the reported items below.<br/>
            Each report will be checked and dismissed manually by site mods. If you're interested in helping out with moderation, please enquire on IRC.<br/>
            (mods can optionally recieve a @madokami.com email address)
        </p>
    </div>

    <div class="container">
        <div id="back-nav">
            <a href="/">Back</a>
        </div>

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
                        <?php $path = $report->pathRecord->getPath(); ?>
                        <tr>
                            <td>
                                <?php if($path->isDir()): ?>
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
                                <?php if($user->hasSuper()): ?>
                                    <button name="report" value="{{{ $report->id }}}" class="button-text">Dismiss</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>

        {{ $reports->links() }}
    </div>
@stop