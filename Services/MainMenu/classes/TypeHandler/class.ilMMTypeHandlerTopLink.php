<?php

use ILIAS\GlobalScreen\Collector\MainMenu\Handler\TypeHandler;
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
			$action = (string)$this->links[$item->getProviderIdentification()->serialize()][self::F_ACTION];
			$is_external = (bool)$this->links[$item->getProviderIdentification()->serialize()][self::F_EXTERNAL];
			$item = $item->withAction($action)->withIsLinkToExternalAction($is_external);
		}

		return $item;
	}


	/**
	 * @inheritDoc
	 */
	protected function getFieldTranslation(): string {
		global $DIC;

		return $DIC->language()->txt("field_url");
	}


	/**
	 * @inheritDoc
	 */
	protected function getFieldInfoTranslation(): string {
		global $DIC;

		return $DIC->language()->txt("field_url_info");
	}
}
