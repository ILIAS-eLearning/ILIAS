<?php
require_once('./libs/composer/vendor/autoload.php');
use LibRIS\RISReader;

/**
 * Class ilRisWrapper
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilRisWrapper {

	/**
	 * @param $content
	 * @return array
	 */
	public function parseContent($content) {
		$RISReader = new RISReader();
		$RISReader->parseString($content);

		return $RISReader->getRecords();
	}
}
