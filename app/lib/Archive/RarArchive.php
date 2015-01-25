<?php

namespace Archive;

class RarArchive implements Archive {

    protected $archive;

    public function __construct(\Path $path) {
        $this->archive = \RarArchive::open($path->getPathName());

        if(!$this->archive) {
            throw new Exception('Failed to open archive');
        }
    }

    public function getFiles() {
        $entries = $this->archive->getEntries();

        $files = array();
        foreach($entries as $entry) {
            if(!$entry->isDirectory()) {
                $files[] = $entry->getName();
            }
        }

        return $files;
    }
    
}
