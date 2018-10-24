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
	protected $links = [];
	/**
	 * @inheritDoc
	 */
	const F_ACTION = 'url';


	/**
	 * ilMMAbstractBaseTypeHandlerAction constructor.
	 */
	public function __construct() {
		$this->links = ilMMTypeActionStorage::getArray('identification', 'action');
	}


	abstract public function matchesForType(): string;


	/**
	 * @inheritdoc
	 */
	abstract public function enrichItem(isItem $item): isItem;


	/**
	 * @inheritdoc
	 */
	public function saveFormFields(\ILIAS\GlobalScreen\Identification\IdentificationInterface $identification, array $data): bool {
		ilMMTypeActionStorage::find($identification->serialize())->setAction($data[self::F_ACTION])->update();

		return true;
	}


	/**
	 * @inheritdoc
	 */
	public function getAdditionalFieldsForSubForm(\ILIAS\GlobalScreen\Identification\IdentificationInterface $identification): array {
		global $DIC;
		$url = $DIC->ui()->factory()->input()->field()->text($this->getFieldTranslation())->withRequired(true);
		if (isset($this->links[$identification->serialize()])) {
			$url = $url->withValue($this->links[$identification->serialize()]);
		}

		return [self::F_ACTION => $url];
	}


	/**
	 * @return string
	 */
	protected abstract function getFieldTranslation(): string;
}
