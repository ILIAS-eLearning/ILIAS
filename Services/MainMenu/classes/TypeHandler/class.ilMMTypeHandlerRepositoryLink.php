<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler\TypeHandler;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasAction;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;

/**
 * Class ilMMTypeHandlerRepositoryLink
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTypeHandlerRepositoryLink extends ilMMAbstractBaseTypeHandlerAction implements TypeHandler {

	public function matchesForType(): string {
		return \ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\RepositoryLink::class;
	}


	/**
	 * @inheritdoc
	 */
	public function enrichItem(isItem $item): isItem {
		if ($item instanceof \ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\RepositoryLink && isset($this->links[$item->getProviderIdentification()->serialize()][self::F_ACTION])) {
			$item = $item->withRefId((int)$this->links[$item->getProviderIdentification()->serialize()][self::F_ACTION]);
		}

		return $item;
	}


	/**
	 * @inheritdoc
	 */
	public function getAdditionalFieldsForSubForm(\ILIAS\GlobalScreen\Identification\IdentificationInterface $identification): array {
		global $DIC;
		$url = $DIC->ui()->factory()->input()->field()->numeric($this->getFieldTranslation());
		if (isset($this->links[$identification->serialize()][self::F_ACTION]) && is_numeric($this->links[$identification->serialize()][self::F_ACTION])) {
			$url = $url->withValue((int)$this->links[$identification->serialize()][self::F_ACTION]);
		}

		return [self::F_ACTION => $url];
	}


	/**
	 * @inheritDoc
	 */
	protected function getFieldTranslation(): string {
		global $DIC;

		return $DIC->language()->txt("field_ref_id");
	}


	/**
	 * @inheritDoc
	 */
	protected function getFieldInfoTranslation(): string {
		global $DIC;

		return $DIC->language()->txt("field_ref_id_info");
	}
}
