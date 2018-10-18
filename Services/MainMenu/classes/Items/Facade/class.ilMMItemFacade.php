<?php

use ILIAS\GlobalScreen\Collector\MainMenu\Main;
use ILIAS\GlobalScreen\MainMenu\isItem;

/**
 * Class ilMMItemFacade
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemFacade extends ilMMAbstractItemFacade implements ilMMItemFacadeInterface {

	public function getDefaultTitle(): string {
		if ($this->gs_item instanceof \ILIAS\GlobalScreen\MainMenu\hasTitle) { //FSX
			return $this->gs_item->getTitle();
		}

		return "No Title";
	}


	/**
	 * @return bool
	 */
	public function isCustom(): bool {
		return false;
	}


	// Setter

	public function setDefaultTitle(string $default_title) {
		// FSX Default Title Handling missing
		return;
	}


	/**
	 * @inheritDoc
	 */
	public function setType(string $type) {
		throw new LogicException("Can't change type");
	}


	/**
	 * @inheritDoc
	 */
	public function setAction(string $action) {
		// Setting action not possible for non custom items
		return;
	}
}

