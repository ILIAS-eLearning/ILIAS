<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/DataCollection/classes/class.ilDataCollectionRecord.php");
include_once("./Modules/DataCollection/classes/class.ilDataCollectionTable.php");
include_once("./Modules/DataCollection/classes/class.ilDataCollectionDatatype.php");
require_once('./Modules/DataCollection/classes/class.ilDataCollectionRecordListTableGUI.php');

/**
 * Class ilDataCollectionField
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Marcel Raimann <mr@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDataCollectionRecordListGUI
{
	private $table_obj;
	/**
	 * Constructor
	 *
	 * @param	object	$a_parent_obj
	 * @param	int $table_id
	 */
	public function  __construct(ilObjDataCollectionGUI $a_parent_obj, $table_id)
	{
        global $ilCtrl;
        $this->main_table_id = $a_parent_obj->object->getMainTableId();
        $this->table_id = $table_id;
        if($this->table_id == NULL)
            $this->table_id = $_GET["table_id"];
        $this->obj_id = $a_parent_obj->obj_id;
        $this->parent_obj = $a_parent_obj;
        $this->table_obj = ilDataCollectionCache::getTableCache($table_id);
        $ilCtrl->setParameterByClass("ildatacollectionrecordeditgui", "table_id", $table_id);

		return;
	}

	/**
	 * execute command
	 */
	public function executeCommand()
	{
		global $tpl, $ilCtrl;

		$cmd = $ilCtrl->getCmd();

		switch($cmd)
		{
			default:
				$this->$cmd();
				break;
		}
	}

	/**
	 * List Records
	 *
	 *
	 */
	public function listRecords()
	{
		global $ilTabs, $tpl, $lng, $ilCtrl, $ilToolbar;

        		// Show tables
		require_once("./Modules/DataCollection/classes/class.ilDataCollectionTable.php");
		if(ilObjDataCollection::_hasWriteAccess($this->parent_obj->ref_id))
			$tables = $this->parent_obj->object->getTables();
		else
			$tables = $this->parent_obj->object->getVisibleTables();


		foreach($tables as $table)
		{
			$options[$table->getId()] = $table->getTitle();
		}

        if(count($options) > 0){
            include_once './Services/Form/classes/class.ilSelectInputGUI.php';
            $table_selection = new ilSelectInputGUI('', 'table_id');
            $table_selection->setOptions($options);
            $table_selection->setValue($this->table_id);

            $ilToolbar->setFormAction($ilCtrl->getFormActionByClass("ilDataCollectionRecordListGUI", "doTableSwitch"));
            $ilToolbar->addText($lng->txt("dcl_table"));
            $ilToolbar->addInputItem($table_selection);
            $ilToolbar->addFormButton($lng->txt('change'),'doTableSwitch');
            $ilToolbar->addSeparator();
        }
        if(($this->table_obj->getExportEnabled() || $this->table_obj->hasPermissionToFields($this->parent_obj->ref_id)) && count($this->table_obj->getRecordFields()))
            $ilToolbar->addButton($lng->txt('dcl_export_table_excel'), $ilCtrl->getFormActionByClass("ildatacollectionrecordlistgui", "exportExcel"));

        if($_GET['table_id'])
            $table_id = $_GET['table_id'];
        else
            $table_id = $this->main_table_id;
        if($this->table_obj->hasPermissionToAddRecord($this->parent_obj->ref_id) && $this->table_obj->hasCustomFields())
            $ilToolbar->addButton($lng->txt("dcl_add_new_record"), $ilCtrl->getFormActionByClass("ildatacollectionrecordeditgui", "create"));

        // requested not to implement this way...
        //$tpl->addJavaScript("Modules/DataCollection/js/fastTableSwitcher.js");

        if(count($this->table_obj->getRecordFields()) == 0){
            ilUtil::sendInfo($lng->txt("dcl_no_fields_yet")." ".($this->table_obj->hasPermissionToFields($this->parent_obj->ref_id)?$lng->txt("dcl_create_fields"):""));
        }

		$list = new ilDataCollectionRecordListTableGUI($this, $ilCtrl->getCmd(), $this->table_obj);
		$tpl->getStandardTemplate();

        $tpl->setPermanentLink("dcl", $this->parent_obj->ref_id);
        $tpl->setContent($list->getHTML());
	}
	
	/*
	 * exportExcel
	 */
	public function exportExcel()
	{
        global $ilCtrl, $lng;

        if(!($this->table_obj->getExportEnabled() || $this->table_obj->hasPermissionToFields($this->parent_obj->ref_id))){
            echo $lng->txt("access_denied");
            exit;
        }

        require_once('./Modules/DataCollection/classes/class.ilDataCollectionRecordListTableGUI.php');
        $list = new ilDataCollectionRecordListTableGUI($this, $ilCtrl->getCmd(), $this->table_obj);
        $table = ilDataCollectionCache::getTableCache($this->table_id);
//        $list->setData($table->getRecords());
        $list->setExternalSorting(true);
        $list->exportData(ilTable2GUI::EXPORT_EXCEL, true);
        $this->listRecords();
    }


	/**
	 * doTableSwitch
	 */
	public function doTableSwitch()
	{
		global $ilCtrl;

		$ilCtrl->setParameterByClass("ilObjDataCollectionGUI", "table_id", $_POST['table_id']);
		$ilCtrl->redirect($this, "listRecords");
	}
	
	/*
	 * applyFilter
	 */
	public function applyFilter()
	{
		global $ilCtrl;
		
		$table =  new ilDataCollectionRecordListTableGUI($this, $ilCtrl->getCmd(), $this->table_obj);
		$table->writeFilterToSession();
		$this->listRecords();
	}
	
	/*
	 * resetFilter
	 */
	public function resetFilter()
	{
		global $ilCtrl;
		
		$table =  new ilDataCollectionRecordListTableGUI($this, $ilCtrl->getCmd(), $this->table_obj);
		$table->resetFilter();
		$this->listRecords();
	}

	/*
	 * sendFile
	 */
	public function sendFile()
	{
		global $ilAccess;
		//need read access to receive file
		if($ilAccess->checkAccess("read", "", $this->parent_obj->ref_id))
		{
			$rec_id = $_GET['record_id'];
			$record = ilDataCollectionCache::getRecordCache($rec_id);
			$field_id = $_GET['field_id'];
			$file_obj = new ilObjFile($record->getRecordFieldValue($field_id), false);
			if(!$this->recordBelongsToCollection($record, $this->parent_obj->ref_id))
			{
				return;
			}
			ilUtil::deliverFile($file_obj->getFile(), 	$file_obj->getTitle());
		}
	}
	
	/*
	 * recordBelongsToCollection
	 */
	private function recordBelongsToCollection(ilDataCollectionRecord $record)
	{
		$table = $record->getTable();
		$obj_id = $this->parent_obj->object->getId();
		$obj_id_rec = $table->getCollectionObject()->getId();
		
		return $obj_id == $obj_id_rec;
	}
}

?>