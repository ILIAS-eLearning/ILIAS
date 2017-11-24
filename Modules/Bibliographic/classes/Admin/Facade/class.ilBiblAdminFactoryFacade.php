<?php

/**
 * Interface ilBiblAdminFactoryFacade
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblAdminFactoryFacade implements ilBiblAdminFactoryFacadeInterface {

	/**
	 * @var \ilBiblTranslationFactory
	 */
	protected $translation_factory;
	/**
	 * @var \ilBiblFieldFactory
	 */
	protected $field_factory;
	/**
	 * @var \ilBiblTypeInterface|\ilBibTex|\ilRis
	 */
	protected $type;
	/**
	 * @var \ilBiblTypeFactory
	 */
	protected $type_factory;
	/**
	 * @var \ilObjBibliographicAdmin
	 */
	protected $ilias_object;


	/**
	 * ilBiblAdminFactoryFacade constructor.
	 *
	 * @param \ilObjBibliographicAdmin $ilObjBibliographicAdmin
	 */
	public function __construct(ilObjBibliographicAdmin $ilObjBibliographicAdmin, $type_id) {
		$this->ilias_object = $ilObjBibliographicAdmin;
		$this->type_factory = new ilBiblTypeFactory();
		$this->type = $this->type_factory->getInstanceForType($type_id);
		$this->field_factory = new ilBiblFieldFactory($this->type);
		$this->translation_factory = new ilBiblTranslationFactory($this->field_factory);
	}


	/**
	 * @inheritDoc
	 */
	public function typeFactory() {
		return $this->type_factory;
	}


	/**
	 * @inheritDoc
	 */
	public function type() {
		return $this->type;
	}


	/**
	 * @inheritDoc
	 */
	public function translationFactory() {
		return $this->translation_factory;
	}


	/**
	 * @inheritDoc
	 */
	public function fieldFactory() {
		return $this->field_factory;
	}


	/**
	 * @inheritDoc
	 */
	public function iliasObject() {
		return $this->ilias_object;
	}
}
