<?php
require_once('./Modules/Bibliographic/interfaces/interface.ilBibliograficFileReader.php');

/**
 * Class ilBibliograficFileReaderBase
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilBibliograficFileReaderBase implements ilBibliograficFileReader {

	const ENCODING_UTF_8 = 'UTF-8';
	const ENCODING_ASCII = 'ASCII';
	const ENCODING_ISO_8859_1 = 'ISO-8859-1';
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
		$this->setPathToFile($path_to_file);
		//		$this->convertFiletoUTF8();
		$this->setFileContent($this->convertStringToUTF8(file_get_contents($path_to_file)));

		return true;
	}


	protected function convertFiletoUTF8() {
		file_put_contents($this->getPathToFile(), $this->convertStringToUTF8(file_get_contents($this->getPathToFile())));
	}


	/**
	 * @param $string
	 * @return string
	 */
	protected function convertStringToUTF8($string) {
		ob_end_clean();
		$mb_detect_encoding = mb_detect_encoding($string);
		mb_detect_order(array( self::ENCODING_UTF_8, self::ENCODING_ISO_8859_1 ));
		switch ($mb_detect_encoding) {
			case self::ENCODING_UTF_8:
				break;
			case self::ENCODING_ASCII:
				$string = utf8_encode(iconv(self::ENCODING_ASCII, 'UTF-8//IGNORE', $string));
				break;
			default:
				$string = mb_convert_encoding($string, self::ENCODING_UTF_8, $mb_detect_encoding);
				break;
		}

		return $string;
	}


	/**
	 * @return string
	 */
	public function getFileContent() {
		return $this->file_content;
	}


	/**
	 * @param string $file_content
	 */
	public function setFileContent($file_content) {
		$this->file_content = $file_content;
	}


	/**
	 * @return string
	 */
	public function getPathToFile() {
		return $this->path_to_file;
	}


	/**
	 * @param string $path_to_file
	 */
	public function setPathToFile($path_to_file) {
		$this->path_to_file = $path_to_file;
	}
}
