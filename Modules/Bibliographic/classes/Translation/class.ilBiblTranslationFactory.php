<?php

/**
 * Class ilBiblTranslationFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblTranslationFactory implements ilBiblTranslationFactoryInterface {

	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $dic;
	/**
	 * @var \ilBiblFieldFactoryInterface
	 */
	protected $field_factory;


	/**
	 * ilBiblTranslationFactory constructor.
	 *
	 * @param \ilBiblFieldFactoryInterface $field_factory
	 */
	public function __construct(ilBiblFieldFactoryInterface $field_factory) {
		global $DIC;
		$this->dic = $DIC;
		$this->field_factory = $field_factory;
	}


	/**
	 * @param \ilBiblFieldInterface $field
	 *
	 * @return string
	 */
	public function translate(ilBiblFieldInterface $field) {
		return $this->translateInCore($field);
	}


	/**
	 * @return \ilBiblFieldFactoryInterface
	 */
	public function getFieldFactory() {
		return $this->field_factory;
	}


	/**
	 * @param \ilBiblFieldInterface $field
	 *
	 * @return string
	 */
	private function translateInCore(ilBiblFieldInterface $field) {
		$prefix = $this->getFieldFactory()->getType()->getStringRepresentation();
		$middle = "default";
		$identifier = $field->getIdentifier();

		return $this->dic->language()->txt(implode("_", [ $prefix, $middle, $identifier ]));
	}


	/**
	 * @inheritDoc
	 */
	public function translationExistsForField(ilBiblFieldInterface $field) {
		return !is_null($this->getInstanceForFieldAndUsersLanguage($field));
	}


	/**
	 * @inheritDoc
	 */
	public function getInstanceForFieldAndUsersLanguage(ilBiblFieldInterface $field) {
		global $DIC;

		return null;

		return ilBiblTranslation::where([
			'field_id'     => $field->getId(),
			"language_key" => $DIC->user()->getCurrentLanguage(),
		])->get();
	}
}
