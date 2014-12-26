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

    public function searchKeywordType($type = null, $keyword = null) {
        if($type && $keyword) {
            $match = sprintf('"%s:%s"', $type, $keyword);
        }
        else {
            $match = '';
        }

        return $this->search($match);
    }

    public function image() {
        App::abort(403);

        $imagesCount = ImageHash::formattedCount();
        return View::make('search-image', array('pageTitle' => 'Search image', 'imagesCount' => $imagesCount));
    }

    public function imageSubmit() {
        App::abort(403);
        
        $url = Input::get('url');

        if($url) {
            try {
                $inputFile = tmpfile();
                $meta = stream_get_meta_data($inputFile);
                $inputFilePath = $meta['uri'];

                $httpStream = fopen($url, 'r');
                stream_copy_to_stream($httpStream, $inputFile);
                fclose($httpStream);
            }
            catch (Exception $e) {
                Session::flash('error', 'Error getting image from URL');
                return Redirect::back();
            }
        }
        elseif(Input::hasFile('file')) {
            $file = Input::file('file');
            if($file->isValid()) {
                $inputFilePath = $file->getRealPath();
            }
            else {
                Session::flash('error', 'Error uploading file');
                return Redirect::back();
            }
        }
        else {
            Session::flash('error', 'No image submitted');
            return Redirect::back();
        }

        $paths = Search::byImage($inputFilePath);
        if($paths === false) {
            Session::flash('error', 'Invalid image file');
            return Redirect::back();
        }

        $imagesCount = ImageHash::formattedCount();

        $viewParams = array(
            'pageTitle' => 'Search image',
            'paths' => $paths,
            'searched' => true,
            'imagesCount' => $imagesCount
        );

        return View::make('search-image', $viewParams);
    }

    public function suggest() {
        $term = Input::get('term');

        $result = array();

        $result = Search::suggest($term);

        return Response::json($result);
    }
}
