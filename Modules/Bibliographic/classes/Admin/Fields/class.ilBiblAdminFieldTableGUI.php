<?php
/**
 * Class ilBiblAdminFieldTableGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblAdminFieldTableGUI extends ilTable2GUI {

	const TBL_ID = 'tbl_bibl_fields';
	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $dic;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var string
	 */
	protected $data_type;
	/**
	 * @var ilBiblAdminFieldGUI
	 */
	protected $parent_obj;
	/**
	 * @var ilObjBibliographicAdmin
	 */
	protected $il_obj_bibliographic_admin;
	/**
	 * @var array
	 */
	protected $filter = [];
	/**
	 * @var \ilBiblFieldFactoryInterface
	 */
	protected $field_factory;
	/**
	 * @var \ilBiblTypeFactoryInterface
	 */
	protected $type_factory;
	/**
	 * @var int
	 */
	protected $position_index = 1;


	/**
	 * ilLocationDataTableGUI constructor.
	 *
	 * @param ilBiblAdminFieldGUI $a_parent_obj
	 * @param string              $a_parent_cmd
	 */
	function __construct($a_parent_obj, $a_parent_cmd, ilObjBibliographicAdmin $il_obj_bibliographic_admin, ilBiblTypeInterface $data_type, ilBiblFieldFactoryInterface $field_factory, ilBiblTypeFactoryInterface $type_factory) {
		global $DIC;
		$this->dic = $DIC;
		$this->parent_obj = $a_parent_obj;
		$this->ctrl = $this->dic->ctrl();
		$this->tpl = $this->dic['tpl'];
		$this->data_type = $data_type;
		$this->field_factory = $field_factory;
		$this->type_factory = $type_factory;

		$this->setId(self::TBL_ID);
		$this->setPrefix(self::TBL_ID);
		$this->setFormName(self::TBL_ID);
		$this->ctrl->saveParameter($a_parent_obj, $this->getNavParameter());
		$this->il_obj_bibliographic_admin = $il_obj_bibliographic_admin;

		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->parent_obj = $a_parent_obj;
		$this->setRowTemplate('tpl.bibl_administration_fields_list_row.html', 'Modules/Bibliographic');

		switch ($this->data_type) {
			case ilBiblTypeFactoryInterface::DATA_TYPE_RIS:
				$this->setFormAction($this->ctrl->getFormActionByClass(ilBiblAdminRisFieldGUI::class));
				break;
			case ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX:
				$this->setFormAction($this->ctrl->getFormActionByClass(ilBiblAdminBibtexFieldGUI::class));
				break;
		}

		$this->setExternalSorting(true);

		$this->setDefaultOrderField("identifier");
		$this->setDefaultOrderDirection("asc");
		$this->setExternalSegmentation(true);
		$this->setEnableHeader(true);

		$this->initColumns();
		if ($this->data_type == ilBiblTypeFactoryInterface::DATA_TYPE_RIS) {
			$this->addCommandButton(ilBiblAdminRisFieldGUI::CMD_SAVE, $this->dic->language()
			                                                                    ->txt("save"));
		} elseif ($this->data_type == ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX) {
			$this->addCommandButton(ilBiblAdminBibtexFieldGUI::CMD_SAVE, $this->dic->language()
			                                                                       ->txt("save"));
		}
		$this->addFilterItems();
		$this->parseData();
	}


	protected function initColumns() {
		$this->addColumn($this->dic->language()->txt('position'), 'position');
		$this->addColumn($this->dic->language()->txt('identifier'), 'identifier');
		$this->addColumn($this->dic->language()->txt('translation'), 'translation');
		$this->addColumn($this->dic->language()->txt('standard'), 'is_standard_field');
		$this->addColumn($this->dic->language()->txt('actions'), '', '150px');
	}


	protected function addFilterItems() {
		$field = new ilTextInputGUI($this->dic->language()->txt('identifier'), 'identifier');
		$this->addAndReadFilterItem($field);
	}


	/**
	 * @param $field
	 */
	protected function addAndReadFilterItem(ilFormPropertyGUI $field) {
		$this->addFilterItem($field);
		$field->readFromSession();
		if ($field instanceof ilCheckboxInputGUI) {
			$this->filter[$field->getPostVar()] = $field->getChecked();
		} else {
			$this->filter[$field->getPostVar()] = $field->getValue();
		}
	}


	/**
	 * Fills table rows with content from $a_set.
	 *
	 * @param array $a_set
	 */
	public function fillRow($a_set) {
		$field = $this->field_factory->findById($a_set['id']);

		$this->tpl->setCurrentBlock("POSITION");
		$this->tpl->setVariable('POSITION_VALUE', $field->getPosition() ? $field->getPosition() : $this->position_index);
		$this->tpl->setVariable('POSITION_NAME', "row_values[" . $a_set['id'] . "][position]");
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("IDENTIFIER");
		$this->tpl->setVariable('IDENTIFIER_VALUE', $field->getIdentifier());

		$this->tpl->setVariable('IDENTIFIER_NAME', "row_values[" . $field->getId()
		                                           . "][identifier]");
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("TRANSLATION");
		//TODO change static content
		$this->tpl->setVariable('VAL_TRANSLATION', 'TRANSLATION');

		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("STANDARD");
		if ($field->getIsStandardField()) {
			$this->tpl->setVariable('IS_STANDARD_VALUE', $this->dic->language()->txt("standard"));
		} else {
			$this->tpl->setVariable('IS_STANDARD_VALUE', $this->dic->language()->txt("custom"));
		}

		$this->tpl->setVariable('IS_STANDARD_NAME', "row_values[" . $field->getId()
		                                            . "][is_standard_field]");
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("DATA_TYPE");
		$this->tpl->setVariable('DATA_TYPE_NAME', "row_values[" . $field->getId() . "][data_type]");
		$this->tpl->setVariable('DATA_TYPE_VALUE', $this->data_type->getId());
		$this->tpl->parseCurrentBlock();
		$this->addActionMenu($field);

		$this->position_index ++;
	}


	/**
	 * @param \ilBiblFieldInterface $field
	 */
	protected function addActionMenu(ilBiblFieldInterface $field) {
		$selectionList = new ilAdvancedSelectionListGUI();
		$selectionList->setListTitle($this->lng->txt("actions"));
		$selectionList->setId($field->getIdentifier());

		$this->ctrl->setParameterByClass(ilBiblAdminFieldGUI::class, ilBiblAdminFieldGUI::FIELD_IDENTIFIER, $field->getId());
		switch ($this->data_type->getId()) {
			case ilBiblTypeFactoryInterface::DATA_TYPE_RIS:
				$this->ctrl->setParameterByClass(ilBiblAdminRisFieldGUI::class, ilBiblAdminRisFieldGUI::FIELD_IDENTIFIER, $field->getId());
				break;
			case ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX:
				$this->ctrl->setParameterByClass(ilBiblAdminBibtexFieldGUI::class, ilBiblAdminBibtexFieldGUI::FIELD_IDENTIFIER, $field->getId());
				break;
		}
		$this->ctrl->setParameterByClass(ilBiblTranslationGUI::class, ilBiblAdminRisFieldGUI::FIELD_IDENTIFIER, $field->getId());

		$txt = $this->dic->language()->txt("translate");
		$selectionList->addItem($txt, "", $this->dic->ctrl()
		                                            ->getLinkTargetByClass(ilBiblTranslationGUI::class, ilBiblTranslationGUI::CMD_DEFAULT));

		$this->tpl->setVariable('VAL_ACTIONS', $selectionList->getHTML());
	}


	protected function parseData() {
		$this->determineOffsetAndOrder();
		$this->determineLimit();

		foreach ($this->filter as $filter_key => $filter_value) {
			switch ($filter_key) {
				case 'identifier':
					// $collection->where(array( $filter_key => '%' . $filter_value . '%' ), 'LIKE');
					break;
			}
		}

		$data = $this->field_factory->filterAllFieldsForTypeAsArray($this->data_type);

		$this->setData($data);
	}
}