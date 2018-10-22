<?php

use ILIAS\GlobalScreen\Collector\MainMenu\TypeHandler;
use ILIAS\GlobalScreen\MainMenu\isItem;

/**
 * Class ilMMTypeHandlerLink
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTypeHandlerLink implements TypeHandler {

	/**
	 * @var array
	 */
	private $links
		= [
			"ilMMCustomProvider|5bcd9cab9b9c2" => "https://www.google.com",
		];


	/**
	 * @inheritDoc
	 */
	public function matchesForType(): string {
		return \ILIAS\GlobalScreen\MainMenu\Item\Link::class;
	}


	/**
	 * @inheritdoc
	 */
	public function enrichItem(isItem $item): isItem {
		if ($item instanceof \ILIAS\GlobalScreen\MainMenu\Item\Link && isset($this->links[$item->getProviderIdentification()->serialize()])) {
			$item = $item->withAction((string)$this->links[$item->getProviderIdentification()->serialize()]);
		}

		return $item;
	}


	/**
	 * @inheritdoc
	 */
	public function getAdditionalFieldsForSubForm(): array {
		global $DIC;
		$fields['url'] = $DIC->ui()->factory()->input()->field()->text("URL")->withRequired(true);

		return $fields;
	}


	/**
	 * @inheritdoc
	 */
	public function saveFormFields(\ILIAS\GlobalScreen\Identification\IdentificationInterface $identification, array $data): bool {
		return true;
	}
}
