<?php

namespace Archive;

class Utils {

    public static function filterImageFiles($files) {
        $result = array();

        foreach($files as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);

            if(in_array($ext, array('jpg', 'jpeg', 'png'))) {
                $result[] = $file;
            }
        }

        return $result;
    }

}