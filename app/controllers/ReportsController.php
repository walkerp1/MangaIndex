<?php

class ReportsController extends BaseController {

    public function reports() {

        Report::clearCache();

        $reports = Report::select()
            ->with('pathRecord', 'user')
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        // check all report paths still exist
        foreach($reports as $key => $report) {
            $path = $report->pathRecord->getPath();
            if(!$path->exists()) {
                $report->delete();
                unset($reports[$key]);
            }
        }

        return View::make('reports', array('reports' => $reports, 'pageTitle' => 'Reports'));
    }

    public function dismiss() {
        $reportId = Input::get('report');

        $report = Report::findOrFail($reportId);
        $report->delete();

        return Redirect::route('reports')->with('success', 'Report dismissed');
    }

}