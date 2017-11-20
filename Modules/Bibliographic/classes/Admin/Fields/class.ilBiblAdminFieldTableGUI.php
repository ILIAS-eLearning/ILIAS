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
	 * @var ilBiblSettingsFilterGUI
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
	 * ilLocationDataTableGUI constructor.
	 *
	 * @param ilBiblAdminFieldGUI $a_parent_obj
	 * @param string      $a_parent_cmd
	 */
	function __construct($a_parent_obj, $a_parent_cmd, ilObjBibliographicAdmin $il_obj_bibliographic_admin, $data_type) {
		global $DIC;
		$this->dic = $DIC;
		$this->parent_obj = $a_parent_obj;
		$this->ctrl = $this->dic->ctrl();
		$this->tpl = $this->dic['tpl'];
		$this->data_type = $data_type;

		$this->setId(self::TBL_ID);
		$this->setPrefix(self::TBL_ID);
		$this->setFormName(self::TBL_ID);
		$this->ctrl->saveParameter($a_parent_obj, $this->getNavParameter());
		$this->il_obj_bibliographic_admin = $il_obj_bibliographic_admin;

		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->parent_obj = $a_parent_obj;
		$this->setRowTemplate('tpl.bibl_administration_fields_list_row.html', 'Modules/Bibliographic');

		$this->setFormAction($this->ctrl->getFormActionByClass(ilBiblAdminFieldGUI::class));
		$this->setExternalSorting(true);

		$this->setDefaultOrderField("identifier");
		$this->setDefaultOrderDirection("asc");
		$this->setExternalSegmentation(true);
		$this->setEnableHeader(true);

		$this->initColumns();
		$this->addCommandButton(ilBiblAdminFieldGUI::CMD_SAVE, $this->dic->language()->txt("save"));
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

		$is_bibl_field = false;

		if(ilBiblField::where(array('id' =>$a_set['id']))->hasSets()) {
			$is_bibl_field = true;
		}
		$this->tpl->setCurrentBlock("POSITION");
		if($is_bibl_field) {
			$this->tpl->setVariable('POSITION_VALUE', $a_set['position']);
		} else {
			$this->tpl->setVariable('POSITION_VALUE', '');
		}
		$this->tpl->setVariable('POSITION_NAME', "row_values[". $a_set['id'] ."][position]");
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("IDENTIFIER");
		if($is_bibl_field) {
			$this->tpl->setVariable('IDENTIFIER_VALUE', $a_set['identifier']);
		} else {
			$this->tpl->setVariable('IDENTIFIER_VALUE', $a_set['name']);
		}
		$this->tpl->setVariable('IDENTIFIER_NAME', "row_values[". $a_set['id'] ."][identifier]");
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("TRANSLATION");
		if($is_bibl_field) {
			//TODO change static content
			$this->tpl->setVariable('VAL_TRANSLATION', 'TRANSLATION');
		} else {
			$this->tpl->setVariable('VAL_TRANSLATION', 'TRANSLATION');
		}
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("STANDARD");
		$file_parts = pathinfo($a_set['filename']);
		if($is_bibl_field) {
			$this->tpl->setVariable('IS_STANDARD_VALUE', $this->dic->language()->txt("standard"));
		} else {
			if($file_parts['extension'] == "bib") {
				if(ilBibTexInterface::isStandardField($a_set['name'])) {
					$this->tpl->setVariable('IS_STANDARD_VALUE', $this->dic->language()->txt("standard"));
				} else {
					$this->tpl->setVariable('IS_STANDARD_VALUE', $this->dic->language()->txt("custom"));
				}
			} elseif($file_parts['extension'] == "ris") {
				if(ilRisInterface::isStandardField($a_set['name'])) {
					$this->tpl->setVariable('IS_STANDARD_VALUE', $this->dic->language()->txt("standard"));
				} else {
					$this->tpl->setVariable('IS_STANDARD_VALUE', $this->dic->language()->txt("custom"));
				}
			}
		}
		$this->tpl->setVariable('IS_STANDARD_NAME', "row_values[". $a_set['id'] ."][is_standard_field]");
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("DATA_TYPE");
		$this->tpl->setVariable('DATA_TYPE_NAME', "row_values[". $a_set['id'] ."][data_type]");
		$this->tpl->setVariable('DATA_TYPE_VALUE', $this->data_type);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("IS_BIBL_FIELD");
		$this->tpl->setVariable('IS_BIBL_FIELD_NAME', "row_values[". $a_set['id'] ."][is_bibl_field]");
		$this->tpl->setVariable('IS_BIBL_FIELD_VALUE', $is_bibl_field);
		$this->tpl->parseCurrentBlock();

		$this->addActionMenu($a_set['id'], $is_bibl_field);
	}

	/**
	 * @param integer $id
	 * @param boolean $is_bibl_field
	 */
	protected function addActionMenu($id, $is_bibl_field) {
		$current_selection_list = new ilAdvancedSelectionListGUI();
		$current_selection_list->setListTitle($this->lng->txt("actions"));
		$current_selection_list->setId($id);
		$current_selection_list->addItem($this->dic->language()->txt("translate"), "", $this->dic->ctrl()->getLinkTargetByClass(ilBiblAdminFieldTranslateGUI::class, ilBiblAdminFieldTranslateGUI::CMD_TRANSLATE));
		$this->ctrl->setParameterByClass(ilBiblAdminFieldDeleteGUI::class, 'is_bibl_field', $is_bibl_field);
		$this->ctrl->setParameterByClass(ilBiblAdminFieldDeleteGUI::class, ilBiblAdminFieldGUI::FIELD_IDENTIFIER, $id);
		$current_selection_list->addItem($this->dic->language()->txt("delete"), "", $this->dic->ctrl()->getLinkTargetByClass(ilBiblAdminFieldDeleteGUI::class, ilBiblAdminFieldDeleteGUI::CMD_STANDARD));
		$this->tpl->setVariable('VAL_ACTIONS', $current_selection_list->getHTML());
	}

	protected function convertStringDataTypeToInt($string_data_type) {
		switch ($string_data_type) {
			case "ris":
				return ilBiblField::DATA_TYPE_RIS;
				break;
			case "bib":
				return ilBiblField::DATA_TYPE_BIBTEX;
				break;
		}
	}

	protected function parseData() {
		$this->determineOffsetAndOrder();
		$this->determineLimit();

		$collection = ilBiblField::getCollection();
		if(!empty($this->data_type)) {
			if(!is_int(intval($this->data_type))) {
				$this->data_type = $this->convertStringDataTypeToInt($this->data_type);
			}
			$collection->where(array( 'data_type' => $this->data_type ));
		} else {
			if(!is_int($this->data_type)) {
				$this->data_type = $this->convertStringDataTypeToInt($this->data_type);
			}
			$collection->where(array( 'data_type' => $this->data_type ));
		}

		$sorting_column = $this->getOrderField() ? $this->getOrderField() : 'identifier';
		$offset = $this->getOffset() ? $this->getOffset() : 0;

		$sorting_direction = $this->getOrderDirection();
		$num = $this->getLimit();

		$collection->orderBy($sorting_column, $sorting_direction);
		$collection->limit($offset, $num);

		$all_attribute_names_and_file_names = ilBiblField::getAllAttributeNamesByDataType($this->data_type);

		foreach ($this->filter as $filter_key => $filter_value) {
			switch ($filter_key) {
				case 'identifier':
					$collection->where(array( $filter_key => '%' . $filter_value . '%' ), 'LIKE');
					$all_attribute_names_and_file_names = ilBiblField::getAllAttributeNamesByIdentifier($filter_value);
					break;
			}
		}
		$data_array = array_merge($collection->getArray(), $all_attribute_names_and_file_names);

		$this->setData($data_array);
	}
}