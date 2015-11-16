<?php

/*
|--------------------------------------------------------------------------
| Register The Artisan Commands
|--------------------------------------------------------------------------
|
| Each available Artisan command must be registered with the console so
| that it is available to be called. We'll register every command so
| the console gets access to each of the command object instances.
|
*/

Artisan::add(new IndexCommand());
Artisan::add(new ReverseIndexCommand());
Artisan::add(new UpdateSeriesCommand());
Artisan::add(new UpdateSizeCommand());
Artisan::add(new GenerateSitemapCommand());
Artisan::add(new MergeAutoUploadsCommand());

if(true || Config::get('app.debug')) {
    Artisan::add(new DummyPathsCommand());
}

// only add if we have inotify, otherwise artisan will die on php notice for undefined constants
if(extension_loaded('inotify')) {
    Artisan::add(new WatcherCommand());
}
