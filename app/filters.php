<?php

App::before(function($request)
{
    if(Input::has('minify')) {
        if(Input::get('minify')) {
            Minify::enable();
        }
        else {
            Minify::disable();
        }
    }
    
    if(Config::get('app.require_auth') === true) {
        // check we're not already logged in
        if(!Auth::check()) {
            // do auth
            Auth::basic('username', $request);

            //check again
            if(Auth::check()) {
                // auth successful
                $user = Auth::user();
                $user->touchLoggedInDate(); // update logged_in_at to current datetime
            }
            else {
                // auth failed
                $headers = array(
                    'WWW-Authenticate' => 'Basic',
                    'Content-Type' => 'text/plain'
                );

                return Response::make('', 401, $headers);
            }
        }
    }
});


App::after(function($request, $response)
{
    //
});

// verify _token request param to match token in session
Route::filter('csrf', function()
{
	if (Session::token() != Input::get('_token')) {
		throw new Illuminate\Session\TokenMismatchException;
	}
});

// require logged in user
Route::filter('auth', function() {
    if(!Auth::check()) {
        App::abort(403, 'You are not logged in');
    }
});

// require user to have superuser perms
Route::filter('auth.super', function() {
    $user = Auth::user();
    if(!$user) {
        App::abort(403, 'You are not logged in');
    }
    elseif(!$user->hasSuper()) {
        App::abort(403, 'You don\'t have permission to do that');
    }
});