<?php namespace ILIAS\GlobalScreen\Collector\MainMenu;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\MainMenu\isItem;
use ILIAS\UI\Component\Input\Field\Input;

/**
 * Class BaseTypeHandler
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
final class BaseTypeHandler implements TypeHandler {

	/**
	 * @inheritDoc
	 */
	public function matchesForType(): string {
		return "";
	}


	/**
	 * @inheritDoc
	 */
	public function enrichItem(isItem $item): isItem {
		return $item;
	}


	/**
	 * @inheritDoc
	 */
	public function getAdditionalFieldsForSubForm(): array {
		return array();
	}


	/**
	 * @inheritDoc
	 */
	public function saveFormFields(IdentificationInterface $identification, array $data): bool {
		return true;
	}
}
