<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Modules/DataCollection/classes/class.ilDataCollectionRecord.php");
include_once("./Modules/DataCollection/classes/class.ilDataCollectionTable.php");
include_once("./Modules/DataCollection/classes/class.ilDataCollectionDatatype.php");
require_once('./Modules/DataCollection/classes/class.ilDataCollectionRecordListTableGUI.php');


/**
 * Class ilDataCollectionRecordListGUI
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Marcel Raimann <mr@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id:
 *
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
        $this->main_table_id = $a_parent_obj->object->getMainTableId();
        $this->table_id = $table_id;
        if($this->table_id == Null)
            $this->table_id = $_GET["table_id"];
        $this->obj_id = $a_parent_obj->obj_id;
		$this->parent_obj = $a_parent_obj;
        $this->table_obj = new ilDataCollectionTable($table_id);

        return;
    }

    /**
     * execute command
     */
    function executeCommand()
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

        //$ilTabs->setTabActive("id_records");

        // Show tables
        require_once("./Modules/DataCollection/classes/class.ilDataCollectionTable.php");
        $arrTables = ilDataCollectionTable::getAll($this->obj_id);
        foreach($arrTables as $table)
        {
            $options[$table['id']] = $table['title'];
        }
        include_once './Services/Form/classes/class.ilSelectInputGUI.php';
        $table_selection = new ilSelectInputGUI(
            '',
            'table_id'
        );
        $table_selection->setOptions($options);
        $table_selection->setValue($this->table_id);
        $ilToolbar->setFormAction($ilCtrl->getFormActionByClass("ilDataCollectionRecordListGUI", "doTableSwitch"));
        $ilToolbar->addInputItem($table_selection);
        $ilToolbar->addFormButton($lng->txt('change'),'doTableSwitch');

		$ilToolbar->addFormButton($lng->txt('dcl_export_table_excel'), "exportExcel");

        $list = new ilDataCollectionRecordListTableGUI($this, $ilCtrl->getCmd(), $this->table_obj);
		$tpl->getStandardTemplate();
        $tpl->setContent($list->getHTML());
    }

	public function exportExcel(){
		global $ilCtrl;
		require_once('./Modules/DataCollection/classes/class.ilDataCollectionRecordListTableGUI.php');
		$list = new ilDataCollectionRecordListTableGUI($this, $ilCtrl->getCmd(), $this->table_obj);
		$table = new ilDataCollectionTable($this->table_id);
		$list->setData($table->getRecords());
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

	function applyFilter(){
		global $ilCtrl;
		$table =  new ilDataCollectionRecordListTableGUI($this, $ilCtrl->getCmd(), $this->table_obj);
		$table->writeFilterToSession();
		$this->listRecords();
	}

	function resetFilter(){
		global $ilCtrl;
		$table =  new ilDataCollectionRecordListTableGUI($this, $ilCtrl->getCmd(), $this->table_obj);
		$table->resetFilter();
		$this->listRecords();
	}


	public function sendFile(){
		global $ilAccess;
		//need read access to receive file
		if($ilAccess->checkAccess("read", "", $this->parent_obj->ref_id)){
			$rec_id = $_GET['record_id'];
			$record = new ilDataCollectionRecord($rec_id);
			$field_id = $_GET['field_id'];
			$file_obj = new ilObjFile($record->getRecordFieldValue($field_id), false);
			if(!$this->recordBelongsToCollection($record, $this->parent_obj->ref_id))
				return;
			ilUtil::deliverFile($file_obj->getFile(), 	$file_obj->getTitle());
		}
	}

	private function recordBelongsToCollection(ilDataCollectionRecord $record){
		$table = $record->getTable();
		$obj_id = $this->parent_obj->object->getId();
		$obj_id_rec = $table->getCollectionObject()->getId();
		return $obj_id == $obj_id_rec;
	}
}

?>