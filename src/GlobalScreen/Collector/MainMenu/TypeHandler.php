<?php namespace ILIAS\GlobalScreen\Collector\MainMenu;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\MainMenu\isItem;
use ILIAS\UI\Component\Input\Field\Input;

/**
 * Class TypeHandler
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface TypeHandler {

	/**
	 * @return string Classname of matching Type this TypeHandler can handle
	 */
	public function matchesForType(): string;


	/**
	 * @param isItem $item
	 *
	 * @return isItem
	 */
	public function enrichItem(isItem $item): isItem;


	/**
	 * @return Input[]
	 */
	public function getAdditionalFieldsForSubForm(): array;


	/**
	 * @param IdentificationInterface $identification
	 * @param array                   $data
	 *
	 * @return bool
	 */
	public function saveFormFields(IdentificationInterface $identification, array $data): bool;
}
