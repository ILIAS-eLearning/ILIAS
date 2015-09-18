<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
* A Whoops error handler that prints the same content as the PrettyPageHandler but as plain text.
*
* This is used for better coexistence with xdebug, see #16627.
*
* @author Richard Klees <richard.klees@concepts-and-training.de>
* @version $Id$
*/

require_once("./Services/Exceptions/lib/Whoops/Handler/HandlerInterface.php");
require_once("./Services/Exceptions/lib/Whoops/Handler/Handler.php");

use Whoops\Handler\Handler;
use Whoops\Exception\Formatter;

class ilPlainTextHandler extends Handler {
	const KEY_SPACE = 25;

	/**
	 * Last missing method from HandlerInterface.
	 *
	 * @return null
	 */
	public function handle() {
		echo "<pre>\n";
		echo $this->content();
		echo "</pre>\n";
	}

	/**
	 * Assemble the output for this handler.
	 *
	 * @return string
	 */
	protected function content() {
		return $this->pageHeader()
			  .$this->exceptionContent()
			  .$this->tablesContent()
			  ;
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
		return Formatter::formatExceptionPlain($this->getInspector());
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

?>
