<?php

use ILIAS\GlobalScreen\Collector\MainMenu\TypeHandler;
use ILIAS\GlobalScreen\MainMenu\hasAction;
use ILIAS\GlobalScreen\MainMenu\isItem;

/**
 * Class ilMMTypeHandlerTopLink
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTypeHandlerTopLink extends ilMMAbstractBaseTypeHandlerAction implements TypeHandler {

	public function matchesForType(): string {
		return \ILIAS\GlobalScreen\MainMenu\TopItem\TopLinkItem::class;
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
