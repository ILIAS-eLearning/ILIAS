<?php

use ILIAS\GlobalScreen\Collector\MainMenu\TypeHandler;
use ILIAS\GlobalScreen\MainMenu\isItem;

/**
 * Class ilMMTypeHandlerTopLink
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTypeHandlerTopLink extends ilMMAbstractBaseTypeHandlerAction implements TypeHandler {

	/**
	 * @inheritdoc
	 */
	public function matchesForType(): string {
		return \ILIAS\GlobalScreen\MainMenu\TopItem\TopLinkItem::class;
	}


	/**
	 * @inheritdoc
	 */
	public function enrichItem(isItem $item): isItem {
		if ($item instanceof \ILIAS\GlobalScreen\MainMenu\TopItem\TopLinkItem && isset($this->links[$item->getProviderIdentification()->serialize()])) {
			$item = $item->withAction((string)$this->links[$item->getProviderIdentification()->serialize()]);
		}

		return $item;
	}
}
