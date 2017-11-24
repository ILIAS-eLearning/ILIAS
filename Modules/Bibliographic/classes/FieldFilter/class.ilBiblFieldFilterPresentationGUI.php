<?php

/**
 * Class ilBiblFieldFilterPresentationGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblFieldFilterPresentationGUI {

	/**
	 * @var \ilBiblTranslationFactoryInterface
	 */
	protected $translation_factory;
	/**
	 * @var \ilBiblFieldFactoryInterface
	 */
	protected $field_factory;
	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $dic;
	/**
	 * @var \ilBiblFieldFilterInterface
	 */
	protected $filter;


	/**
	 * ilBiblFieldFilterPresentationGUI constructor.
	 *
	 * @param \ilBiblFieldFilterInterface  $filter
	 * @param \ilBiblFieldFactoryInterface $field_factory
	 */
	public function __construct(\ilBiblFieldFilterInterface $filter, ilBiblFieldFactoryInterface $field_factory, ilBiblTranslationFactoryInterface $translation_factory) {
		global $DIC;
		$this->field_factory = $field_factory;
		$this->translation_factory = $translation_factory;
		$this->dic = $DIC;
		$this->dic->language()->loadLanguageModule('bibl');
		$this->filter = $filter;
	}


	/**
	 * @return ilTableFilterItem
	 */
	public function getFilterItem() {
		$field = $this->field_factory->findById($this->getFilter()->getFieldId());
		$translated = $this->translation_factory->translate($field);

		$ilBiblFieldFilter = $this->getFilter();

		switch ($ilBiblFieldFilter->getFilterType()) {
			case ilBiblFieldFilterInterface::FILTER_TYPE_TEXT_INPUT:
				$filter = new ilTextInputGUI($translated, $field->getIdentifier());
				break;
			case ilBiblFieldFilterInterface::FILTER_TYPE_SELECT_INPUT:
				$filter = new ilSelectInputGUI($translated, $field->getIdentifier());
				break;
			case ilBiblFieldFilterInterface::FILTER_TYPE_MULTI_SELECT_INPUT:
				$filter = new ilMultiSelectInputGUI($translated, $field->getIdentifier());
				break;
			default:
				throw new LogicException('no filter type used');
				break;
		}

		return $filter;
	}


	/**
	 * @return \ilBiblFieldFilterInterface
	 */
	public function getFilter() {
		return $this->filter;
	}


	/**
	 * @param \ilBiblFieldFilterInterface $filter
	 */
	public function setFilter($filter) {
		$this->filter = $filter;
	}
}
