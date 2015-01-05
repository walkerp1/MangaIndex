<?php

class Path extends SplFileInfo {

    public $record;
    protected $hash;

    // file extensions that are safe(ish) for users to download
    protected static $safeFileExtensions = array('zip', 'rar', 'cbz', '7z', 'txt', 'jpg', 'png', 'bmp', 'cbr', 'md5');

    public static function fromRelative($relPath) {
        $path = realpath(Config::get('app.manga_path').$relPath);
        return new Path($path);
    }

    public function getRelative() {
        $basePath = rtrim(self::fixSlashes(Config::get('app.manga_path')), '/');
        $absPath = self::fixSlashes($this->getPathname());

        return str_replace($basePath, '', $absPath);
    }

    public function getRelativeTop($count = 2) {
        $path = trim($this->getRelative(), '/');
        $bits = explode('/', $path);

        if($count > $bits) {
            return $this->getRelative();
        }
        else {
            $bits = array_slice($bits, count($bits) - $count, $count);
            return '/'.implode('/', $bits);
        }
    }

    public static function fixSlashes($path) {
        return str_replace('\\', '/', $path);
    }

    public static function hashPath($path) {
        $path = self::fixSlashes($path);
        return sha1($path);
    }

    public function getHash() {
        if(!$this->hash) {
            $path = $this->getRelative();
            $this->hash = self::hashPath($path);
        }

        return $this->hash;
    }

    public function loadCreateRecord() {
        if(!$this->record) {
            $this->record = PathRecord::getCreateForPath($this);
        }

        return $this->record;
    }

    public function loadRecord() {
        if(!$this->record) {
            $this->record = PathRecord::getForPath($this);
        }

        return $this->record;
    }
    
    public function getParent() {
        if(!$this->isRoot()) {
            return new Path($this->getPath());
        }
    }

    public function getChildren($ignoreDots = true) {
        $fileNames = scandir($this->getPathname());

        $ret = array();
        foreach($fileNames as $fileName) {
            if($ignoreDots && in_array($fileName, array('.', '..'))) {
                continue;
            }

            $path = new self($this->getPathname().'/'.$fileName);
            $ret[] = $path;
        }

        return $ret;
    }

    public function isRoot() {
        return ($this->getRelative() === '');
    }

    public function exists() {
        return file_exists($this->getPathname());
    }

    public function getDisplayName() {
        if($this->isRoot()) {
            return '/';
        }
        else {
            return $this->getFilename();
        }
    }

    public function getBreadcrumbs() {
        if($this->isRoot()) { // root fucks with everything...
            $path = Path::fromRelative('/');
            return array($path);
        }
        else {
            $path = trim($this->getRelative(), '/');
            $bits = explode('/', $path);

            $crumbs = array();
            $crumbs[] = Path::fromRelative('/');

            for ($i = 1; $i <= count($bits); $i++) { 
                $path = '/'.implode('/', array_slice($bits, 0, $i));
                $crumbs[] = Path::fromRelative($path);
            }

            return $crumbs;
        }
    }

    public function getUrl() {
        $path = trim($this->getRelative(), '/');
        $bits = explode('/', $path);
        $bits = array_map('rawurlencode', $bits);
        $url = implode('/', $bits);

        return '/'.$url;
    }

    public function getDisplaySize() {
        $size = $this->getSize();

        if($size === 0) {
            return '0';
        }

        $base = log($size) / log(1024);
        $suffixes = array('', 'K', 'M', 'G', 'T');
        $suffix = $suffixes[floor($base)];

        $dp = 1;
        if($suffix === '' || $suffix === 'K') {
            $dp = 0;
        }
        
        return number_format(pow(1024, $base - floor($base)), $dp) . $suffix;
    }

    public function getDisplayTime($short = false) {
        return DisplayTime::format($this->getMTime(), $short);
    }

    public function isSafeExtension() {
        $ext = $this->getExtension();
        return ($ext && in_array($ext, self::$safeFileExtensions));
    }

    public function export() {
        $data = new stdClass();

        // FS stat-based info
        $data->name = $this->getDisplayName();
        $data->size = $this->getDisplaySize();
        $data->rawSize = $this->getSize();
        $data->rawTime = $this->getMTime();
        $data->url = $this->getUrl();
        $data->isDir = $this->isDir();

        $record = $this->loadCreateRecord();
        if($record) {
            $data->record = $record->export();
            unset($record);
        }

        return $data;
    }
}
