<?php

use ILIAS\GlobalScreen\Collector\MainMenu\TypeHandler;
use ILIAS\GlobalScreen\MainMenu\hasAction;
use ILIAS\GlobalScreen\MainMenu\isItem;

/**
 * Class ilMMTypeHandlerLink
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTypeHandlerLink extends ilMMAbstractBaseTypeHandlerAction implements TypeHandler {

	public function matchesForType(): string {
		return \ILIAS\GlobalScreen\MainMenu\Item\Link::class;
	}


	/**
	 * @inheritdoc
	 */
	public function enrichItem(isItem $item): isItem {
		if ($item instanceof hasAction && isset($this->links[$item->getProviderIdentification()->serialize()])) {
			$item = $item->withAction((string)$this->links[$item->getProviderIdentification()->serialize()]);
		}

		return $item;
	}


	/**
	 * @inheritDoc
	 */
	protected function getFieldTranslation(): string {
		global $DIC;

		return $DIC->language()->txt("url");
	}
}
