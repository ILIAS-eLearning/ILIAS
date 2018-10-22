<?php

use ILIAS\GlobalScreen\Collector\MainMenu\TypeHandler;
use ILIAS\GlobalScreen\MainMenu\isItem;

/**
 * Class ilMMAbstractBaseTypeHandlerAction
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilMMAbstractBaseTypeHandlerAction implements TypeHandler {

	/**
	 * @var array
	 */
	protected $links
		= [
			"ilMMCustomProvider|5bcd9cab9b9c2" => "https://www.google.com",
		];
	/**
	 * @inheritDoc
	 */
	const F_URL = 'url';


	abstract public function matchesForType(): string;


	/**
	 * @inheritdoc
	 */
	abstract public function enrichItem(isItem $item): isItem;


	/**
	 * @inheritdoc
	 */
	public function getAdditionalFieldsForSubForm(): array {
		global $DIC;
		$fields[self::F_URL] = $DIC->ui()->factory()->input()->field()->text("URL")->withRequired(true);

		return $fields;
	}


	/**
	 * @inheritdoc
	 */
	public function saveFormFields(\ILIAS\GlobalScreen\Identification\IdentificationInterface $identification, array $data): bool {
		ilMMTypeActionStorage::installDB();

		ilMMTypeActionStorage::find($identification->serialize())->setAction($data[self::F_URL])->update();

		return true;
	}
}
