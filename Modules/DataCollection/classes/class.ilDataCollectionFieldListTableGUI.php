<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once 'class.ilDataCollectionCache.php';

/**
 * Class ilDataCollectionFieldListTableGUI
 *
 * @author       Martin Studer <ms@studer-raimann.ch>
 * @author       Marcel Raimann <mr@studer-raimann.ch>
 * @author       Fabian Schmid <fs@studer-raimann.ch>
 * @author       Oskar Truffer <ot@studer-raimann.ch>
 * @version      $Id:
 *
 * @extends      ilTable2GUI
 * @ilCtrl_Calls ilDateTime
 */
class ilDataCollectionFieldListTableGUI extends ilTable2GUI {

	/**
	 * @param ilDataCollectionFieldListGUI $a_parent_obj
	 * @param string                       $a_parent_cmd
	 * @param string                       $table_id
	 */
	public function  __construct(ilDataCollectionFieldListGUI $a_parent_obj, $a_parent_cmd, $table_id) {
		global $lng, $ilCtrl;

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->parent_obj = $a_parent_obj;
		$this->table = ilDataCollectionCache::getTableCache($table_id);

		$this->setId('dcl_field_list');
		$this->addColumn('', '', '1', true);
		$this->addColumn($lng->txt('dcl_order'), NULL, '30px');
		$this->addColumn($lng->txt('dcl_title'), NULL, 'auto');
		$this->addColumn($lng->txt('dcl_visible'), NULL, '30px');
		$this->addColumn($lng->txt('dcl_filter'), NULL, '30px');
		$this->addColumn($lng->txt('dcl_locked'), NULL, '30px');
		$this->addColumn($lng->txt('dcl_in_export'), NULL, '30px');
		$this->addColumn($lng->txt('dcl_description'), NULL, 'auto');
		$this->addColumn($lng->txt('dcl_field_datatype'), NULL, 'auto');
		$this->addColumn($lng->txt('dcl_required'), NULL, 'auto');
		$this->addColumn($lng->txt('dcl_unique'), NULL, 'auto');
		$this->addColumn($lng->txt('actions'), NULL, '30px');
		// Only add mutli command for custom fields
		if (count($this->table->getRecordFields())) {
			$this->setSelectAllCheckbox('dcl_field_ids[]');
			$this->addMultiCommand('confirmDeleteFields', $lng->txt('dcl_delete_fields'));
		}

		$ilCtrl->setParameterByClass('ildatacollectionfieldeditgui', 'table_id', $this->parent_obj->table_id);
		$ilCtrl->setParameterByClass('ildatacollectionfieldlistgui', 'table_id', $this->parent_obj->table_id);

		$this->setFormAction($ilCtrl->getFormActionByClass('ildatacollectionfieldlistgui'));
		$this->addCommandButton('save', $lng->txt('dcl_save'));

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setFormName('field_list');

		//those two are important as we get our data as objects not as arrays.
		$this->setExternalSegmentation(true);
		$this->setExternalSorting(true);

		$this->setTopCommands(true);
		$this->setEnableHeader(true);
		$this->setShowRowsSelector(false);
		$this->setShowTemplates(false);
		$this->setEnableHeader(true);
		$this->setEnableTitle(true);
		$this->setDefaultOrderDirection('asc');

		$this->setData($this->table->getFields());
		require_once('./Modules/DataCollection/classes/class.ilDataCollectionDatatype.php'); //wird dies benötigt?
		$this->setTitle($lng->txt('dcl_table_list_fields'));
		$this->setRowTemplate('tpl.field_list_row.html', 'Modules/DataCollection');
		$this->setStyle('table', $this->getStyle('table') . ' ' . 'dcl_record_list');
	}


