<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/DataCollection/classes/class.ilDataCollectionRecord.php");
require_once("./Modules/DataCollection/classes/class.ilDataCollectionField.php");
require_once("./Modules/DataCollection/classes/class.ilDataCollectionTable.php");
require_once("./Modules/DataCollection/classes/class.ilDataCollectionDatatype.php");


/**
* Class ilDataCollectionRecordEditGUI
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
* @version $Id: 
*
*/


class ilDataCollectionRecordEditGUI
{

    private $record_id;
    private $table_id;
    private $table;
    private $parent_obj;

	/**
	 * Constructor
	 *
	*/
	public function __construct(ilObjDataCollectionGUI $parent_obj)
	{
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();
        $this->parent_obj = $parent_obj;
		//TODO Prüfen, ob inwiefern sich die übergebenen GET-Parameter als Sicherheitslücke herausstellen
		$this->record_id = $_REQUEST['record_id'];
        $this->table_id = $_GET['table_id'];
		include_once("class.ilDataCollectionDatatype.php");
		if($_REQUEST['table_id']) 
		{
			$this->table_id = $_REQUEST['table_id'];
		}

		$this->table = new ilDataCollectionTable($this->table_id);
	}
	
	
	/**
	* execute command
	*/
	function executeCommand()
	{
		global $ilCtrl;

		$cmd = $ilCtrl->getCmd();

		switch($cmd)
		{
			default:
			$this->$cmd();
			break;
		}
		return true;
	}
	
	
	/**
	 * create Record
	 *
	 */
	public function create()
	{
		global $ilCtrl, $tpl;

		$this->initForm();

        $tpl->setContent($this->form->getHTML());
	}

