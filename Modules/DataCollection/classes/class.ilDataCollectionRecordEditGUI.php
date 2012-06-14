<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/DataCollection/classes/class.ilDataCollectionRecord.php");
require_once("./Modules/DataCollection/classes/class.ilDataCollectionField.php");

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

	/**
	 * Constructor
	 *
	*/
	public function __construct()
	{
		//TODO Prüfen, ob inwiefern sich die übergebenen GET-Parameter als Sicherheitslücke herausstellen
		$this->record_id = $_GET['record_id'];
		include_once("class.ilDataCollectionDatatype.php");
		if($_REQUEST['table_id']) 
		{
			$this->table_id = $_REQUEST['table_id'];
		}
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
		global $ilTabs, $tpl;

		$this->initForm();
	
		$tpl->setContent($this->form->getHTML());
	}

	/**
	 * edit Record
	*/
	public function edit()
	{
		global $tpl;
		
		$this->initForm("edit");
		$this->getValues();
		
		$tpl->setContent($this->form->getHTML());
	}	

	/**
	 * init Form
	 *
	 * @param string $a_mode values: create | edit
	 */
	public function initForm($a_mode = "create")
	{
		global $lng, $ilCtrl;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		//table_id
		$hidden_prop = new ilHiddenInputGUI("table_id");
		$hidden_prop ->setValue($this->table_id);
		$this->form->addItem($hidden_prop );

		//TODO Falls Feld-Reihenfolge festgelegt, dann nehmen wir diese. Andernfalls sämtliche Felder darstellen
		//if
        //...
		//else
		//sämtliche Felder
		$allFields = ilDataCollectionField::getAll($this->table_id);

		foreach($allFields as $field)
		{

			switch($field['datatype_id'])
			{
				case ilDataCollectionDatatype::INPUTFORMAT_TEXT:       
					$item = new ilTextInputGUI($field['title'], 'field_'.$field['id']);
					$this->form->addItem($item);
					break;
					
				case ilDataCollectionDatatype::INPUTFORMAT_NUMBER:
					$item = new ilTextInputGUI($field['title'], 'field_'.$field['id']);
					$this->form->addItem($item);
					break;

				/*case ilDataCollectionDatatype::INPUTFORMAT_REFERENCE:
					//TODO select-list
					//$subitem = new ilCheckboxInputGUI($lng->txt($field['title']), 'field_'.$field['id']);
					//$opt->addSubItem($subitem);
				break;*/

				case ilDataCollectionDatatype::INPUTFORMAT_BOOLEAN:
					$item = new ilCheckboxInputGUI($field['title'], 'field_'.$field['id']);
					$this->form->addItem($item);
					break;

				case ilDataCollectionDatatype::INPUTFORMAT_DATETIME:
					$item = new ilDateTimeInputGUI($field['title'], 'field_'.$field['id']);
					$this->form->addItem($item);
					break;
				
				case ilDataCollectionDatatype::INPUTFORMAT_FILE:
					$item = new ilFileInputGUI($field['title'], 'field_'.$field['id']);
					$this->form->addItem($item);
					break;
			}

			//datetype_id mitgegeben -> wird beim speichern zu bestimmung für die storage_id benötigt
			$item = new ilHiddenInputGUI($field['storage_location'], 'storage_location_'.$field['id']);
			$this->form->addItem($item);

		}

		// save and cancel commands
		if(isset($rec_id))
		{
			$this->form->addCommandButton("update", $lng->txt("update"));
			$this->form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
			$this->form->setFormAction($ilCtrl->getFormAction($this, "update"));
		}
		else
		{
			$this->form->addCommandButton("save", $lng->txt("save"));
			$this->form->addCommandButton("cancelSave", $lng->txt("cancel"));
		}
				
		$this->form->setTitle($lng->txt("dcl_add_new_record"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}


	/**
	* get Values
	* 
	*/
	//FIXME
	public function getValues()
	{

		//Get Record-Values
		$record_obj = new ilDataCollectionRecord($this->record_id);

		//Get Table Field Definitions
		$allFields = ilDataCollectionField::getAll($this->table_id);

		$values = array();
		foreach($allFields as $field)
		{
			$values['field_'.$field['id']] = $record_obj->getFieldvalues($field['id']);
		}

	
		$this->form->setValuesByArray($values);

		return true;
	}

	/**
	* save Record
	*
	* @param string $a_mode values: create | edit
	*/
	public function save($a_mode = "create")
	{	
		global $tpl, $ilUser, $lng, $ilCtrl;

		//Sämtliche Felder, welche gespeichert werden holen
		$all_fields = ilDataCollectionField::getAll($this->table_id);

		$this->initForm($a_mode);
		if($this->form->checkInput())
		{
			$record_obj = new ilDataCollectionRecord();

			$date_obj = new ilDateTime(time(), IL_CAL_UNIX);

			$record_obj->setTableId($this->table_id);
			if($a_mode == "create")
			{
				$record_obj->setCreateDate($date_obj->get(IL_CAL_DATETIME));
			}
			$record_obj->setLastUpdate($date_obj->get(IL_CAL_DATETIME));
			$record_obj->setOwner($ilUser->getId());

			foreach($all_fields as $key => $value)
			{
				//TODO Properties holen und die Felder entsprechend überprüfen
				//echo "<pre>".print_r($all_fields,1)."</pre>";
				if($value['datatype_id'] == self::INPUTFORMAT_FILE)
				{
					$file = $this->form->getInput("field_".$value["id"]);
					if($file['tmp_name'])
					{
						include("class.ilObjDataCollectionFile.php");
						$file_obj = new ilObjDataCollectionFile();
						//echo "<pre>".print_r($this->form->getInput("dcl_fileupload"),1)."</pre>";
						
						$file_obj->setType("file");
						$file_obj->setTitle($file["name"]);
						$file_obj->setFileName($file["name"]);
						include_once ("./Services/Utilities/classes/class.ilMimeTypeUtil.php");
						$file_obj->setFileType(ilMimeTypeUtil::getMimeType("", $file["name"], $file["type"]));
						$file_obj->setFileSize($file["size"]);
						$file_obj->setMode("object");
						$file_obj->create();
						$file_obj->getUploadFile($file["tmp_name"], $file["name"]);
						
						$file_id = $file_obj->getId();

					}
					$record_obj->setFieldvalue($file_id, $value["id"]);
				}
				else
				{
					$record_obj->setFieldvalue($this->form->getInput("field_".$value["id"]), $value["id"]);
				}	
				
			}

			//We need $allFields because of the storage_location
			$record_obj->doCreate($all_fields);
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"),true);

			$ilCtrl->setParameter($this, "table_id", $this->table_id);

			$ilCtrl->redirectByClass("ildatacollectionrecordlistgui", "listRecords");

			$ilCtrl->redirect($this, "create");
		}

	}
}

?>