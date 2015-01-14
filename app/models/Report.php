<?php

class Report extends Eloquent {

    public static function boot() {
        parent::boot();

        Report::saved(function($report) {
            Report::clearCache();
        });

        Report::deleted(function($report) {
            Report::clearCache();
        });
    }

    public static function clearCache() {
        Cache::forget('reportsCount');
    }

    public function pathRecord() {
        return $this->belongsTo('PathRecord');
    }

    public function user() {
        return $this->belongsTo('User');
    }

    public function getDisplayTime($short = false) {
        $time = strtotime($this->created_at);
        return DisplayTime::format($time, $short);
    }

    public function getPath() {
        return Path::fromRelative($this->path);
    }

}