<?php
/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

include_once('Services/FileSystem/classes/class.ilFileSystemStorage.php');

use Whoops\Exception\Formatter;

/**
 * Saves error informations into file
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilLoggingErrorFileStorage extends ilFileSystemStorage {
	const KEY_SPACE = 25;

	public function __construct($inspector, $a_container_id = 0) {
		$this->inspector = $inspector;
		parent::__construct(self::STORAGE_WEB, true, $a_container_id);
		$this->create();
	}

	protected function getPathPostfix() {
		return 'errl';
	}

	protected function getPathPrefix() {
		return 'errl';
	}

	protected function content() {
		return $this->pageHeader()
			  .$this->exceptionContent()
			  .$this->tablesContent()
			  ;
	}

	public function write($filename) {
		$this->writeToFile($this->content(), $this->getAbsolutePath()."/".$filename);
	}

	/**
	 * Get the header for the page.
	 *
	 * @return string
	 */
	protected function pageHeader() {
		return "";
	}

	/**
	 * Get a short info about the exception.
	 *
	 * @return string
	 */
	protected function exceptionContent() {
		return Formatter::formatExceptionPlain($this->inspector);
	}

	/**
	 * Get the header for the page.
	 *
	 * @return string
	 */
	protected function tablesContent() {
		$ret = "";
		foreach ($this->tables() as $title => $content) {
			$ret .= "\n\n-- $title --\n\n";
			if (count($content) > 0) {
				foreach ($content as $key => $value) {
					$key = str_pad($key, self::KEY_SPACE);

					// indent multiline values, first print_r, split in lines,
					// indent all but first line, then implode again.
					$first = true;
					$indentation = str_pad("", self::KEY_SPACE);
					$value = implode("\n", array_map(function($line) use (&$first, $indentation) {
								if ($first) {
									$first = false;
									return $line;
								}
								return $indentation.$line;
							}, explode("\n", print_r($value, true))));

					$ret .= "$key: $value\n";
				}
			}
			else {
				$ret .= "empty\n";
			}
		}
		return $ret;
	}

	/**
	 * Get the tables that should be rendered.
	 *
	 * @return array 	$title => $table
	 */
	protected function tables() {
		return array
			( "GET Data" => $_GET
			, "POST Data" => $_POST
			, "Files" => $_FILES
			, "Cookies" => $_COOKIES
			, "Session" => isset($_SESSION) ? $_SESSION : array()
			, "Server/Request Data" => $_SERVER
			, "Environment Variables" => $_ENV
			);
	}	
}