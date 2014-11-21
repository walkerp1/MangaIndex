<?php

class RecentController extends BaseController {

    public function recent() {

        $records = $this->getRecentRecords();

        $paths = array();
        $bucket = array();
        $currentParent = null;
        foreach($records as $record) {
            $path = Path::fromRelative($record->path);

            if($path->exists()) {
                $path->record = $record;
                $parent = $path->getParent();

                if($currentParent === null) {
                    $currentParent = $parent;
                }

                // if this path's parent is the same as the previous, add it to the bucket
                if($parent->getHash() === $currentParent->getHash()) {
                    $bucket[] = $path;
                }
                else {
                    // if's different, add it to the paths array and start a new bucket
                    $paths[] = array('parent' => $currentParent, 'paths' => $bucket);
                    $bucket = array($path);
                    $currentParent = $parent;
                }
            }
        }

        if(count($bucket) > 0) {
            $paths[] = array('parent' => $currentParent, 'paths' => $bucket);
        }

        return View::make('recent', array('pathBuckets' => $paths, 'pageTitle' => 'Recent uploads'));
    }

    public function rss() {
        $records = $this->getRecentRecords();

        $feed = Feed::make();

        $feed->title = '/a/ manga';
        $feed->description = '/a/ manga';
        $feed->logo = URL::to('/img/icon.png');
        $feed->link = URL::route('recent');
        $feed->lang = 'en';

        if(count($records) > 0) {
            $feed->setDateFormat('datetime');
            $feed->pubdate = $records[0]->modified;
        }

        foreach($records as $record) {
            $path = Path::fromRelative($record->path);

            if($path->exists()) {
                $link = sprintf('<a href="%s">%s</a>', URL::to($path->getUrl()), $record->path);
                $feed->add($record->path, 'Madokami', URL::to($path->getUrl()), $record->modified, $link, $link);
            }
        }

        return $feed->render('atom');
    }

    protected function getRecentRecords() {
        $records = PathRecord::whereDirectory(false)
            ->whereRaw('left(path, 5) in ("/Mang", "/Raws", "/Ones")')
            ->orderBy('modified', 'desc')
            ->take(1000)
            ->get();

        return $records;
    }

}
