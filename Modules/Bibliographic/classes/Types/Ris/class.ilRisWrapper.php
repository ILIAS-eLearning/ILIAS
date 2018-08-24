<?php
use LibRIS\RISReader;

/**
 * Class ilRisWrapper
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilRisWrapper {

	/**
	 * @param $content
	 *
	 * @return array
	 */
	public function parseContent($content) {
		$RISReader = new RISReader();

		$RISReader->parseString($content);

		return $RISReader->getRecords();
	}


	/**
	 * @param $path_to_file
	 *
	 * @return null
	 * @throws \LibRIS\ParseException
	 */
	public function parseFile($path_to_file) {
		$RISReader = new RISReader();

		$RISReader->parseFile($path_to_file);

		return $RISReader->getRecords();
	}
}
