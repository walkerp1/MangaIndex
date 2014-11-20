<?php

class Facet extends Eloquent {

    public function series() {
        return $this->belongsToMany('Series');
    }

    public static function getCreateByName($name) {
        $name = trim($name);

        $facet = self::whereName($name)->first();
        if(!$facet) {
            $facet = new self();
            $facet->name = $name;
            $facet->save();
        }

        return $facet;
    }

}