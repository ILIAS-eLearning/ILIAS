<?php
/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */
require_once './libs/composer/vendor/autoload.php';

use Whoops\Exception\Formatter;

/**
 * Saves error informations into file
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilLoggingErrorFileStorage {
	const KEY_SPACE = 25;
	const FILE_FORMAT = ".log";

	public function __construct($inspector, $file_path, $file_name) {
		$this->inspector = $inspector;
		$this->file_path = $file_path;
		$this->file_name = $file_name;
	}

	protected function createDir($path) {
		if(!is_dir($this->file_path)) {
			ilUtil::makeDirParents($this->file_path);
		}
	}

	protected function content() {
		return $this->pageHeader()
			  .$this->exceptionContent()
			  .$this->tablesContent()
			  ;
	}

	public function write() {
		$this->createDir($this->file_path);

		$file_name = $this->file_path."/".$this->file_name.self::FILE_FORMAT;
		$stream = fopen($file_name, 'w+');
		fwrite($stream, $this->content());
		fclose($stream);
		chmod($file_name, 0755);
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