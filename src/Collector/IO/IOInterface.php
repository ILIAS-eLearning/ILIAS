<?php namespace ILIAS\Collector\IO;

/**
 * Interface IOInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface IOInterface {

	/**
	 * @param string $output
	 */
	public function write(string $output): void;
}
