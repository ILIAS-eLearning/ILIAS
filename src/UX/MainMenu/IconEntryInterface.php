<?php namespace ILIAS\UX\MainMenu;

/**
 * Interface IconEntryInterface
 *
 * Methods for Entries with Icons
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface IconEntryInterface {

	/**
	 * @param string $path_to_svg_icon
	 *
	 * @return IconEntryInterface
	 */
	public function withIconPath(string $path_to_svg_icon): IconEntryInterface;


	/**
	 * @return string
	 */
	public function getIconPath(): string;
}
