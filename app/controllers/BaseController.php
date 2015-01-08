<?php

class BaseController extends Controller {

    public function __construct() {
        $user = Auth::user();
        View::share('user', $user);

        // notifications
        if($user) {
            $notifyCount = $user->notifications()->unseen()->count();
            View::share('notifyCount', $notifyCount);
        }

        // google analytics id
        $gaId = Config::get('app.ga_id');
        View::share('gaId', $gaId);

        $this->setupAssets();
        $this->setupStats();
    }

    // css and js files to include
    protected function setupAssets() {
        $stylesheets = array(
            '/css/normalize.css',
            '/css/jquery-ui.structure.css',
            '/css/fonts.css',
            '/css/manga.css'
        );

        View::share('stylesheets', $stylesheets);

        $javascripts = array(
            '/js/jquery.js',
            '/js/jquery-ui.js',
            '/js/manga.js'
        );

        View::share('javascripts', $javascripts);
    }

    // total size used in footer
    protected function setupStats() {
        $size = Cache::remember('statTotalSize', 60, function() {
            return DB::table('path_records')->sum('size');
        });

        $formatted = DisplaySize::format($size, 2);
        View::share('statTotalSize', $formatted);
    }

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}
	}

    protected function download(Path $path) {
        $record = $path->loadCreateRecord($path);
        $record->downloaded_at = $record->freshTimestamp();
        $record->increment('downloads');
        $record->save();

        if(!$path->isSafeExtension()) {
            App::abort(403, 'Illegal file type.');
        }

        if($path->canUseHubicUrl()) {
            $url = Hubic::generateUrlForPath($path);
            return Redirect::to($url);
        }
        else {
            $file = new AsciiSafeDownloadFile($path->getPathname());
            
            $baseName = $path->getBasename();
            $baseName = str_replace('%', '', $baseName);

            return Response::download($file, $baseName);
        }
    }

}
