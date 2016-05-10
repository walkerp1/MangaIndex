<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class WatcherCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'command:watcher';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load filesystem watcher for indexing paths in realtime';

    protected $watchEvents = array(
        IN_ATTRIB => 'IN_ATTRIB',
        IN_CLOSE_WRITE => 'IN_CLOSE_WRITE',
        IN_MOVED_TO => 'IN_MOVED_TO',
        IN_MOVED_FROM => 'IN_MOVED_FROM',
        IN_CREATE => 'IN_CREATE',
        IN_DELETE => 'IN_DELETE',
        IN_DELETE_SELF => 'IN_DELETE_SELF',
        IN_MOVE_SELF => 'IN_MOVE_SELF'
    );

    protected $computedMask = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->computeMask();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $watches = array();

        $in = inotify_init();

        // add watches starting from root directory
        $root = Path::fromRelative('');
        $this->addWatches($in, $root, $watches);

        printf("\nReading for events\n");
        while(true) {
            $events = inotify_read($in);

            foreach($events as $event) {
                $path = $watches[$event['wd']];

                $expanded = $this->expandMask($event['mask']);
                $eventName = trim(implode(', ', $expanded), ', ');

                // if the event has a name attached, then index that
                if($event['name']) {
                    $newPathName = $path->getPathname().'/'.$event['name'];
                    $newPath = new Path($newPathName);
                    Indexer::index($newPath, 1);

                    // this may be a new directory, so add a watch to it anyway
                    if($newPath->exists() && $newPath->isDir()) {
                        $wd = inotify_add_watch($in, $newPath->getPathname(), $this->computedMask);
                        $watches[$wd] = $newPath;
                    }
                }
                else {
                    // event must apply to this directory, so index it, 1 level deep
                    Indexer::index($path, 1);
                }
            }
        }
    }

    protected function addWatches($in, Path $path, &$watches) {
        if(!$path->isDir()) {
            // not in a directory, bail
            return;
        }

        $wd = inotify_add_watch($in, $path->getPathname(), $this->computedMask);
        $watches[$wd] = $path;

        //printf("\rAdding watches... %d", count($watches));

        // recurse into this directory's children
        $children = $path->getChildren();
        foreach($children as $child) {
            if($child->isDir()) {
                $this->addWatches($in, $child, $watches);
            }
        }
    }

    protected function computeMask() {
        if($this->computedMask === null) {
            foreach(array_keys($this->watchEvents) as $event) {
                $this->computedMask |= $event;
            }
        }

        return $this->computedMask;
    }

    protected function expandMask($mask) {
        $ret = $this->watchEvents;

        foreach($ret as $key => $value) {
            if(($mask & $key) === 0) {
                unset($ret[$key]);
            }
        }

        return $ret;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            //array('example', InputArgument::REQUIRED, 'An example argument.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            //array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
        );
    }

}
