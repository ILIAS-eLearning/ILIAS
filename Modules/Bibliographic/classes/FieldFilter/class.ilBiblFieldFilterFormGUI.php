<?php
/**
 * Class ilBiblFieldFilterFormGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblFieldFilterFormGUI extends ilPropertyFormGUI {

	const F_FIELD_ID = "field_id";
	const F_FILTER_TYPE = "filter_type";
	/**
	 * @var  ilBiblFieldFilter
	 */
	protected $il_bibl_filter;
	/**
	 * @var ilBiblFieldFilterGUI
	 */
	protected $parent_gui;
	/**
	 * @var ilObjBibliographic
	 */
	protected $il_obj_bibliographic;
	/**
	 * @var  ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $dic;


	/**
	 * ilBiblFieldFilterFormGUI constructor.
	 *
	 * @param \ilBiblFieldFilterGUI $parent_gui
	 * @param \ilBiblFieldFilter    $il_bibl_field
	 */
	public function __construct(ilBiblFieldFilterGUI $parent_gui, ilBiblFieldFilter $il_bibl_field) {
		global $DIC;

		$this->dic = $DIC;
		$this->il_bibl_filter = $il_bibl_field;
		$this->ctrl = $this->dic->ctrl();
		$this->parent_gui = $parent_gui;
		$this->il_obj_bibliographic = ilObjectFactory::getInstanceByRefId($_GET['ref_id']);

		$this->dic->language()->loadLanguageModule('bibl');

		parent::__construct();

		$this->initForm();
	}


	public function initForm() {
		$this->setTarget('_top');

		$options = ilBiblField::getAvailableFieldsForObjId($this->il_obj_bibliographic->getId());
		$select_options = [];
		foreach ($options as $field_name) {
			$select_options[$field_name] = $this->dic->language()
			                                         ->txt($this->il_obj_bibliographic->getFiletype()
			                                               . '_default_'
			                                               . $field_name); // TODO Übersetzungsdienst nutzen
		}

		$si = new ilSelectInputGUI($this->dic->language()
		                                     ->txt("filter_identifier"), self::F_FIELD_ID);
		$si->setOptions($select_options);
		$si->setRequired(true);
		$this->addItem($si);

		$options = [
			ilBiblFieldFilter::FILTER_TYPE_TEXT_INPUT         => $this->dic->language()
			                                                               ->txt("filter_type_"
			                                                                     . ilBiblFieldFilter::FILTER_TYPE_TEXT_INPUT),
			ilBiblFieldFilter::FILTER_TYPE_SELECT_INPUT       => $this->dic->language()
			                                                               ->txt("filter_type_"
			                                                                     . ilBiblFieldFilter::FILTER_TYPE_SELECT_INPUT),
			ilBiblFieldFilter::FILTER_TYPE_MULTI_SELECT_INPUT => $this->dic->language()
			                                                               ->txt("filter_type_"
			                                                                     . ilBiblFieldFilter::FILTER_TYPE_MULTI_SELECT_INPUT),
		];
		$si = new ilSelectInputGUI($this->dic->language()
		                                     ->txt("please_choose_filter_type"), self::F_FILTER_TYPE);
		$si->setOptions($options);
		$si->setRequired(true);
		$this->addItem($si);

		$this->initButtonsAndTitle();

		$this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
	}


	public function fillForm() {
		$array = array(
			self::F_FIELD_ID    => $this->il_bibl_filter->getFieldId(),
			self::F_FILTER_TYPE => $this->il_bibl_filter->getFilterType(),
		);
		$this->setValuesByArray($array);
	}


	protected function fillObject() {
		if (!$this->checkInput()) {
			return false;
		}

		$this->il_bibl_filter->setFieldId($this->getInput(self::F_FIELD_ID));
		$this->il_bibl_filter->setFilterType($this->getInput(self::F_FILTER_TYPE));

		if ($this->il_bibl_filter->getId()) {
			$this->il_bibl_filter->update();
		} else {
			$this->il_bibl_filter->create();
		}

		return true;
	}


	/**
	 * @return bool|string
	 */
	public function saveObject() {
		if (!$this->fillObject()) {
			return false;
		}

		return true;
	}


	protected function initButtonsAndTitle() {
		if ($this->il_bibl_filter->getId()) {
			$this->setTitle($this->dic->language()->txt('filter_form_title'));

			$this->addCommandButton(ilBiblFieldFilterGUI::CMD_UPDATE, $this->dic->language()
			                                                                    ->txt('create'));
			$this->addCommandButton(ilBiblFieldFilterGUI::CMD_CANCEL, $this->dic->language()
			                                                                    ->txt("cancel"));
		} else {
			$this->setTitle($this->dic->language()->txt('filter_form_title'));

			$this->addCommandButton(ilBiblFieldFilterGUI::CMD_CREATE, $this->dic->language()
			                                                                    ->txt('save'));
			$this->addCommandButton(ilBiblFieldFilterGUI::CMD_CANCEL, $this->dic->language()
			                                                                    ->txt("cancel"));
		}
	}
}