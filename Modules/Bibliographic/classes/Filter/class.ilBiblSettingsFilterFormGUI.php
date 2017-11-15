<?php
/**
 * Class ilBiblSettingsFilterFormGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblSettingsFilterFormGUI extends ilPropertyFormGUI {

	/**
	 * @var  ilBiblField
	 */
	protected $il_bibl_field;
	/**
	 * @var  ilBiblFilter
	 */
	protected $il_bibl_filter;
	/**
	 * @var ilBiblSettingsFilterGUI
	 */
	protected $il_bibliographic_settings_filter_gui;
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


	public function __construct($parent_gui, ilBiblField $ilBiblField) {
		global $DIC;

		$this->dic = $DIC;
		$this->il_bibl_field = $ilBiblField;
		$this->tpl = $this->dic['tpl'];
		$this->ctrl = $this->dic->ctrl();
		$this->il_bibliographic_settings_filter_gui = $parent_gui;
		$this->il_obj_bibliographic = ilObjectFactory::getInstanceByRefId($_GET['ref_id']);

		$this->dic->language()->loadLanguageModule('bibl');

		parent::__construct();

		$this->initForm();
	}


	public function initForm() {
		$this->setTarget('_top');
		$this->setTitle($this->dic->language()->txt('filter_create'));

		$options = ilBiblField::getAvailableFieldsForObjId($this->il_obj_bibliographic->getId());
		$select_options = [];
		foreach ($options as $field_name) {
			$select_options[$field_name] = $this->dic->language()->txt($this->il_obj_bibliographic->getFiletype() . '_default_'
				. $field_name); // TODO Übersetzungsdienst nutzen
		}

		/*
		 * 1) loop through options array
		 * 2) get id for every value of options, e.q. id of cite
		 * 3)
		 */

		$si = new ilSelectInputGUI($this->dic->language()->txt("please_choose_field"), "identifier");
		$si->setOptions($select_options);
		$si->setRequired(true);
		$this->addItem($si);

		$options = [
			1 => ilBiblFilter::FILTER_TYPE_TEXT_INPUT . " - Text Input",
			2 => ilBiblFilter::FILTER_TYPE_SELECT_INPUT . " - Select Input",
			3 => ilBiblFilter::FILTER_TYPE_MULTI_SELECT_INPUT . " - Multi Select Input"
		];
		$si = new ilSelectInputGUI($this->dic->language()->txt("please_choose_filter_type"), "filter_type");
		$si->setOptions($options);
		$si->setRequired(true);
		$this->addItem($si);

		$this->addCommandButton(ilBiblSettingsFilterGUI::CMD_UPDATE, $this->dic->language()->txt('save'));
		$this->addCommandButton(ilBiblSettingsFilterGUI::CMD_CANCEL, $this->dic->language()->txt("cancel"));

		$this->ctrl->setParameter($this->il_bibliographic_settings_filter_gui, ilBiblSettingsFilterGUI::FIELD_IDENTIFIER, $_GET['field_identifier']);
		$this->setFormAction($this->ctrl->getFormAction($this->il_bibliographic_settings_filter_gui));
	}

	public function fillForm() {
		$array = array(
			'field' => $this->il_bibl_field->getIdentifier(),
			'filter_type' => $this->il_bibl_filter->getFilterType()
		);
		$this->setValuesByArray($array);
	}


	protected function fillObject() {
		if (!$this->checkInput()) {
			return false;
		}

		//
		// Abfüllen der Daten aus via $this->>getInput()['field']...
		//
		if ($this->il_bibl_field->getId()) {
			$this->il_bibl_field->update();
		} else {
			$this->il_bibl_field->create();
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
}