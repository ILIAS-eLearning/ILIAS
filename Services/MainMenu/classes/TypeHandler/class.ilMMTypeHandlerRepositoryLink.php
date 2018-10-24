<?php

use ILIAS\GlobalScreen\Collector\MainMenu\TypeHandler;
use ILIAS\GlobalScreen\MainMenu\hasAction;
use ILIAS\GlobalScreen\MainMenu\isItem;

/**
 * Class ilMMTypeHandlerRepositoryLink
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTypeHandlerRepositoryLink extends ilMMAbstractBaseTypeHandlerAction implements TypeHandler {

	public function matchesForType(): string {
		return \ILIAS\GlobalScreen\MainMenu\Item\RepositoryLink::class;
	}


	/**
	 * @inheritdoc
	 */
	public function enrichItem(isItem $item): isItem {
		if ($item instanceof \ILIAS\GlobalScreen\MainMenu\Item\RepositoryLink && isset($this->links[$item->getProviderIdentification()->serialize()])) {
			$item = $item->withRefId((int)$this->links[$item->getProviderIdentification()->serialize()]);
		}

		return $item;
	}


	/**
	 * @inheritdoc
	 */
	public function getAdditionalFieldsForSubForm(\ILIAS\GlobalScreen\Identification\IdentificationInterface $identification): array {
		global $DIC;
		$url = $DIC->ui()->factory()->input()->field()->numeric($this->getFieldTranslation());
		if (isset($this->links[$identification->serialize()]) && is_numeric($this->links[$identification->serialize()])) {
			$url = $url->withValue((int)$this->links[$identification->serialize()]);
		}

		return [self::F_ACTION => $url];
	}


	/**
	 * @inheritDoc
	 */
	protected function getFieldTranslation(): string {
		global $DIC;

		return $DIC->language()->txt("ref_id");
	}
}
