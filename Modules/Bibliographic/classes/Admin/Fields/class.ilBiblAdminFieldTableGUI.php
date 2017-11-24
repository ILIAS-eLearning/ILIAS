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
	 * ilLocationDataTableGUI constructor.
	 *
	 * @param ilBiblAdminFieldGUI $a_parent_obj
	 * @param string      $a_parent_cmd
	 */
	function __construct($a_parent_obj, $a_parent_cmd, ilObjBibliographicAdmin $il_obj_bibliographic_admin, $data_type, ilBiblFieldFactoryInterface $field_factory, ilBiblTypeFactoryInterface $type_factory) {
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

		if($this->data_type == ilBiblTypeFactoryInterface::DATA_TYPE_RIS) {
			$this->setFormAction($this->ctrl->getFormActionByClass(ilBiblAdminRisFieldGUI::class));
		} elseif($this->data_type == ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX) {
			$this->setFormAction($this->ctrl->getFormActionByClass(ilBiblAdminBibtexFieldGUI::class));
		}

		$this->setExternalSorting(true);

		$this->setDefaultOrderField("identifier");
		$this->setDefaultOrderDirection("asc");
		$this->setExternalSegmentation(true);
		$this->setEnableHeader(true);

		$this->initColumns();
		if($this->data_type == ilBiblTypeFactoryInterface::DATA_TYPE_RIS) {
			$this->addCommandButton(ilBiblAdminRisFieldGUI::CMD_SAVE, $this->dic->language()->txt("save"));
		} elseif($this->data_type == ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX) {
			$this->addCommandButton(ilBiblAdminBibtexFieldGUI::CMD_SAVE, $this->dic->language()->txt("save"));
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
	 * @param array    $a_set
	 */
	public function fillRow($a_set) {

		$this->tpl->setCurrentBlock("POSITION");
		$this->tpl->setVariable('POSITION_VALUE', $a_set['position']);
		$this->tpl->setVariable('POSITION_NAME', "row_values[". $a_set['id'] ."][position]");
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("IDENTIFIER");
		$this->tpl->setVariable('IDENTIFIER_VALUE', $a_set['identifier']);

		$this->tpl->setVariable('IDENTIFIER_NAME', "row_values[". $a_set['id'] ."][identifier]");
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("TRANSLATION");
		//TODO change static content
		$this->tpl->setVariable('VAL_TRANSLATION', 'TRANSLATION');

		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("STANDARD");
		if($a_set['is_standard_field']) {
			$this->tpl->setVariable('IS_STANDARD_VALUE', $this->dic->language()->txt("standard"));
		} else {
			$this->tpl->setVariable('IS_STANDARD_VALUE', $this->dic->language()->txt("custom"));
		}


		$this->tpl->setVariable('IS_STANDARD_NAME', "row_values[". $a_set['id'] ."][is_standard_field]");
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("DATA_TYPE");
		$this->tpl->setVariable('DATA_TYPE_NAME', "row_values[". $a_set['id'] ."][data_type]");
		$this->tpl->setVariable('DATA_TYPE_VALUE', $this->data_type);
		$this->tpl->parseCurrentBlock();
		$this->addActionMenu($a_set['id']);
	}

	/**
	 * @param integer $id
	 * @param boolean $is_bibl_field
	 * @param boolean $data_type
	 */
	protected function addActionMenu($id) {
		$current_selection_list = new ilAdvancedSelectionListGUI();
		$current_selection_list->setListTitle($this->lng->txt("actions"));
		$current_selection_list->setId($id);
		$current_selection_list->addItem($this->dic->language()->txt("translate"), "", $this->dic->ctrl()->getLinkTargetByClass(ilBiblAdminFieldTranslateGUI::class, ilBiblAdminFieldTranslateGUI::CMD_TRANSLATE));
		$this->ctrl->setParameterByClass(ilBiblAdminFieldGUI::class, ilBiblAdminFieldGUI::FIELD_IDENTIFIER, $id);
		if($this->data_type == ilBiblTypeFactoryInterface::DATA_TYPE_RIS) {
			$this->ctrl->setParameterByClass(ilBiblAdminRisFieldGUI::class, ilBiblAdminRisFieldGUI::FIELD_IDENTIFIER, $id);
/*			$this->ctrl->setParameterByClass(ilBiblAdminRisFieldGUI::class, ilBiblAdminRisFieldGUI::DATA_TYPE, ilBiblTypeFactoryInterface::DATA_TYPE_RIS);
			$current_selection_list->addItem($this->dic->language()->txt("delete"), "", $this->dic->ctrl()->getLinkTargetByClass(ilBiblAdminRisFieldGUI::class, ilBiblAdminRisFieldGUI::CMD_DELETE));*/
		} elseif($this->data_type == ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX) {
			$this->ctrl->setParameterByClass(ilBiblAdminBibtexFieldGUI::class, ilBiblAdminBibtexFieldGUI::FIELD_IDENTIFIER, $id);
/*			$this->ctrl->setParameterByClass(ilBiblAdminBibtexFieldGUI::class, ilBiblAdminBibtexFieldGUI::DATA_TYPE, ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX);
			$current_selection_list->addItem($this->dic->language()->txt("delete"), "", $this->dic->ctrl()->getLinkTargetByClass(ilBiblAdminBibtexFieldGUI::class, ilBiblAdminBibtexFieldGUI::CMD_DELETE));*/
		}
		$this->tpl->setVariable('VAL_ACTIONS', $current_selection_list->getHTML());
	}

/*	protected function convertStringDataTypeToInt($string_data_type) {
		switch ($string_data_type) {
			case "ris":
				return ilBiblField::DATA_TYPE_RIS;
				break;
			case "bib":
				return ilBiblField::DATA_TYPE_BIBTEX;
				break;
		}
	}*/

	protected function parseData() {
		$this->determineOffsetAndOrder();
		$this->determineLimit();

		$collection = ilBiblField::getCollection();
		if(!empty($this->data_type)) {
			if(!is_int(intval($this->data_type))) {
				$this->data_type = $this->type_factory->convertFileEndingToDataType($this->data_type);
			}
		} else {
			$this->data_type = $this->type_factory->convertFileEndingToDataType($this->data_type);
		}

		$collection->where(array( 'data_type' => $this->data_type ));

		$sorting_column = $this->getOrderField() ? $this->getOrderField() : 'identifier';
		$offset = $this->getOffset() ? $this->getOffset() : 0;

		$sorting_direction = $this->getOrderDirection();
		$num = $this->getLimit();

		$collection->orderBy($sorting_column, $sorting_direction);
		$collection->limit($offset, $num);

		//$all_attribute_names_and_file_names = $this->field_factory->getAllAttributeNamesByDataType($this->data_type);

		foreach ($this->filter as $filter_key => $filter_value) {
			switch ($filter_key) {
				case 'identifier':
					$collection->where(array( $filter_key => '%' . $filter_value . '%' ), 'LIKE');
					//$all_attribute_names_and_file_names = $this->field_factory->getAllAttributeNamesByIdentifier($filter_value);
					break;
			}
		}
		//$data_array = array_merge($collection->getArray(), $all_attribute_names_and_file_names);

		//$this->setData($data_array);
		$this->setData($collection->getArray());
	}
}