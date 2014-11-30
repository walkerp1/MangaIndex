<?php

class RelatedSeries extends Eloquent {

    public function series() {
        return $this->belongsTo('Series');
    }

}