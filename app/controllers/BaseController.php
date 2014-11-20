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

        if($path->isSafeExtension()) {
            $file = new AsciiSafeDownloadFile($path->getPathname());
            return Response::download($file, $path->getBasename());
        }
        else {
            App::abort(403, 'Illegal file type.');
        }
    }

}
