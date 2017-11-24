<?php

use ILIAS\Modules\OrgUnit\ARHelper\DIC;

/**
 * Class ilBiblTranslationGUI
 *
 * @author Benjamin Seglias   <bs@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblTranslationGUI {

	use DIC;
	const CMD_DEFAULT = 'index';
	/**
	 * @var \ilBiblTranslationFactoryInterface
	 */
	protected $translation_factory;
	/**
	 * @var \ilBiblFieldFactoryInterface
	 */
	protected $field_factory;


	/**
	 * ilBiblTranslationGUI constructor.
	 *
	 * @param \ilBiblTranslationFactoryInterface $translation_factory
	 * @param \ilBiblFieldFactoryInterface       $field_factory
	 */
	public function __construct(\ilBiblTranslationFactoryInterface $translation_factory, \ilBiblFieldFactoryInterface $field_factory) {
		$this->translation_factory = $translation_factory;
		$this->field_factory = $field_factory;
	}


	public function executeCommand() {
		switch ($this->ctrl()->getNextClass()) {
			default:
				$cmd = $this->ctrl()->getCmd();
				$this->{$cmd}();
		}
	}


	protected function index() {

	}
}