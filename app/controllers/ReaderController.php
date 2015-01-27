<?php

class ReaderController extends BaseController {

    public function read($relativePath) {
        $path = Path::fromRelative($relativePath);

        if(!$path->exists()) {
            App::abort(404, 'Archive not found');
        }

        // TODO: cache file entries
        $archive = Archive\Factory::open($path);
        $files = $archive->getFiles();
        $files = Archive\Utils::filterImageFiles($files);

        if(count($files) === 0) {
            App::abort(500, 'No valid image files found in archive');
        }

        $index = (int)Input::get('index', 0);

        $params = array(
            'additionalStylesheets' => array(
                '/css/reader.css'
            ),
            'additionalJavascripts' => array(
                '/js/reader.js'
            ),
            'path' => $relativePath,
            'files' => json_encode($files),
            'index' => $index
        );

        return View::make('reader', $params);
    }

    public function image() {
        $relativePath = Input::get('path');
        $filePath = Input::get('file');

        $path = Path::fromRelative($relativePath);

        if(!$path->exists()) {
            App::abort(404, 'Archive not found');
        }

        $archive = Archive\Factory::open($path);
        $imageStream = $archive->getEntryStream($filePath);
        $imageData = stream_get_contents($imageStream);

        $response = Response::make($imageData);

        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        switch($ext) {
            case 'jpg':
            case 'jpeg':
                $response->header('Content-Type', 'image/jpeg');
                break;
            case 'png':
                $response->header('Content-Type', 'image/png');
                break;
        }
        
        return $response;
    }

}