<?php

class ImageHash extends Eloquent {

    public function pathRecord() {
        return $this->belongsTo('PathRecord');
    }

}