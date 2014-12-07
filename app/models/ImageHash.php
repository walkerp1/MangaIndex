<?php

class ImageHash extends Eloquent {

    public function pathRecord() {
        return $this->belongsTo('PathRecord');
    }

    public static function formattedCount() {
        return number_format(self::count());
    }

}