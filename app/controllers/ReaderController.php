<?php

class ReaderController extends BaseController {

    public function read($relativePath) {
        $path = Path::fromRelative($relativePath);

        if(!$path->exists()) {
            App::abort(404, 'Archive not found');
        }

        $archive = Archive\Factory::open($path);
        $files = $archive->getFiles();

        Debugbar::disable();

        $params = array(
            'additionalStylesheets' => array(
                '/css/reader.css'
            ),
            'additionalJavascripts' => array(
                '/js/reader.js'
            ),
            'path' => $relativePath,
            'files' => json_encode($files)
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

        var_dump($relativePath, $filePath);exit;
    }

}