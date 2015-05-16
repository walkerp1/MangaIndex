<?php

use Foolz\SphinxQL\SphinxQL;
use Foolz\SphinxQL\Connection;
use Foolz\SphinxQL\Helper;

class Search {
    
    const SEARCH_THRESHOLD = 6;
    
    public static function searchPaths($keyword, &$count) {
        $conn = self::getSphinxConnection();

        $query = SphinxQL::create($conn)
            ->select('*')
            ->from('mangaindexnew_paths')
            ->where('directory', '=', 1)
            ->limit(0, 100);

        $escaped = $query->halfEscapeMatch($keyword);
        $escaped = str_replace('?', '\?', $escaped); // "?" behaves strangely and doesn't get escaped by the above
        $query->match('*', $escaped);

        $result = $query->execute();
        $ids = self::getIds($result);

        $metaResult = Helper::create($conn)->showMeta()->execute();
        $meta = self::sortMeta($metaResult);

        $count = $meta['total_found'];

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
        $sphConfig = Config::get('sphinx.ql_connection');
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

    protected static function sortMeta($meta) {
        $result = array();

        foreach($meta as $row) {
            $result[$row['Variable_name']] = $row['Value'];
        }

        return $result;
    }

    public static function url($keyword, $type = null) {
        $keyword = strtolower($keyword);
        $keyword = str_replace('/', '%2F', $keyword); // URL::route() will not encode forward slashes

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