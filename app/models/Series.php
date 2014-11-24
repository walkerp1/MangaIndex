<?php

class Series extends Eloquent {

    public static function boot() {
        parent::boot();

        Series::updating(function($series) {
            // delete cache entries for every path record
            foreach($series->pathRecords as $record) {
                $record->deleteCache();
            }
        });
    }

    public function pathRecords() {
        return $this->hasMany('PathRecord');
    }

    public function facets() {
        return $this->belongsToMany('Facet')->withPivot('type');
    }

    public function users() {
        return $this->belongsToMany('User', 'user_series');
    }

    /*
    
    below are not used. all data should be returned using a single facet() query
    and then processed in the application level

    public function authors() {
        return $this->belongsToMany('Facet')->whereType('author');
    }

    public function artists() {
        return $this->belongsToMany('Facet')->whereType('artist');
    }

    public function categories() {
        return $this->belongsToMany('Facet')->whereType('category');
    }

    public function genres() {
        return $this->belongsToMany('Facet')->whereType('genre');
    }

    public function staff() {
        return $this->belongsToMany('Facet')->whereIn('type', array('author', 'artist'));
    }

    public function titles() {
        return $this->belongsToMany('Facet')->whereType('title');
    }
    */

    public function getFacetNames($type) {
        $facets = $this->facets;
        $ret = array();

        foreach($facets as $facet) {
            if($facet->pivot->type === $type) {
                $ret[] = $facet->name;
            }
        }

        return $ret;
    }

    public static function getCreateFromMuId($muId) {
        $manga = self::whereMuId($muId)->first();
        if(!$manga) {
            $muData = MangaUpdates::getManga($muId);

            if($muData) {
                DB::beginTransaction();

                try {
                    $manga = new self();
                    $manga->save();

                    $manga->mu_id = $muId;
                    $manga->importMuData($muData);
                    $manga->save();
                } catch (Exception $e) {
                    DB::rollback();

                    throw $e;
                }

                DB::commit();
            }
        }

        return $manga;
    }

    public function updateMuData() {
        $muData = MangaUpdates::getManga($this->mu_id);
        if($muData) {
            $this->importMuData($muData);
            $this->updated_at = $this->freshTimestamp();
            $this->save();
        }
    }

    private function importMuData($muData) {
        $this->name = $muData->name;
        $this->description = $muData->description;
        $this->year = $muData->year;
        $this->origin_status = $muData->origin_status;
        $this->scan_status = $muData->scan_status;

        if(isset($muData->image)) {
            $this->image = $muData->image;
        }

        // remove all related facets
        $this->facets()->detach();

        // authors
        foreach($muData->authors as $author) {
            $this->addFacetByName($author, 'author');
        }

        // artists
        foreach($muData->artists as $artist) {
            $this->addFacetByName($artist, 'artist');
        }
        
        // categories
        foreach($muData->categories as $category) {
            $this->addFacetByName($category, 'category');
        }

        // genres
        foreach($muData->genres as $genre) {
            $this->addFacetByName($genre, 'genre');
        }

        // titles
        foreach($muData->altTitles as $title) {
            $this->addFacetByName($title, 'title');
        }

        // add main title
        if(!in_array($muData->name, $muData->altTitles)) {
            $this->addFacetByName($muData->name, 'title');
        }
    }

    public function addFacetByName($facetName, $type) {
        $facet = Facet::getCreateByName($facetName);
        $this->facets()->attach($facet->id, array('type' => $type));
    }

    public function getExternalUrl() {
        return 'https://www.mangaupdates.com/series.html?id='.$this->mu_id;
    }

    public function getGroupedStaff() {
        $staff = array();

        foreach($this->facets as $facet) {
            // if this facet is a staff, and is unqiue
            if(in_array($facet->pivot->type, array('artist', 'author')) && !in_array($facet->name, $staff)) {
                $staff[] = $facet->name;
            }
        }
        
        return $staff;
    }

    // users are allowed to update MU data once every 24 hrs
    public function canUpdateMu() {
        return (strtotime($this->updated_at) < strtotime('-1 day'));
    }

    public function getImageUrl() {
        return '/images/'.$this->image;
    }

    public function hasImage() {
        return !!$this->image;
    }

}
