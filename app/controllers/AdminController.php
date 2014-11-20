<?php

class AdminController extends BaseController {

    public function flushCache() {
        $user = Auth::user();

        if($user && $user->hasSuper()) {
            Cache::tags('paths')->flush();
        }

        return View::make('message', array('title' => 'Flush cache', 'message' => 'Cache flushed'));
    }

}