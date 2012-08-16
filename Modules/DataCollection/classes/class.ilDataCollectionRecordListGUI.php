<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Modules/DataCollection/classes/class.ilDataCollectionRecord.php");
include_once("./Modules/DataCollection/classes/class.ilDataCollectionTable.php");
include_once("./Modules/DataCollection/classes/class.ilDataCollectionDatatype.php");

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

        require_once('./Modules/DataCollection/classes/class.ilDataCollectionRecordListTableGUI.php');
        $list = new ilDataCollectionRecordListTableGUI($this, $ilCtrl->getCmd(), $this->table_obj);
		$tpl->getStandardTemplate();
        $tpl->setContent($list->getHTML());
    }

	public function exportExcel(){
		global $ilCtrl;
		require_once('./Modules/DataCollection/classes/class.ilDataCollectionRecordListTableGUI.php');
		$list = new ilDataCollectionRecordListTableGUI($this, $ilCtrl->getCmd(), $this->table_obj);
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


	public function sendFile(){
		global $ilAccess;
		//need read access to receive file
		if($ilAccess->checkAccess("read", "", $this->parent_obj->ref_id)){
			echo "here";
		}
	}
}

?>