	/**
	 * @param ilDataCollectionField $a_set
	 */
	public function fillRow(ilDataCollectionField $a_set) {
		global $lng, $ilCtrl;

		if (!$a_set->isStandardField()) {
			$this->tpl->setVariable('FIELD_ID', $a_set->getId());
		}

		$this->tpl->setVariable('NAME', 'order[' . $a_set->getId() . ']');
		$this->tpl->setVariable('VALUE', $this->order);

		$this->tpl->setVariable('CHECKBOX_VISIBLE', 'visible[' . $a_set->getId() . ']');
		if ($a_set->isVisible()) {
			$this->tpl->setVariable('CHECKBOX_VISIBLE_CHECKED', 'checked');
		}

		/* Don't enable setting filter for MOB fields or reference fields that reference a MOB field */
		$show_filter = true;
		$show_exportable = true;
		if ($a_set->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_MOB
			|| $a_set->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_FILE
		) {
			$show_filter = false;
		}
		if ($a_set->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_REFERENCE) {
			$ref_field = ilDataCollectionCache::getFieldCache((int)$a_set->getFieldRef());
			if ($ref_field
				&& ($ref_field->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_MOB
					|| $ref_field->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_FILE)
			) {
				$show_filter = false;
			}
		}
		if ($a_set->getId() == 'comments') {
			$show_filter = false;
			$show_exportable = false;
		}
		if ($show_filter) {
			$this->tpl->setVariable('CHECKBOX_FILTERABLE', 'filterable[' . $a_set->getId() . ']');
			if ($a_set->isFilterable()) {
				$this->tpl->setVariable('CHECKBOX_FILTERABLE_CHECKED', 'checked');
			}
		} else {
			$this->tpl->setVariable('NO_FILTER', '');
		}

		if ($show_exportable) {
			$this->tpl->setVariable('CHECKBOX_EXPORTABLE', 'exportable[' . $a_set->getId() . ']');
			if ($a_set->getExportable()) {
				$this->tpl->setVariable('CHECKBOX_EXPORTABLE_CHECKED', 'checked');
			}
		} else {
			$this->tpl->setVariable('NO_FILTER_EXPORTABLE', '');
		}

		if (!$a_set->isStandardField()) {
			$this->tpl->setVariable('CHECKBOX_NAME_LOCKED', 'locked[' . $a_set->getId() . ']');
			if ($a_set->getLocked()) {
				$this->tpl->setVariable('CHECKBOX_CHECKED_LOCKED', 'checked');
			}
		} else {
			$this->tpl->setVariable('NOT_LOCKED', '');
		}

		$this->order = $this->order + 10;
		$this->tpl->setVariable('ORDER_NAME', 'order[' . $a_set->getId() . ']');
		$this->tpl->setVariable('ORDER_VALUE', $this->order);

		$this->tpl->setVariable('TITLE', $a_set->getTitle());
		$this->tpl->setVariable('DESCRIPTION', $a_set->getDescription());
		$this->tpl->setVariable('DATATYPE', $a_set->getDatatypeTitle());

		if (!$a_set->isStandardField()) {
			switch ($a_set->getRequired()) {
				case 0:
					$required = ilUtil::getImagePath('icon_not_ok.svg');
					break;
				case 1:
					$required = ilUtil::getImagePath('icon_ok.svg');
					break;
			}
			switch ($a_set->isUnique()) {
				case 0:
					$uniq = ilUtil::getImagePath('icon_not_ok.svg');
					break;
				case 1:
					$uniq = ilUtil::getImagePath('icon_ok.svg');
					break;
			}
			$this->tpl->setVariable('REQUIRED', $required);
			$this->tpl->setVariable('UNIQUE', $uniq);
		} else {
			$this->tpl->setVariable('NO_REQUIRED', '');
			$this->tpl->setVariable('NO_UNIQUE', '');
		}

		$ilCtrl->setParameterByClass('ildatacollectionfieldeditgui', 'field_id', $a_set->getId());

		if (!$a_set->isStandardField()) {
			include_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');
			$alist = new ilAdvancedSelectionListGUI();
			$alist->setId($a_set->getId());
			$alist->setListTitle($lng->txt('actions'));

			if ($this->table->hasPermissionToFields($this->parent_obj->parent_obj->ref_id)) {
				$alist->addItem($lng->txt('edit'), 'edit', $ilCtrl->getLinkTargetByClass('ildatacollectionfieldeditgui', 'edit'));
				$alist->addItem($lng->txt('delete'), 'delete', $ilCtrl->getLinkTargetByClass('ildatacollectionfieldeditgui', 'confirmDelete'));
			}

			$this->tpl->setVariable('ACTIONS', $alist->getHTML());
		}
	}
}

?>