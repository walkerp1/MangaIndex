<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class GenerateSitemapCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'command:generate-sitemap';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generates a sitemap with all path records.';

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
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        PathRecord::chunk(200, function($records) use($xml) {
            foreach($records as $record) {
                $path = $record->getPath();
                if($path->exists()) {
                    $url = URL::to($path->getUrl());

                    $xml->startElement('url');
                    $xml->writeElement('loc', $url);
                    $xml->endElement();
                }
            }
        });

        $xml->endElement();
        $xml->endDocument();
        echo $xml->flush();
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
