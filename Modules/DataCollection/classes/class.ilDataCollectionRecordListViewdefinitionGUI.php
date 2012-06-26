<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Modules/DataCollection/classes/class.ilDataCollectionRecordListViewdefinition.php");


/**
* Class ilDataCollectionRecordListViewdefinitionGUI
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
*
*/


class ilDataCollectionRecordListViewdefinitionGUI
{
	public function __construct($a_parent_obj, $table_id)
	{
		$this->main_table_id = $a_parent_obj->object->getMainTableId();
		$this->table_id = $table_id;
		$this->obj_id = $a_parent_obj->obj_id;

		if(isset($view_id)) 
		{
			//TODO
			//$this->field_obj = new ilDataCollectionField($field_id);
		} else {
			$this->view_obj = new ilDataCollectionRecordListViewdefinition();
			$this->view_obj->setTableId($table_id);
		}

		return;
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
	
//
// Methoden ListViewdefinition
//

	/**
	 * createRecordListViewdefinition
	 * a_val = 
	 */
	public function create($a_val)
	{
		global $tpl;
		
		$this->initForm("create");
		$this->getFormValues();
		
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * initRecordListViewdefinitionForm
	 *
	 * @param string $a_mode values: create | edit
	 */
	public function initForm($a_mode = "create")
	{
		global $lng, $ilCtrl;

		// Form
		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		if ($a_mode == "edit")
		{
			$this->form->setFormAction($ilCtrl->getFormAction($this),"update");
			$this->form->addCommandButton('update', $lng->txt('dcl_listviewdefinition_'.$a_mode));
		} 
		else 
		{
			$this->form->setFormAction($ilCtrl->getFormAction($this),"save");
			$this->form->addCommandButton('save', 		$lng->txt('dcl_listviewdefinition_'.$a_mode));
		}
		$this->form->addCommandButton('cancel', 	$lng->txt('cancel'));

		//Table-ID
		$hidden_prop = new ilHiddenInputGUI("table_id");
		$hidden_prop->setValue($this->table_id);
		$this->form->addItem($hidden_prop);

		//Get fields
		require_once("./Modules/DataCollection/classes/class.ilDataCollectionField.php");
		$fields = ilDataCollectionField::getAll($this->table_id);

		$tabledefinition = array(
								"id" => array("title" => $lng->txt("id")), 
								"dcl_table_id" => array("title" => $lng->txt("dcl_table_id")), 
								"create_date" => array("title" => $lng->txt("create_date")), 
								"last_update" => array("title" => $lng->txt("last_update")), 
								"owner" => array("title" => $lng->txt("owner"))
							);

		$fields = array_merge($tabledefinition,$fields);
		
		foreach($fields as $key => $field) {
			$chk_prop = new ilCheckboxInputGUI($field['title'],'visible_'.$key);
			$chk_prop->setOptionTitle($lng->txt('visible'));

			$text_prop = new ilTextInputGUI($lng->txt('dcl_field_ordering'), 'order_'.$key);
			$chk_prop->addSubItem($text_prop);

			$this->form->addItem($chk_prop);
		}
		
		
	
		$this->form->setTitle($lng->txt('dcl_view_viewdefinition'));
		
	}
	
	/**
	 * getRecordListViewdefinitionValues
	 * a_val = 
	 */
	public function getFormValues()
	{
		global $x;
	
		
		return true;
	}
	

	/**
	 *  saveRecordListViewdefinition
	 *
	 * @param string $a_mode values: create | update
	 */
	public function save($a_mode = "create")
	{
		global $ilCtrl, $lng;
		
		$this->initForm();

		if ($this->form->checkInput())
		{
			//Get fields
			require_once("./Modules/DataCollection/classes/class.ilDataCollectionField.php");
			$fields = ilDataCollectionField::getAll($this->table_id);

			//TODO tabledefs global definieren
			$tabledefinition = array(
									"id" => array("title" => $lng->txt("id")), 
									"dcl_table_id" => array("title" => $lng->txt("dcl_table_id")), 
									"create_date" => array("title" => $lng->txt("create_date")), 
									"last_update" => array("title" => $lng->txt("last_update")), 
									"owner" => array("title" => $lng->txt("owner"))
								);
			$fields = array_merge($tabledefinition,$fields);
			
			foreach($fields as $key => $field) {
				$this->view_obj->setArrFieldOrder($this->form->getInput("order_".$key),$key);
			}

			if($a_mode == "update") 
			{
				$this->view_obj->doUpdate();
			}
			else 
			{
				$this->view_obj->doCreate();
			}

			ilUtil::sendSuccess($lng->txt("msg_obj_modified"),true);

			$ilCtrl->redirect($this, "create");
		}
		else
		{
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"),false);
			$this->form_gui->setValuesByPost();
			$this->tpl->setContent($this->form_gui->getHTML());
		}
	}

}

?>