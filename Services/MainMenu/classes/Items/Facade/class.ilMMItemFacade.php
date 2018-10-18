<?php

/**
 * Class ilMMItemFacade
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemFacade extends ilMMAbstractItemFacade implements ilMMItemFacadeInterface {

	/**
	 * @return bool
	 */
	public function isCustom(): bool {
		return false;
	}


	// Setter
	/**
	 * @inheritDoc
	 */
	public function setType(string $type) {
		$this->type = $type;
		// throw new LogicException("Can't change type");
	}


	/**
	 * @inheritDoc
	 */
	public function setAction(string $action) {
		// Setting action not possible for non custom items
		return;
	}
}

