<?php

use Foolz\SphinxQL\SphinxQL;
use Foolz\SphinxQL\Connection;

class Search {
    
    const SEARCH_THRESHOLD = 6;
    
    public static function searchPaths($keyword) {
        $conn = self::getSphinxConnection();

        $query = SphinxQL::create($conn)
            ->select('*')
            ->from('mangaindexnew_paths')
            ->where('directory', '=', 1)
            ->limit(0, 100);

        $query->match('*', $query->halfEscapeMatch($keyword));

        $result = $query->execute();
        $ids = self::getIds($result);

        if(count($ids) > 0) {
            $records = PathRecord::whereIn('id', $ids)->get();

            return $records;
        }
        else {
            return array();
        }
    }

    protected static function getSphinxConnection() {
        $conn = new Connection();
        $sphConfig = Config::get('database.sphinxql');
        $conn->setParams($sphConfig);
        return $conn;
    }

    protected static function getIds($result) {
        $ids = array();

        foreach($result as $row) {
            $ids[] = $row['id'];
        }

        return $ids;
    }

    public static function url($keyword, $type = null) {
        $keyword = strtolower($keyword);

        if($type) {
            return URL::route('searchKeywordType', array('type' => $type, 'keyword' => $keyword));
        }
        else {
            return URL::route('search', array('keyword' => $keyword));
        }
    }

    public static function byImage($inputFilePath) {
        $binaryHash = sha1_file($inputFilePath);
        $phash = ph_dct_imagehash($inputFilePath);

        if(!$phash) {
            return false;
        }

        $results = ImageHash::whereRaw('binary_hash = ? or bit_count(phash ^ ?) <= ?', array($binaryHash, $phash, self::SEARCH_THRESHOLD))->get();
        $paths = array();

        foreach($results as $hash) {
            $record = $hash->pathRecord;
            $path = $record->getPath();
            $paths[$record->id] = $path;
        }

        return $paths;
    }

    public static function suggest($keyword) {
        $conn = self::getSphinxConnection();

        $query = SphinxQL::create($conn)
            ->select('*')
            ->from('mangaindexnew_suggested')
            ->limit(0, 100);

        $keyword = rtrim($keyword, '*').'*';
        $query->match('*', $query->halfEscapeMatch($keyword));

        $result = $query->execute();

        $suggestions = array();
        foreach($result as $row) {
            $suggestions[] = array('value' => $row['keyword']);
        }

        return $suggestions;
    }
}