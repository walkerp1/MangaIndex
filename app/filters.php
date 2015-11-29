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
