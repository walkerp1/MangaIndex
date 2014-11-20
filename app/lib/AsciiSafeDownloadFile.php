<?php

use Symfony\Component\HttpFoundation\File\File;

/*
    This class is a hack/workaround for the InvalidArgumentException "The filename fallback must only contain ASCII characters."
    for file downloads with non-ASCII filenames. Since the Response::download() method doesn't allow specifying a fallback name,
    this is the only realistic option, aside from extending, rewriting, and maintaining a bunch of framework classes, or
    modifying the framework itself.
*/

class AsciiSafeDownloadFile extends File {

    /*
        Take the original filename and ASCII-fy it
    */
    public function getFilename() {
        $orig = parent::getFilename();
        return Str::ascii($orig);
    }

}