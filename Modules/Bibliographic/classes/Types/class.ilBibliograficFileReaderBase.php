<?php

require_once('./Modules/Bibliographic/interfaces/interface.ilBibliograficFileReader.php');

/**
 * Class ilBibliograficFileReaderBase
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilBibliograficFileReaderBase implements ilBibliograficFileReader {

	/**
	 * @var string
	 */
	protected $file_content = '';


	/**
	 * @param $path_to_file
	 * @return bool
	 */
	public function readContent($path_to_file) {
		$this->convertFiletoUTF8($path_to_file);
		$raw_content = file_get_contents($path_to_file);
		$this->file_content = $raw_content;

		return true;
	}


	/**
	 * @param $path_to_file
	 */
	protected function convertFiletoUTF8($path_to_file) {
		$filedata = file_get_contents($path_to_file);
		if (strlen($filedata) == strlen(utf8_decode($filedata))) {
			$filedata = mb_convert_encoding($filedata, 'UTF-8', 'ISO-8859-1');
			file_put_contents($path_to_file, $filedata);
		}
	}
}
