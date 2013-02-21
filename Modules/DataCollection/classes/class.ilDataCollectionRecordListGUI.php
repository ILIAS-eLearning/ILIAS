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

    protected $max_imports = 100;

    protected $supported_import_datatypes = array(ilDataCollectionDatatype::INPUTFORMAT_BOOLEAN, ilDataCollectionDatatype::INPUTFORMAT_NUMBER, ilDataCollectionDatatype::INPUTFORMAT_REFERENCE, ilDataCollectionDatatype::INPUTFORMAT_TEXT);

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
        if($this->table_obj->getExportEnabled() || $this->table_obj->hasPermissionToFields($this->parent_obj->ref_id))
            $ilToolbar->addFormButton($lng->txt('dcl_export_table_excel'), "exportExcel");

        if($_GET['table_id'])
            $table_id = $_GET['table_id'];
        else
            $table_id = $this->main_table_id;
        if($this->table_obj->hasPermissionToAddRecord($this->parent_obj->ref_id) && $this->table_obj->hasCustomFields()){
            $ilToolbar->addButton($lng->txt("dcl_import_records .xls"), $ilCtrl->getFormActionByClass("ildatacollectionrecordlistgui", "showImportExcel"));
            $ilToolbar->addButton($lng->txt("dcl_add_new_record"), $ilCtrl->getFormActionByClass("ildatacollectionrecordeditgui", "create"));
        }

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
		$list->setData($table->getRecords());
		$list->exportData(ilTable2GUI::EXPORT_EXCEL, true);
		$this->listRecords();
	}

    public function showImportExcel($form = null){
        global $tpl;
        if(!$form)
            $form = $this->initForm();
        $tpl->setContent($form->getHTML());
    }

    /**
     * @return ilPropertyFormGUI
     */
    public function initForm(){
        global $lng, $ilCtrl;
        /** @var $ilCtrl ilCtrl */
        $ilCtrl = $ilCtrl;
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        $item = new ilCustomInputGUI();
        $item->setHtml($lng->txt('dcl_file_format_description'));
        $item->setTitle("Info");
        $form->addItem($item);


        $file = new ilFileInputGUI($lng->txt("import_file"), "import_file");
        $file->setRequired(true);
        $form->addItem($file);

        $cb = new ilCheckboxInputGUI($lng->txt("dcl_simulate_import"), "simulate");
        $cb->setInfo($lng->txt("dcl_simulate_info"));
        $form->addItem($cb);

        $form->addCommandButton("importExcel", $lng->txt("save"));
        return $form;

    }

    public function importExcel(){
        global $lng;

        if(!($this->table_obj->hasPermissionToAddRecord($this->parent_obj->ref_id))){
            echo $lng->txt("access_denied");
            exit;
        }
        $form = $this->initForm();
        if($form->checkInput()){
            $file = $form->getInput("import_file");
            $file_location = $file["tmp_name"];
            $simulate = $form->getInput("simulate");
            $this->importRecords($file_location, $simulate);
        }else{
            $this->showImportExcel($form);
        }
    }

    private function importRecords($file, $simulate = false){
        global $ilUser, $lng;
        include_once("./Modules/DataCollection/libs/ExcelReader/excel_reader2.php");

        $warnings = array();
        try{
            $excel = new Spreadsheet_Excel_Reader($file);
        }catch (Exception $e){
            $warnings[] = $lng->txt("dcl_file_not_readable");
        }
        if(count($warnings))
            $this->endImport(0, $warnings);
        $field_names = array();
        for($i = 1; $i <= $excel->colcount(); $i++)
            $field_names[$i] = $excel->val(1, $i);
        $fields = $this->getImportFieldsFromTitles($field_names, $warnings);


        for($i = 2; $i <= $excel->rowcount(); $i++){
            $record = new ilDataCollectionRecord();
            $record->setTableId($this->table_obj->getId());
            $record->setOwner($ilUser->getId());
            $date_obj = new ilDateTime(time(), IL_CAL_UNIX);
            $record->setCreateDate($date_obj->get(IL_CAL_DATETIME));
            $record->setTableId($this->table_id);
            if(!$simulate)
                $record->doCreate();
            foreach($fields as $col => $field){
                $value = $excel->val($i, $col);
                try{
                    if($field->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_REFERENCE){
                        $old = $value;
                        $value = $this->getReferenceFromValue($field, $value);
                        if(!$value)
                            $warnings [] = "(".$i.", ".$this->getExcelCharForInteger($col).") ".$lng->txt("dcl_no_such_reference")." ".$old;
                    }
                    $field->checkValidity($value, $record->getId);
                    if(!$simulate)
                        $record->setRecordFieldValue($field->getId(), $value);
                }catch(ilDataCollectionInputException $e){
                    $warnings[] = "(".$i.", ".$this->getExcelCharForInteger($col).") ".$e;
                }
            }
            if(!$simulate)
                $record->doUpdate();
            if($i - 1 > $this->max_imports){
                $warnings[] = $lng->txt("dcl_max_import").($excel->rowcount() - 1)." > ".$this->max_imports;
                break;
            }
        }
        $this->endImport($i - 2, $warnings);
    }

    public function endImport($i, $warnings){
        global $tpl, $lng, $ilCtrl;
        $output = new ilTemplate("tpl.dcl_import_terminated.html",true, true, "Modules/DataCollection");
        $output->setVariable("IMPORT_TERMINATED", $lng->txt("dcl_import_terminated").": ".$i);
        foreach($warnings as $warning){
            $output->setCurrentBlock("warnings");
            $output->setVariable("WARNING", $warning);
            $output->parseCurrentBlock();
        }
        if(!count($warnings)){
            $output->setCurrentBlock("warnings");
            $output->setVariable("WARNING", $lng->txt("dcl_no_warnings"));
            $output->parseCurrentBlock();
        }
        $output->setVariable("BACK_LINK", $ilCtrl->getLinkTargetByClass("ilDataCollectionRecordListGUI", "listRecords"));
        $output->setVariable("BACK", $lng->txt("back"));
        $tpl->setContent($output->get());
    }

    /**
     * @param $field ilDataCollectionField
     * @param $value
     * @return int
     */
    public function getReferenceFromValue($field, $value){
        $field = ilDataCollectionCache::getFieldCache($field->getFieldRef());
        $table = ilDataCollectionCache::getTableCache($field->getTableId());
        $record_id = 0;
        foreach($table->getRecords() as $record)
            if($record->getRecordField($field->getId())->getValue() == $value)
                $record_id = $record->getId();
        return $record_id;
    }

    private function getExcelCharForInteger($int){
        $char = "";
        $rng = range("A", "Z");
        while($int > 0){
            $diff = $int % 26;
            $char = $rng[$diff-1].$char;
            $int -= $char;
            $int /= 26;
        }
        return $char;
    }

    /**
     * @param $field ilDataCollectionField
     * @param $warnings array
     */
    private function checkImportType($field, &$warnings){
        global $lng;
        if(in_array($field->getDatatypeId(), $this->supported_import_datatypes))
            return true;
        else{
            $warnings[] = $field->getTitle().": ".$lng->txt("dcl_not_supported_in_import");
            return false;
        }
    }

    /**
     * @param $titles string[]
     * @return ilDataCollectionField[]
     */
    private function getImportFieldsFromTitles($titles, &$warnings){
        global $lng;
        $fields = $this->table_obj->getRecordFields();
        $import_fields = array();
        foreach($fields as $field){
            if($this->checkImportType($field, $warnings)){
                foreach($titles as $key => $value)
                {
                    if($value == $field->getTitle())
                        $import_fields[$key] = $field;
                }
            }
        }
        foreach($titles as $key => $value)
        {
            if(!isset($import_fields[$key]))
                $warnings[] = "(1, ".$this->getExcelCharForInteger($key).") \"".$value."\" ".$lng->txt("dcl_row_not_found");
        }
        return $import_fields;
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