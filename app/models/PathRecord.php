<?php

class PathRecord extends Eloquent {

    public static function boot() {
        parent::boot();

        // observer for on-created notifications
        PathRecord::observe(new PathRecordNotifications());

        PathRecord::updating(function($record) {
            $record->deleteCache();

            if($record->hashed_at && $record->shouldHash()) {
                $record->imageHashes()->delete();
            }
        });
    }

    public function deleteCache() {
        Cache::tags('paths')->forget($this->path_hash);
    }

    public function series() {
        return $this->belongsTo('Series');
    }

    public function imageHashes() {
        return $this->hasMany('ImageHash');
    }

    public static function getCreateForPath(Path $path) {
        $hash = $path->getHash();

        $record = self::wherePathHash($hash)->first();
        if(!$record) {
            $record = new self();

            $fields = self::getPathFields($path);
            foreach($fields as $field => $value) {
                $record->$field = $value;
            }

            $record->save();
        }

        return $record;
    }

    protected static function getPathFields(Path $path) {
        $ret = array();
        $ret['path'] = $path->getRelative();
        $ret['path_hash'] = $path->getHash();
        $ret['directory'] = $path->isDir();
        $ret['size'] = $path->getSize();
        $ret['created'] = date('Y-m-d H:i:s', $path->getCTime());
        $ret['modified'] = date('Y-m-d H:i:s', $path->getMTime());
        return $ret;
    }

    public static function getForPath(Path $path) {
        $hash = $path->getHash();
        $record = self::with('series.facets')->wherePathHash($hash)->first();
        return $record;
    }

    public function getPath() {
        return Path::fromRelative($this->path);
    }

    public function checkUpdate(Path $path = null) {
        if($path === null) {
            $path = $this->getPath();
        }

        $dirty = false;

        $fields = self::getPathFields($path);
        foreach($fields as $field => $value) {
            if($this->$field != $value) {
                $dirty = true;
                break;
            }
        }

        if($dirty) {
            foreach($fields as $field => $value) {
                $this->$field = $value;
            }

            $this->save();
        }
    }

    public function shouldHash() {
        return (!$this->hashed_at || strtotime($this->modified) > strtotime($this->hashed_at));
    }
}