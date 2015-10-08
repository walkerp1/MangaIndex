<?php

use Illuminate\Support\Arr;

class IndexController extends BaseController {

    public function index($requestPath = '') {
        $path = Path::fromRelative('/'.$requestPath);

        if(!$path->exists()) {
            App::abort(404, 'Path not found');
        }

        // if it's a file then download
        if($path->isFile()) {
            return $this->download($path);
        }

        $record = $path->loadCreateRecord();

        $childPaths = $path->getChildren();


        Debugbar::startMeasure('query');

        $result = DB::table('path_records')
                    ->select('path_records.id', 'path_records.path', 'path_records.path_hash', 'path_records.directory',
                        'path_records.size', 'path_records.modified', 'path_records.locked')
                    ->leftJoin('series', 'series.id', '=', 'path_records.series_id')
                    ->leftJoin('facet_series', 'facet_series.series_id', '=', 'series.id')
                    ->leftJoin('facets', 'facets.id', '=', 'facet_series.facet_id')
                    ->where('parent_id', '=', $record->id)
                    //->orderBy('path_records.path', 'asc')
                    ->groupBy('path_records.id')
                    ->get();

        Debugbar::stopMeasure('query');

        Debugbar::startMeasure('format');

        $children = array();

        foreach($childPaths as $child) {
            $children[$child->getHash()] = array(
                'path' => $child,
                'record' => null,
            );
        }

        foreach($result as $row) {
            if(array_key_exists($row->path_hash, $children)) {
                $children[$row->path_hash]['record'] = $row;
                //$children[$row->path_hash]['size'] = DisplaySize::format($row->size);
            }
        }

        $toSort = array();
        $orderParams = $this->doSorting($toSort);

        Debugbar::stopMeasure('format');

        return View::make('index', $orderParams)
            ->with('breadcrumbs', $path->getBreadcrumbs())
            ->with('path', $path)
            ->with('children', $children);
    }

    public function index_old($requestPath = '') {
        $path = Path::fromRelative('/'.$requestPath);

        if(!$path->exists()) {
            App::abort(404, 'Path not found');
        }

        // if it's a file then download
        if($path->isFile()) {
            return $this->download($path);
        }

        $path->loadCreateRecord($path);
        $children = $this->exportChildren($path);

        $orderParams = $this->doSorting($children);

        $groupedStaff = null;
        $genres = null;
        $categories = null;
        $userIsWatching = null;
        $pageTitle = null;
        $pageDescription = null;
        $pageImage = null;
        $relatedSeries = null;
        
        if($series = $path->record->series) {
            $groupedStaff = $series->getGroupedStaff();
            $genres = $series->getFacetNames('genre');
            $categories = $series->getFacetNames('category');
            $pageTitle = $series->name;
            $pageDescription = $series->description;

            if($series->hasImage()) {
                $pageImage = $series->getImageUrl();
            }

            $relatedSeries = $series->getRelated();

            $user = Auth::user();
            if($user) {
                $userIsWatching = $user->isWatchingSeries($series);
            }
        }
        else {
            if(!$path->isRoot()) {
                $pageTitle = $path->getRelativeTop();
            }
        }
        
        $params = array(
            'path' => $path,
            'groupedStaff' => $groupedStaff,
            'genres' => $genres,
            'categories' => $categories,
            'breadcrumbs' => $path->getBreadcrumbs(),
            'children' => $children,
            'userIsWatching' => $userIsWatching,
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageImage' => $pageImage,
            'relatedSeries' => $relatedSeries
        );

        $params = array_merge($params, $orderParams);

        return View::make('index', $params);
    }

    protected function doSorting(&$children) {
        $orderMethod = Input::get('order', 'name');
        $orderDir = Input::get('dir', 'asc');

        if(!Sorting::validOrderMethod($orderMethod)) {
            $orderMethod = 'name';
        }

        if(!Sorting::validOrderDirection($orderDir)) {
            $orderDir = 'asc';
        }
        
        // if the values are default then skip sorting as the paths already in order
        if($orderMethod !== 'name' || $orderDir !== 'asc') {

            //Sorting::sort($children, $orderMethod, $orderDir);
        }

        $invOrderDir = ($orderDir === 'asc') ? 'desc' : 'asc';

        $params = array(
            'orderMethod' => $orderMethod,
            'orderDir' => $orderDir,
            'invOrderDir' => $invOrderDir
        );

        return $params;
    }

    protected function exportChildren(Path $path) {
        $children = array();
        $pathChildren = $path->getChildren();

        foreach($pathChildren as $child) {
            $hash = $child->getHash();

            $children[] = Cache::tags('paths')->rememberForever($hash, function() use($child) {
                return $child->export();
            });
        }

        return $children;
    }

    public function save() {
        $recordId = Input::get('record');
        $muId = Input::get('mu_id');
        $locked = Input::get('locked');
        $delete = Input::get('delete');
        $update = Input::get('update');
        $comment = Input::get('comment');

        // load record
        $record = PathRecord::findOrFail($recordId);

        // remove series link
        if($delete) {
            $record->series_id = null;
        }
        else {
            if($update) { // download new data from MU
                if($record->series) {
                    $record->series->updateMuData();
                }
            }
            elseif($muId) {
                // get series
                $series = Series::getCreateFromMuId($muId);
                if(!$series) {
                    Session::flash('error', 'Failed to find series for MU ID');
                    return Redirect::back();
                }

                $record->series_id = $series->id;
            }

            $record->comment = $comment;

            $user = Auth::user();
            if($user && $user->hasSuper()) {
                $record->locked = !!$locked;
            }
        }

        $record->save();

        Session::flash('success', 'Saved path details successfully');
        return Redirect::back();
    }

    public function report() {
        $recordId = Input::get('record');
        $reason = Input::get('reason');

        if(!$reason) {
            Session::flash('error', 'Please enter a report reason!');
            return Redirect::back();
        }

        $count = Report::where('path_record_id', '=', $recordId)->count();
        if($count > 0) {
            Session::flash('error', 'This path has already been reported');
            return Redirect::back();
        }

        // load record
        $record = PathRecord::findOrFail($recordId);

        if($record->locked) {
            Session::flash('error', 'You cannot report this directory!');
            return Redirect::back();
        }

        $report = new Report();
        $report->path_record_id = $record->id;
        $report->path = $record->path;
        $report->reason = $reason;

        $user = Auth::user();
        if($user) {
            $report->user_id = $user->id;
        }

        $report->save();

        Session::flash('success', 'Report submitted');
        return Redirect::back();
    }
}
