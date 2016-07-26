<?php

class SearchController extends BaseController {

    public function search($keyword = null) {
        Auth::basic('username');
        if(!Auth::check()) {
            // do auth
            Auth::basic('username');
            if(!Auth::check()) {
                return Response::make(View::make('unauth',array()),401)->header('WWW-Authenticate', 'Basic');
            }
        }
        if($keyword) {
            $match = $keyword;
        }
        else {
           $match = Input::get('q'); 
        }

        $count = 0;
        if($match) {
            $result = Search::searchPaths($match, $count);
        }
        else {
            $result = array();
        }

        $paths = array();
        foreach($result as $row) {
            $path = $row->getPath();

            if($path->exists()) {
                $path->record = $row;
                $paths[] = $path;
            }
        }

        // page title
        $pageTitle = 'Search: '.$match;

        return View::make('search', array('keyword' => $match, 'paths' => $paths, 'pageTitle' => $pageTitle, 'count' => $count));
    }

    // route for e.g /search/genre/drama
    public function searchKeywordType($type = null, $keyword = null) {
        Auth::basic('username');
        if(!Auth::check()) {
            // do auth
            Auth::basic('username');
            if(!Auth::check()) {
                return Response::make(View::make('unauth',array()),401)->header('WWW-Authenticate', 'Basic');
            }
        }
        if($type && $keyword) {
            $match = sprintf('"%s:%s"', $type, $keyword);
        }
        else {
            $match = '';
        }

        return $this->search($match);
    }
    
    public function suggest() {
        Auth::basic('username');
        if(!Auth::check()) {
            // do auth
            Auth::basic('username');
            if(!Auth::check()) {
                return Response::make(View::make('unauth',array()),401)->header('WWW-Authenticate', 'Basic');
            }
        }
        $term = Input::get('term');
        $result = Search::suggest($term);
        return Response::json($result);
    }
}
