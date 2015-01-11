<?php

class AdminController extends BaseController {

    public function flushCache() {
        Cache::tags('paths')->flush();
        
        return View::make('message', array('title' => 'Flush cache', 'message' => 'Cache flushed'));
    }

}