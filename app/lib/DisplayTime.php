<?php

class DisplayTime {

    public static function format($time, $short = false) {
        $now = new DateTime();
        $created = new DateTime(date('Y-m-d H:i:s', $time));

        $diff = $created->diff($now);
        
        if($diff->d > 7 || $diff->m > 0) {
            return date('Y-m-d H:i', $time);
        }
        else {
            $steps = array(
                'd' => array(
                    'long' => array('1 day ago', '%d days ago'),
                    'short' => '%dd'
                ),
                'h' => array(
                    'long' => array('1 hour ago', '%h hours ago'),
                    'short' => '%hh'
                ),
                'i' => array(
                    'long' => array('1 minute ago', '%i minutes ago'),
                    'short' => '%im'
                ),
                's' => array(
                    'long' => array('1 second ago', '%s seconds ago'),
                    'short' => '%ss'
                )
            );

            foreach($steps as $var => $messages) {
                if($diff->$var > 0) {
                    if($short) {
                        return $diff->format($messages['short']);
                    }

                    if($diff->$var === 1) {
                        return $messages['long'][0];
                    }
                    else {
                        return $diff->format($messages['long'][1]);
                    }
                }
            }
        }
    }

}
