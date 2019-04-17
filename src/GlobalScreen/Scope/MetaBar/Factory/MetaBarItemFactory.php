<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Class MetaBarItemFactory
 *
 * This factory provides you all available types for MainMenu GlobalScreen Items.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MetaBarItemFactory {

	/**
	 * @param IdentificationInterface $identification
	 *
	 * @return BaseItem
	 */
	public function baseItem(IdentificationInterface $identification): BaseItem {
		return new BaseItem($identification);
	}
}
