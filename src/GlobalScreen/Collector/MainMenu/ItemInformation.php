<?php namespace ILIAS\GlobalScreen\Collector\MainMenu;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\MainMenu\isItem;
use ILIAS\GlobalScreen\MainMenu\hasTitle;
use ILIAS\GlobalScreen\MainMenu\isChild;
use ILIAS\GlobalScreen\MainMenu\isTopItem;

/**
 * Class ItemInformation
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ItemInformation {

	/**
	 * @param isItem $item
	 *
	 * @return bool
	 */
	public function isItemActive(isItem $item): bool;


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


	/**
	 * @param hasTitle $item
	 *
	 * @return hasTitle
	 */
	public function translateItemForUser(hasTitle $item): hasTitle;


	/**
	 * @param isChild $item
	 *
	 * @return IdentificationInterface
	 */
	public function getParent(isChild $item): IdentificationInterface;


	/**
	 * @param isItem $item
	 *
	 * @return TypeHandler
	 */
	public function getTypeHandlerForType(isItem $item): TypeHandler;
}
