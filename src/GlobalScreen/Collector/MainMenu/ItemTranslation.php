<?php namespace ILIAS\GlobalScreen\Collector\MainMenu;

use ILIAS\GlobalScreen\MainMenu\hasTitle;
use ILIAS\GlobalScreen\MainMenu\isItem;

/**
 * Class ItemSorting
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ItemTranslation {

	/**
	 * @param hasTitle $item
	 *
	 * @return hasTitle
	 */
	public function translateItemForUser(hasTitle $item): hasTitle;
}
