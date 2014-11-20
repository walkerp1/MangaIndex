<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class HashCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'command:hash';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
        $basePath = Config::get('app.manga_path');
        $path = new Path($basePath);

        $this->processPath($path);
	}

    protected function processPath(Path $path) {
        if(!$path->isDir()) {
            $pathRecord = $path->loadCreateRecord();
            $ext = $path->getExtension();

            if(!$pathRecord->shouldHash()) {
                return;
            }

            // delete old hash records
            $pathRecord->imageHashes()->delete();

            if(strcasecmp($ext, 'zip') === 0) {
                $this->processZip($path, $pathRecord);
            }
        }
        else {
            $children = $path->getChildren();
            foreach($children as $child) {
                $this->processPath($child);
            }
        }
    }

    protected function processZip(Path $path, PathRecord $pathRecord) {
        printf("Processing zip: %s\n", $path->getPathname());

        $zip = new ZipArchive();
        if($zip->open($path->getPathname()) !== true) {
            printf("\tERROR: Failed to open.\n");
            return;
        }

        for($i=0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if($this->validImageFilename($name)) {
                printf("\t%s ", $name);

                try {
                    // get stream for zip entry
                    $entryStream = $zip->getStream($name);

                    // create stream for temp file
                    $tempStream = tmpfile();

                    // copy entry to temp file
                    stream_copy_to_stream($entryStream, $tempStream);

                    // close entry stream
                    fclose($entryStream);

                    // get filename of temp stream
                    $streamMeta = stream_get_meta_data($tempStream);
                    $tempFile = $streamMeta['uri'];

                    // hash file
                    $phash = ph_dct_imagehash($tempFile);
                    if(!$phash) {
                        printf("hashing failed\n");
                    }
                    else {
                        $hashRecord = new ImageHash();
                        $hashRecord->path_record_id = $pathRecord->id;
                        $hashRecord->name = $name;
                        $hashRecord->binary_hash = sha1_file($tempFile);
                        $hashRecord->phash = $phash;
                        $hashRecord->save();

                        printf("phash: %x\n", $phash);
                    }
                }
                catch(Exception $e) {
                    printf("ERROR\n");
                }
            }
        }

        $pathRecord->hashed_at = $pathRecord->freshTimestamp();
        $pathRecord->save();
    }

    protected function validImageFilename($filename) {
        $validExt = array('png', 'jpg', 'jpeg', 'bmp', 'gif');
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, $validExt);
    }

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
            //array('worker', InputArgument::REQUIRED, 'ID of worker (1 or 0)'),
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
