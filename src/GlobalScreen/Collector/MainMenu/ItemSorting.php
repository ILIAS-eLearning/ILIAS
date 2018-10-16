<?php namespace ILIAS\GlobalScreen\Collector\MainMenu;

use ILIAS\GlobalScreen\MainMenu\isChild;
use ILIAS\GlobalScreen\MainMenu\isTopItem;

/**
 * Class ItemSorting
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ItemSorting {

	/**
	 * @param isChild $child
	 *
	 * @return int
	 */
	public function getPositionOfSubItem(isChild $child): int;


	/**
	 * @param isTopItem $top_item
	 *
	 * @return int
	 */
	public function getPositionOfTopItem(isTopItem $top_item): int;
}
