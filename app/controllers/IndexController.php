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

        $path->loadCreateRecord($path);
        $children = $this->exportChildren($path);

        $orderParams = $this->doSorting($children);

        $groupedStaff = null;
        $genres = null;
        $categories = null;
        $userIsWatching = null;
        $pageTitle = null;
        $relatedSeries = null;
        if($series = $path->record->series) {
            $groupedStaff = $series->getGroupedStaff();
            $genres = $series->getFacetNames('genre');
            $categories = $series->getFacetNames('category');
            $pageTitle = $series->name;
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

    protected function download(Path $path) {
        $record = $path->loadCreateRecord($path);
        $record->downloaded_at = $record->freshTimestamp();
        $record->increment('downloads');
        $record->save();

        if(!$path->isSafeExtension()) {
            App::abort(403, 'Illegal file type.');
        }

        if($path->canUseHubicUrl()) {
            $url = Hubic::generateUrlForPath($path);
            return Redirect::to($url);
        }
        else {
            $file = new AsciiSafeDownloadFile($path->getPathname());
            
            $baseName = $path->getBasename();
            $baseName = str_replace('%', '', $baseName);

            return Response::download($file, $baseName);
        }
    }
}
