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

        // load the parent record
        $parent = $path->getParent();
        if($parent) {
            $parentRecord = $parent->loadCreateRecord();
            $ret['parent_id'] = $parentRecord->id;
        }

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

    public function export() {
        // if we've got a series, then load series and facets data
        if($this->series_id > 0) {
            $this->load('series.facets');
        }

        // convert to stdClass for easy use
        $recordData = (object)$this->toArray();

        // process series data
        if(isset($recordData->series)) {
            $recordData->series = (object)$recordData->series;

            // export facets
            if(isset($recordData->series->facets)) {
                $recordData->series->facets = self::processExportedFacets($recordData->series->facets);
                $recordData->series->groupedStaff = $this->series->getGroupedStaff();
            }
        }
        
        return $recordData;
    }

    // group all facets by type
    protected static function processExportedFacets($facets) {
        $ret = new stdClass();

        foreach($facets as $facet) {
            $type = $facet['pivot']['type'];

            if(!isset($ret->$type)) {
                $ret->$type = array();
            }

            $ret->{$type}[] = $facet['name'];
        }

        return $ret;
    }
}