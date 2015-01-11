<?php

class UsersController extends BaseController {

    public function notifications() {
        $user = Auth::user();

        $notifications = $user
            ->notifications()
            ->join('path_records', 'path_records.id', '=', 'notifications.path_record_id')
            ->select('notifications.*')
            ->with('pathRecord')
            ->orderBy('notifications.dismissed', 'asc')
            ->orderBy('path_records.modified', 'desc')
            ->paginate(20);

        $watched = $user->series()->with('pathRecords')->get();

        $params = array(
            'notifications' => $notifications,
            'watched' => $watched,
            'pageTitle' => 'Notifications'
        );

        return View::make('notifications', $params);
    }

    public function dismiss() {
        $user = Auth::user();

        if(Input::has('all')) { // dismiss all
            $user->notifications()->update(array('dismissed' => true));
            Session::flash('success', 'All notifications dismissed');
        }
        else { // dismiss single
            $notifyId = Input::get('notification');
            $notify = Notification::findOrFail($notifyId);

            if($notify->user_id !== $user->id) {
                App::abort(403, 'That notification doesn\'t belong to you');
            }

            $notify->dismiss();
        }

        return Redirect::route('notifications');
    }

    public function toggleWatch() {
        $seriesId = Input::get('series');
        $series = Series::findOrFail($seriesId);
        $user = Auth::user();

        if(!$series) {
            App::abort(400, 'Invalid params');
        }
        else {
            $watching = $user->watchSeries($series);

            if(Request::ajax()) {  
                return Response::json(array('result' => true, 'watching' => $watching));
            }
            else {
                return Redirect::back();
            }
        }
    }

    public function downloadDismiss(Notification $notification) {
        $user = Auth::user();
        if($notification->user_id !== $user->id) {
            App::abort(403, 'That notification doesn\'t belong to you');
        }

        $notification->dismiss();

        $path = $notification->pathRecord->getPath();
        return $this->download($path);
    }

}
