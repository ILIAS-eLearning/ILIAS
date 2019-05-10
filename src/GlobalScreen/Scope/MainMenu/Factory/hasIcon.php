<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

use ILIAS\UI\Component\Symbol\Icon\Icon;

/**
 * Interface hasIcon
 *
 * Methods for Entries with Icons
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface hasIcon {

	/**
	 * @param Icon $icon
	 *
	 * @return hasIcon
	 */
	public function withIcon(Icon $icon): hasIcon;


	/**
	 * @return Icon
	 */
	public function getIcon(): Icon;


	/**
	 * @return bool
	 */
	public function hasIcon(): bool;
}
