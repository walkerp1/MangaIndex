<?php

class Indexer {

    public static function index(Path $path, $levels = null) {
        printf("Indexing: %s\n", $path->getRelative());

        $record = $path->loadRecord();

        // if this path does not exist, and we have a record then delete it
        if(!$path->exists()) {
            if($record) {
                $record->delete();
            }

            return;
        }

        if(!$record) {
            // none exists, create it
            $path->loadCreateRecord();
        }
        else {
            // it exists, check if it needs updating
            $record->checkUpdate($path);
        }

        // index children
        if($levels > 0 || $levels === null) {
            if($levels !== null) {
                $levels--;
            }

            if($path->isDir()) {
                $children = $path->getChildren();

                foreach($children as $child) {
                    self::index($child, $levels);
                }
            }
        }
    }

}