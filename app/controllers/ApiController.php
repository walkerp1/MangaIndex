<?php

class ApiController extends BaseController {

    public function muid($muId) {
        $records = PathRecord::with('series.facets')->whereHas('series', function($q) use($muId) {
            $q->whereMuId($muId);
        })->get();

        $export = array();
        foreach($records as $record) {
            if(strpos($record->path, '/Manga/') === 0) {
                // Skip paths
                if(strpos($record->path, '/Manga/Non-English') === 0) {
                    continue;
                }
                else {
                    $path = $record->getPath();
                    if($path->exists()) {
                        $export[] = $record->toArray();
                    }
                }
            }
        }

        return Response::json(array('result' => true, 'data' => $export));
    }

    public function register() {
        $username = Input::get('username');
        $password = Input::get('password');

        if(!Auth::check()) {
            Auth::basic('username');
        }

        $user = Auth::user();
        if(!$user || !$user->hasSuper()) {
            return Response::json(array('result' => false, 'message' => 'Access denied'));
        }

        if(!User::usernameIsUnique($username)) {
            return Response::json(array('result' => false, 'message' => 'Username provided is already registered.'));
        }

        if($username && $password) {
            User::register($username, $password);

            return Response::json(array('result' => true));
        }
        else {
            return Response::json(array('result' => false, 'message' => 'Invalid details provided'));
        }
    }

    public function changePassword() {
        $username = Input::get('username');
        $old = Input::get('old');
        $new = Input::get('new');

        $user = User::getByUsernamePassword($username, $old);
        if(!$user) {
            return Response::json(array('result' => false, 'message' => 'Username not found or password incorrect.'));
        }
        elseif($new) {
            $user->setPassword($new);
            $user->save();

            return Response::json(array('result' => true));
        }
        else {
            return Response::json(array('result' => false, 'message' => 'Invalid details provided'));
        }
    }

}
