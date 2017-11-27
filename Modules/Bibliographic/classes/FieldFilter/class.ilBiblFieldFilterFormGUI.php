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
	 * @var \ilBiblFieldFilterFactoryInterface
	 */
	private $bibl_filter_factory;
	/**
	 * @var  \ilBiblFieldFilter
	 */
	protected $filter;
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
	 * @var ilTabsGUI
	 */
	protected $tabs;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $dic;
	/**
	 * @var \ilBiblFieldFilterFactoryInterface
	 */
	private $filter_factory;
	/**
	 * @var \ilBiblFieldFactoryInterface
	 */
	private $field_factory;

	/**
	 * ilBiblFieldFilterFormGUI constructor.
	 *
	 * @param \ilBiblFieldFilterGUI              $parent_gui
	 * @param \ilBiblFieldFilter                 $il_bibl_field
	 * @param \ilBiblFieldFilterFactoryInterface $filter_factory
	 * @param \ilBiblFieldFactoryInterface       $field_factory
	 */
	public function __construct(ilBiblFieldFilterGUI $parent_gui, ilBiblFieldFilter $il_bibl_field, ilBiblFieldFilterFactoryInterface $filter_factory, ilBiblFieldFactoryInterface $field_factory) {
		global $DIC;
		$this->filter_factory = $filter_factory;
		$this->field_factory = $field_factory;
		$this->dic = $DIC;
		$this->filter = $il_bibl_field;
		$this->ctrl = $this->dic->ctrl();
		$this->parent_gui = $parent_gui;
		$this->il_obj_bibliographic = ilObjectFactory::getInstanceByRefId($_GET['ref_id']);
		$this->bibl_filter_factory = new ilBiblFieldFilterFactory();
		$this->dic->language()->loadLanguageModule('bibl');
		$this->dic->ctrl()
		          ->saveParameterByClass(ilBiblFieldFilterGUI::class, ilBiblFieldFilterGUI::FILTER_ID);
		$this->tabs = $this->dic->tabs();
		$this->tabs->clearTargets();
		$this->tabs->setBackTarget($this->dic->language()->txt("back"), $this->ctrl->getLinkTargetByClass(ilBiblFieldFilterGUI::class, ilBiblFieldFilterGUI::CMD_STANDARD));

		parent::__construct();
		$this->initForm();
	}


	public function initForm() {
		$this->setTarget('_top');

		$available_fields_for_object = $this->field_factory->getAvailableFieldsForObjId($this->il_obj_bibliographic->getId());

		$edited_filter = $this->filter_factory->findById($this->dic->http()->request()->getQueryParams()[ilBiblFieldFilterGUI::FILTER_ID]);

		//show only the fields as options which don't have already a filter
		$options = [];
		foreach($available_fields_for_object as $available_field) {
			if(empty($this->filter_factory->findByFieldId($available_field->getId())) || (!empty($edited_filter) && $edited_filter->getFieldId() == $available_field->getId()) ) {
				if(!empty($edited_filter) && $edited_filter->getFieldId() == $available_field->getId()) {
					array_unshift($options, $available_field);
					continue;
				}
				$options[] = $available_field;
			}
		}
		//$options = $this->field_factory->getAvailableFieldsForObjId($this->il_obj_bibliographic->getId());


		$select_options = [];
		foreach ($options as $field_name) {
			$select_options[$field_name->getId()] = $this->dic->language()
			                                                  ->txt($this->il_obj_bibliographic->getFileTypeAsString()
			                                                        . '_default_'
			                                                        . $field_name->getIdentifier()); // TODO Übersetzungsdienst nutzen
		}

		$si = new ilSelectInputGUI($this->dic->language()->txt("field"), self::F_FIELD_ID);
		$si->setInfo($this->dic->language()->txt("filter_field_info"));
		$si->setOptions($select_options);
		$si->setRequired(true);
		$this->addItem($si);

		$options = [
			ilBiblFieldFilterInterface::FILTER_TYPE_TEXT_INPUT         => $this->dic->language()
			                                                                        ->txt("filter_type_"
			                                                                              . ilBiblFieldFilterInterface::FILTER_TYPE_TEXT_INPUT),
			ilBiblFieldFilterInterface::FILTER_TYPE_SELECT_INPUT       => $this->dic->language()
			                                                                        ->txt("filter_type_"
			                                                                              . ilBiblFieldFilterInterface::FILTER_TYPE_SELECT_INPUT),
			ilBiblFieldFilterInterface::FILTER_TYPE_MULTI_SELECT_INPUT => $this->dic->language()
			                                                                        ->txt("filter_type_"
			                                                                              . ilBiblFieldFilterInterface::FILTER_TYPE_MULTI_SELECT_INPUT),
		];
		$si = new ilSelectInputGUI($this->dic->language()->txt("filter_type"), self::F_FILTER_TYPE);
		$si->setInfo($this->dic->language()->txt("filter_type_info"));
		$si->setOptions($options);
		$si->setRequired(true);
		$this->addItem($si);

		$this->setTitle($this->dic->language()->txt('filter_form_title'));

		$this->initButtons();

		$this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
	}


	public function fillForm() {
		$array = array(
			self::F_FIELD_ID    => $this->filter->getFieldId(),
			self::F_FILTER_TYPE => $this->filter->getFilterType(),
		);
		$this->setValuesByArray($array);
	}


	protected function fillObject() {
		if (!$this->checkInput()) {
			return false;
		}

		$this->filter->setFieldId($this->getInput(self::F_FIELD_ID));
		$this->filter->setFilterType($this->getInput(self::F_FILTER_TYPE));

		if ($this->filter->getId()) {
			$this->filter->update();
		} else {
			$this->filter->create();
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


	protected function initButtons() {
		if ($this->filter->getId()) {
			$this->addCommandButton(ilBiblFieldFilterGUI::CMD_UPDATE, $this->dic->language()
			                                                                    ->txt('save'));
			$this->addCommandButton(ilBiblFieldFilterGUI::CMD_CANCEL, $this->dic->language()
			                                                                    ->txt("cancel"));
		} else {
			$this->addCommandButton(ilBiblFieldFilterGUI::CMD_CREATE, $this->dic->language()
			                                                                    ->txt('create'));
			$this->addCommandButton(ilBiblFieldFilterGUI::CMD_CANCEL, $this->dic->language()
			                                                                    ->txt("cancel"));
		}
	}
}