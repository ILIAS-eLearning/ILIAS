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
	function __construct($a_parent_obj, $a_parent_cmd, ilObjBibliographicAdmin $il_obj_bibliographic_admin) {
		global $DIC;
		$this->dic = $DIC;
		$this->parent_obj = $a_parent_obj;
		$this->ctrl = $this->dic->ctrl();
		$this->tpl = $this->dic['tpl'];

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
		/**
		 * @var ilBiblField $ilField
		 */
		if(is_array($a_set) && array_key_exists('id', $a_set)) {
			$ilField = ilBiblField::find($a_set['id']);
		}
		$this->tpl->setCurrentBlock("POSITION");
		if(isset($ilField)) {
			$this->tpl->setVariable('VAL_POSITION', $a_set['position']);
		} else {
			$this->tpl->setVariable('VAL_POSITION', '');
		}
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("IDENTIFIER");
		if(isset($ilField)) {
			$this->tpl->setVariable('VAL_IDENTIFIER', $a_set['identifier']);
		} else {
			$this->tpl->setVariable('VAL_IDENTIFIER', $a_set['name']);
		}
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("TRANSLATION");
		if(isset($ilField)) {
			//TODO change static content
			$this->tpl->setVariable('VAL_TRANSLATION', 'TRANSLATION');
		} else {
			$this->tpl->setVariable('VAL_TRANSLATION', 'TRANSLATION');
		}
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("STANDARD");
		if(isset($ilField)) {
			$this->tpl->setVariable('VAL_STANDARD', $a_set['is_standard_field']);
		} else {
			$file_parts = pathinfo($a_set['filename']);
			if($file_parts['extension'] == "bib") {
				$this->tpl->setVariable('VAL_STANDARD', ilBibTex::isStandardField($a_set['name']));
			} elseif($file_parts['extension'] == "ris") {
				$this->tpl->setVariable('VAL_STANDARD', ilRis::isStandardField($a_set['name']));
			}

		}
		$this->tpl->parseCurrentBlock();
		if(isset($ilField)) {
			$this->addActionMenu($ilField);
		}
	}

	/**
	 * @param ilBiblField $ilField
	 */
	protected function addActionMenu(ilBiblField $ilField) {
		$current_selection_list = new ilAdvancedSelectionListGUI();
		$current_selection_list->setListTitle($this->lng->txt("actions"));
		$current_selection_list->setId($ilField->getId());
		$current_selection_list->addItem($this->dic->language()->txt("translate"), "", $this->dic->ctrl()->getLinkTargetByClass(ilBiblAdminFieldTranslateGUI::class, ilBiblAdminFieldTranslateGUI::CMD_TRANSLATE));
		$current_selection_list->addItem($this->dic->language()->txt("delete"), "", $this->dic->ctrl()->getLinkTargetByClass(ilBiblAdminFieldDeleteGUI::class, ilBiblAdminFieldDeleteGUI::CMD_STANDARD));
		$this->tpl->setVariable('VAL_ACTIONS', $current_selection_list->getHTML());
	}

	protected function parseData() {
		$this->determineOffsetAndOrder();
		$this->determineLimit();

		$collection = ilBiblField::getCollection();
		//$collection->where(array( 'object_id' => $this->il_obj_bibliographic_admin->getId() ));

		$sorting_column = $this->getOrderField() ? $this->getOrderField() : 'identifier';
		$offset = $this->getOffset() ? $this->getOffset() : 0;

		$sorting_direction = $this->getOrderDirection();
		$num = $this->getLimit();

		$collection->orderBy($sorting_column, $sorting_direction);
		$collection->limit($offset, $num);

		$all_attribute_names_and_file_names = ilBiblField::getAllAttributeNamesAndFileNames();

		foreach ($this->filter as $filter_key => $filter_value) {
			switch ($filter_key) {
				case 'identifier':
					$collection->where(array( $filter_key => '%' . $filter_value . '%' ), 'LIKE');
					break;
			}
		}
		$collection_array = $collection->getArray();
		$data_array = array_merge($collection->getArray(), $all_attribute_names_and_file_names);

		//$this->setData($collection->getArray());
		$this->setData($data_array);
	}
}