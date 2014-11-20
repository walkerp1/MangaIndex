<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;

class User extends Eloquent implements UserInterface {

	use UserTrait;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password_hash', 'remember_token');

    public function series() {
        return $this->belongsToMany('Series', 'user_series');
    }

    public function notifications() {
        return $this->hasMany('Notification');
    }

    public function getAuthPassword() {
        return $this->password_hash;
    }

    public function hasSuper() {
        return !!$this->is_super;
    }

    public static function usernameIsUnique($username) {
        $count = self::whereUsername($username)->count();
        return ($count === 0);
    }

    public static function register($username, $password) {
        $user = new User();
        $user->username = $username;
        $user->setPassword($password);
        $user->save();

        return $user;
    }

    public static function getByUsernamePassword($username, $password) {
        $user = self::whereUsername($username)->first();
        if($user) {
            if(password_verify($password, $user->getAuthPassword())) {
                return $user;
            }
        }

        return null;
    }

    public function setPassword($password) {
        $this->password_hash = password_hash($password, PASSWORD_DEFAULT);
    }

    public function watchSeries(Series $series) {
        $watching = $this->isWatchingSeries($series);

        if($watching) {
            $this->series()->detach($series->id);
        }
        else {
            $this->series()->attach($series->id);
        }

        return !$watching;
    }

    public function isWatchingSeries(Series $series) {
        $result = $this
            ->series()
            ->whereSeriesId($series->id)
            ->count();

        return ($result > 0);
    }
}
