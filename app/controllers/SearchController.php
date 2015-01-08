<?php

class SearchController extends BaseController {

    public function search($keyword = null) {
        if($keyword) {
            $match = $keyword;
        }
        else {
           $match = Input::get('q'); 
        }

        if($match) {
            $result = Search::searchPaths($match);
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

        return View::make('search', array('keyword' => $match, 'paths' => $paths, 'pageTitle' => $pageTitle));
    }

    // route for e.g /search/genre/drama
    public function searchKeywordType($type = null, $keyword = null) {
        if($type && $keyword) {
            $match = sprintf('"%s:%s"', $type, $keyword);
        }
        else {
            $match = '';
        }

        return $this->search($match);
    }
    
    public function suggest() {
        $term = Input::get('term');
        $result = Search::suggest($term);
        return Response::json($result);
    }
}
