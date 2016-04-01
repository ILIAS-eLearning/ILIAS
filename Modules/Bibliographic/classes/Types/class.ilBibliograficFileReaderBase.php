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
	 * @var string
	 */
	protected $path_to_file = '';


	/**
	 * @param $path_to_file
	 * @return bool
	 */
	public function readContent($path_to_file) {
		$this->path_to_file = $path_to_file;
//		$this->convertFiletoUTF8();
		$this->file_content = $this->convertStringToUTF8(file_get_contents($path_to_file));

		return true;
	}


	protected function convertFiletoUTF8() {
		file_put_contents($this->path_to_file, $this->convertStringToUTF8(file_get_contents($this->path_to_file)));
	}


	/**
	 * @param $string
	 * @return string
	 */
	protected function convertStringToUTF8($string) {
		ob_end_clean();
		$mb_detect_encoding = mb_detect_encoding($string);
		mb_detect_order(array( 'UTF-8', 'ISO-8859-1' ));
		switch ($mb_detect_encoding) {
			case 'UTF-8':
				break;
			case 'ASCII':
				$string = utf8_encode(iconv('ASCII', 'UTF-8//IGNORE', $string));
				break;
			default:
				$string = mb_convert_encoding($string, 'UTF-8', $mb_detect_encoding);
				break;
		}

		return $string;
	}
}
