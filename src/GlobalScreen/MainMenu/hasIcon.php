<?php namespace ILIAS\GlobalScreen\MainMenu;

/**
 * Interface hasIcon
 *
 * Methods for Entries with Icons
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface hasIcon {

	/**
	 * @param string $path_to_svg_icon
	 *
	 * @return hasIcon
	 */
	public function withIconPath(string $path_to_svg_icon): hasIcon;


	/**
	 * @return string
	 */
	public function getIconPath(): string;
}
