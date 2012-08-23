<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
* Class ilDataCollectionRecordListTableGUI
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
* @version $Id: 
*
* @extends ilTable2GUI
*
*/


class ilDataCollectionRecordListTableGUI  extends ilTable2GUI
{

    private $table;

	public function  __construct(ilDataCollectionRecordListGUI $a_parent_obj, $a_parent_cmd, ilDataCollectionTable $table)
	{
		global $lng, $tpl, $ilCtrl;

		parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->table = $table;

		include_once("class.ilDataCollectionDatatype.php");
		include_once("class.ilObjDataCollectionFile.php");
		
	 	$this->parent_obj = $a_parent_obj;
		$this->setFormName('record_list');
		
		$this->setRowTemplate("tpl.record_list_row.html", "Modules/DataCollection");

        foreach($this->table->getVisibleFields() as $field)
        {
            $this->addColumn($field->getTitle());
        }
        $this->setId("dcl_record_list");
        
        $this->addColumn($lng->txt("actions"), "", 	 "30px");


		$this->setTopCommands(true);
		$this->setEnableHeader(true);
		$this->setDisableFilterHiding(true);
		$this->setShowRowsSelector(false);
		$this->setShowTemplates(false);
		$this->setEnableHeader(true);
		$this->setEnableTitle(true);
		$this->setDefaultOrderDirection("asc");
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, "applyFilter"));
		$this->initFilter();
		$this->setExportFormats(array(self::EXPORT_EXCEL));
		$this->setData($table->getRecordsByFilter($this->filter));

        //leave these two
        $this->setExternalSegmentation(true);
        $this->setExternalSorting(true);

		if($this->table->hasPermissionToAddRecord($this->parent_obj->parent_obj->ref_id))
		{
			$ilCtrl->setParameterByClass("ildatacollectionrecordeditgui","table_id", $this->parent_obj->table_id);
			$this->addHeaderCommand($ilCtrl->getLinkTargetByClass("ildatacollectionrecordeditgui", "create"),$lng->txt("dcl_add_new_record"));
		}
		
		$ilCtrl->setParameterByClass("ildatacollectionrecordlistgui","table_id", $this->parent_obj->table_id);
		$this->addHeaderCommand($ilCtrl->getLinkTargetByClass("ildatacollectionrecordlistgui", "exportExcel"),$lng->txt("dcl_export_table_excel"));
	}
	
	/*
	 * fillHeaderExcel
	 */
	public function fillHeaderExcel($worksheet, &$row)
	{
		$col = 0;
		foreach($this->table->getFields() as $field)
		{
			$worksheet->writeString($row, $col, $field->getTitle());
			$col++;
		}
		$row++;
	}
	
	
	/*
	 * fillRowExcel
	 */
	public function fillRowExcel($worksheet, &$row, ilDataCollectionRecord $record)
	{
		$col = 0;
		foreach($this->table->getFields() as $field)
		{
			$worksheet->writeString($row, $col, $record->getRecordFieldValue($field->getId()));
			$col++;
		}
	}
	
	/**
	 * fill row 
	 *
	 * @access public
	 * @param $a_set
	 */
	public function fillRow(ilDataCollectionRecord $record)
	{
		global $ilUser, $ilCtrl, $tpl, $lng, $ilAccess;

		$this->tpl->setVariable("TITLE", $this->table->getTitle());

		foreach($this->table->getVisibleFields() as $field)
		{
			$this->tpl->setCurrentBlock("field");
			$this->tpl->setVariable("CONTENT", $record->getRecordFieldHTML($field->getId()));

			$this->tpl->parseCurrentBlock();
		}
		
		
		$ilCtrl->setParameterByClass("ildatacollectionfieldeditgui", "record_id", $record->getId());
		$ilCtrl->setParameterByClass("ildatacollectionrecordviewgui", "record_id", $record->getId());
		
		include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		
		$alist = new ilAdvancedSelectionListGUI();
		$alist->setId($record->getId());
		$alist->setListTitle($lng->txt("actions"));
		
		$alist->addItem($lng->txt('view'), 'view', $ilCtrl->getLinkTargetByClass("ildatacollectionrecordviewgui", 'renderRecord'));

		$ilCtrl->setParameterByClass("ildatacollectionrecordeditgui","record_id", $record->getId());
		if($record->hasPermissionToEdit($this->parent_obj->parent_obj->ref_id))
			$alist->addItem($lng->txt('edit'), 'edit', $ilCtrl->getLinkTargetByClass("ildatacollectionrecordeditgui", 'edit'));
		if($record->hasPermissionToDelete($this->parent_obj->parent_obj->ref_id))
			$alist->addItem($lng->txt('delete'), 'delete', $ilCtrl->getLinkTargetByClass("ildatacollectionrecordeditgui", 'confirmDelete'));

		$this->tpl->setVariable("ACTIONS", $alist->getHTML());

		return true;
    }

	function initFilter()
	{

		foreach($this->table->getFilterableFields() as $field){
			$input = ilDataCollectionDatatype::addFilterInputFieldToTable($field, $this);
			$input->readFromSession();
			$this->filter["filter_".$field->getId()] = $input->getValue();
		}
	}
}

?>