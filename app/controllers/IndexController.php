<?php

class IndexController extends BaseController {

    public function index($requestPath) {
        $mangaPath = Config::get('app.manga_path');
        $fullPath = realpath($mangaPath.'/'.$requestPath);
        $path = new Path($fullPath);

        if(!$path->exists()) {
            App::abort(404);
        }

        // if it's a file then download
        if($path->isFile()) {
            return $this->download($path);
        }

        // don't do this, it'll blow the memory on large dirs
        //$ignoreCache = Input::get('ignorecache', false);
        $ignoreCache = false;

        $path->loadCreateRecord($path);
        $children = $this->exportChildren($path, $ignoreCache);

        $orderParams = $this->doSorting($children);

        $groupedStaff = null;
        $genres = null;
        $categories = null;
        $userIsWatching = null;
        $pageTitle = null;
        if($series = $path->record->series) {
            $groupedStaff = $series->getGroupedStaff();
            $genres = $series->getFacetNames('genre');
            $categories = $series->getFacetNames('category');
            $pageTitle = $series->name;

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

            Sorting::sort($children, $orderMethod, $orderDir);
        }

        $invOrderDir = ($orderDir === 'asc') ? 'desc' : 'asc';

        $params = array(
            'orderMethod' => $orderMethod,
            'orderDir' => $orderDir,
            'invOrderDir' => $invOrderDir
        );

        return $params;
    }

    protected function exportChildren(Path $path, $ignoreCache) {
        $children = array();
        $pathChildren = $path->getChildren();

        foreach($pathChildren as $child) {
            $hash = $child->getHash();

            if($ignoreCache) {
                $children[] = $this->exportPath($child);
            }
            else {
                $children[] = Cache::tags('paths')->rememberForever($hash, function() use($child) {
                    return $this->exportPath($child);
                });
            }
        }

        return $children;
    }

    protected function exportPath($path) {
        $data = new stdClass();

        // FS stat-based info
        $data->name = $path->getDisplayName();
        $data->size = $path->getDisplaySize();
        $data->rawSize = $path->getSize();
        $data->rawTime = $path->getMTime();
        $data->url = $path->getUrl();
        $data->isDir = $path->isDir();

        $record = $path->loadCreateRecord(); // TODO: load all child records using a single IN query
        if($record) {

            // if we've got a series, then load series and facets data
            if($record->series_id > 0) {
                $record->load('series.facets');
            }

            // convert to stdClass for easy use
            $recordData = (object)$record->toArray();

            // process series data
            if(isset($recordData->series)) {
                $recordData->series = (object)$recordData->series;

                if(isset($recordData->series->facets)) {
                    $recordData->series->facets = $this->processFacets($recordData->series->facets);
                    $recordData->series->groupedStaff = $record->series->getGroupedStaff();
                }
            }

            $data->record = $recordData;

            unset($record);
        }

        return $data;
    }

    // group all facets by type
    protected function processFacets($facets) {
        $ret = new stdClass();

        foreach($facets as $facet) {
            $type = $facet['pivot']['type'];

            if(!isset($ret->$type)) {
                $ret->$type = array();
            }

            $ret->{$type}[] = $facet['name'];
        }

        return $ret;
    }

    public function save() {
        $pathId = Input::get('path_id');
        $muId = Input::get('mu_id');
        $incomplete = Input::get('incomplete');
        $locked = Input::get('locked');
        $delete = Input::get('delete');
        $update = Input::get('update');
        $comment = Input::get('comment');

        // load record
        $record = PathRecord::findOrFail($pathId);

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

            $record->incomplete = !!$incomplete;
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
}
