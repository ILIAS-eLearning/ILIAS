<?php namespace ILIAS\GlobalScreen\BootLoader;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

/**
 * Class RegexFinder
 *
 * @package ILIAS\Collector
 */
class RegexFinder {

	/**
	 * @var string
	 */
	private $regex = "";
	/**
	 * @var string
	 */
	private $path = "";


	/**
	 * RegexFinder constructor.
	 *
	 * @param string $regex
	 * @param string $path
	 */
	public function __construct(string $regex, string $path) {
		$this->regex = $regex;
		$this->path = $path;
	}


	/**
	 * @return array
	 */
	public function getFiles(): array {
		$Directory = new RecursiveDirectoryIterator($this->path);
		$Iterator = new RecursiveIteratorIterator($Directory);
		$Regex = new RegexIterator($Iterator, $this->regex, RecursiveRegexIterator::GET_MATCH);
		$files = [];
		foreach ($Regex as $file) {
			$files[$file[0]] = $file[1];
		}

		return $files;
	}
}