	/**
	 * edit Record
	*/
	public function edit()
	{
		global $tpl, $ilCtrl;
		
		$this->initForm("edit");
        $this->getValues();
		
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * confirmDelete
	 */
	public function confirmDelete()
	{
		global $ilCtrl, $lng, $tpl;
		
		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$conf = new ilConfirmationGUI();
		$conf->setFormAction($ilCtrl->getFormAction($this));
		$conf->setHeaderText($lng->txt('dcl_confirm_delete_record'));

		$record = new ilDataCollectionRecord($this->record_id);
		
		$conf->addItem('record_id', $record->getId(), implode(", ", $record->getRecordFieldValues()));
		$conf->addHiddenItem('table_id', $this->table_id);
		
		$conf->setConfirm($lng->txt('delete'), 'delete');
		$conf->setCancel($lng->txt('cancel'), 'cancelDelete');

		$tpl->setContent($conf->getHTML());
	}
	
	/**
	 * cancelDelete
	 */
	public function cancelDelete()
	{
		global $ilCtrl;
		
		$ilCtrl->redirectByClass("ildatacollectionfieldlistgui", "listFields");
	}
	
    public function delete()
    {
        global $ilCtrl, $lng;
        
        $record = new ilDataCollectionRecord($this->record_id);
        $record->doDelete();
        ilUtil::sendSuccess($lng->txt("dcl_record_deleted"), true);
        $ilCtrl->redirectByClass("ildatacollectionrecordlistgui", "listRecords");
    }

	/**
	 * init Form
	 *
	 * @param string $a_mode values: create | edit
	 */
	public function initForm()
	{
		global $lng, $ilCtrl;

		//table_id
		$hidden_prop = new ilHiddenInputGUI("table_id");
		$hidden_prop ->setValue($this->table_id);
		$this->form->addItem($hidden_prop );

		$ilCtrl->setParameter($this, "record_id", $this->record_id);
		$this->form->setFormAction($ilCtrl->getFormAction($this));


			//TODO: für benutzer ohne write rechten ändern in getEditableFields.
		$allFields = $this->table->getRecordFields();

		foreach($allFields as $field)
		{
            $item = ilDataCollectionDatatype::getInputField($field);
			if($field->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_REFERENCE){
				$fieldref = $field->getFieldRef();
				$reffield = new ilDataCollectionField($fieldref);
				$options = array();
				$reftable = new ilDataCollectionTable($reffield->getTableId());
				// echo "fieldref: ".$fieldref." tableref: ".$reftable->getId();
				foreach($reftable->getRecords() as $record){
					$options[$record->getId()] = $record->getRecordFieldValue($fieldref);
				}
				$item->setOptions($options);
			}
            $item->setRequired($field->getRequired());
			$item->setInfo($this->getInfo($field));
            $this->form->addItem($item);
		}

		// save and cancel commands
		if(isset($this->record_id))
		{
			$this->form->addCommandButton("save", $lng->txt("update"));
			$this->form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
		}
		else
		{
			$this->form->addCommandButton("save", $lng->txt("save"));
			$this->form->addCommandButton("cancelSave", $lng->txt("cancel"));
		}

        $ilCtrl->setParameter($this, "table_id", $this->table_id);
        $ilCtrl->setParameter($this, "record_id", $this->record_id);
		$this->form->setTitle($lng->txt("dcl_add_new_record"));
	}


	private function getInfo(ilDataCollectionField $field){
		global $lng;

		if($field->isEditable())
			$info =" ".$lng->txt("dcl_is_editable_by_others_info");
		else
			$info =" ".$lng->txt("dcl_is_not_editable_by_others_info");

		if($field->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_TEXT){
			$info .= $lng->txt("dcl_text_length").": ".$field->getLength();
		}

		return $info;
	}

	/**
	* get Values
	* 
	*/
	// FIXME
	public function getValues()
	{

		//Get Record-Values
		$record_obj = new ilDataCollectionRecord($this->record_id);

		//Get Table Field Definitions
		$allFields = $this->table->getFields();

		$values = array();
		foreach($allFields as $field)
		{
            $value = $record_obj->getRecordFieldFormInput($field->getId());
            $value = ($value=="-"?"":$value);
			$values['field_'.$field->getId()] = $value;
		}

		$this->form->setValuesByArray($values);

		return true;
	}
	
	/*
	 * cancelUpdate
	 */
    public function cancelUpdate()
    {
        global $ilCtrl;
        $ilCtrl->redirectByClass("ildatacollectionrecordlistgui", "listRecords");
    }
    
    /*
     * cancelSave
     */
    public function cancelSave()
    {
        $this->cancelUpdate();
    }

	/**
	* save Record
	*
	* @param string $a_mode values: create | edit
	*/
	public function save()
	{	
		global $tpl, $ilUser, $lng, $ilCtrl;

		$this->initForm();
		if($this->form->checkInput())
		{
			$record_obj = new ilDataCollectionRecord($this->record_id);

			$date_obj = new ilDateTime(time(), IL_CAL_UNIX);

			$record_obj->setTableId($this->table_id);

			$record_obj->setLastUpdate($date_obj->get(IL_CAL_DATETIME));
			$record_obj->setLastEditBy($ilUser->getId());

			//check access. those who can edit can also create records.
			if(!$record_obj->hasEditPermission($this->parent_obj->ref_id)){
				$this->accessDenied();
				return;
			}

            if(!isset($this->record_id))
            {
				$record_obj->setOwner($ilUser->getId());
				$record_obj->setCreateDate($date_obj->get(IL_CAL_DATETIME));
                $record_obj->setTableId($this->table_id);
                $record_obj->DoCreate();
                $this->record_id = $record_obj->getId();
            }

			$all_fields = $this->table->getRecordFields();
			$fail = "";
			foreach($all_fields as $field)
			{
            	try
			    {
                   $value = $this->form->getInput("field_".$field->getId());
					$record_obj->setRecordFieldValue($field->getId(), $value);
				}catch(ilDataCollectionInputException $e){
                 $fail .= $field->getTitle().": ".$e."<br>";
            	}
				
			}

			if($fail){
				ilUtil::sendFailure($fail, true);
				$this->sendFailure();
				return;
			}

			$record_obj->doUpdate();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"),true);

			$ilCtrl->setParameter($this, "table_id", $this->table_id);
            $ilCtrl->setParameter($this, "record_id", $this->record_id);
			$ilCtrl->redirectByClass("ildatacollectionrecordlistgui", "listRecords");
		}else{
			global $tpl;
			$this->form->setValuesByPost();
			$tpl->setContent($this->form->getHTML());
        }

	}

	private function accessDenied(){
		global $tpl;
		$tpl->setContent("Access denied");
	}

	private function sendFailure(){
		global $tpl;
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHTML());
	}
}

?>