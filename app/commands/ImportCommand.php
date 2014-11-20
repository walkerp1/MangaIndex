<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ImportCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'command:import';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Import from old manga index db.';

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
        $importDb = DB::connection('import');

		/*
		$importDb->statement('delete from mangaindex_new.facet_series');
		$importDb->statement('delete from mangaindex_new.facets');
		$importDb->statement('delete from mangaindex_new.path_records');
		$importDb->statement('delete from mangaindex_new.series');
		*/

        $importDb->statement('delete from mangaindex_new.users');
        $importDb->statement(
            'INSERT INTO `mangaindex_new`.`users` (`username`, `password_hash`, `created_at`)
            SELECT username, password_hash, registered FROM mangaindex.user;'
        );

		$iter = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(Config::get('app.manga_path'), RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST,
			RecursiveIteratorIterator::CATCH_GET_CHILD
		);

		$index = 0;
		foreach ($iter as $info) {
			if(in_array($info->getBasename(), array('.', '..'))) {
				continue;
			}

			if(!$info->isDir()) {
				continue;
			}

			printf("\r%d", $index++);

			$path = new Path($info->getPathname());
			$path->loadCreateRecord();

			$pathResult = $importDb->select('select * from path where path = ? limit 1', array($path->getRelative()));
			if(count($pathResult) === 1) {
				if($pathResult[0]->incomplete != $path->record->incomplete) {
					$path->record->incomplete = $pathResult[0]->incomplete;
					$path->record->save();
				}

				if($path->record->series_id || !$pathResult[0]->manga_id) {
                                	continue;
                        	}

				$mangaResult = $importDb->select('select * from manga where id = ? limit 1', array($pathResult[0]->manga_id));
				if(count($mangaResult) === 1) {
					$muId = $mangaResult[0]->muid;

					$series = Series::getCreateFromMuId($muId);
					if($series) {
						$path->record->series_id = $series->id;
						$path->record->save();
					}
				}
			}
		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(

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

		);
	}

}
