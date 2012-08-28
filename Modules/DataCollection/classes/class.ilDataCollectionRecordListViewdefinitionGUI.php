<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/DataCollection/classes/class.ilDataCollectionRecordListViewdefinition.php");

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
class ilDataCollectionRecordListViewdefinitionGUI
{
	/*
	 * __construct
	 */
	public function __construct($a_parent_obj, $table_id)
	{
		$this->main_table_id = $a_parent_obj->object->getMainTableId();
		$this->table_id = $table_id;
		$this->obj_id = $a_parent_obj->obj_id;

		if(isset($view_id)) 
		{
			//TODO
			//$this->field_obj = new ilDataCollectionField($field_id);
		}
		else
		{
			$this->view_obj = new ilDataCollectionRecordListViewdefinition();
			$this->view_obj->setTableId($table_id);
		}

		return;
	}
	
	/**
	 * execute command
	 */
	public function executeCommand()
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
	 *
	 *
	 */
	public function edit()
	{
		global $tpl;
		
		$this->initForm();
		$this->getFormValues();
		
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * initRecordListViewdefinitionForm
	 *
	 */
	public function initForm()
	{
		global $lng, $ilCtrl, $ilToolbar;

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
		$ilToolbar->setFormAction($ilCtrl->getFormActionByClass("ilDataCollectionRecordListViewdefinitionGUI", "doTableSwitch"));
		$ilToolbar->addInputItem($table_selection);
		$ilToolbar->addFormButton($lng->txt('change'),'doTableSwitch');

		// Form
		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		$this->form->setFormAction($ilCtrl->getFormAction($this),"save");
		$this->form->addCommandButton('save', $lng->txt('dcl_listviewdefinition_update'));
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
			"table_id" => array("title" => $lng->txt("dcl_table_id")), 
			"create_date" => array("title" => $lng->txt("create_date")), 
			"last_update" => array("title" => $lng->txt("last_update")), 
			"owner" => array("title" => $lng->txt("owner"))
		);

		//Array zusammenführen
		foreach($fields as $key => $value) 
		{
			$tabledefinition[$key] = $value;
		}

		foreach($tabledefinition as $key => $field)
		{
			$chk_prop = new ilCheckboxInputGUI($field['title'],'visible_'.$key);
			$chk_prop->setOptionTitle($lng->txt('visible'));

			$text_prop = new ilTextInputGUI($lng->txt('dcl_field_ordering'), 'order_'.$key);
			$chk_prop->addSubItem($text_prop);

			$this->form->addItem($chk_prop);
		}
		
		$this->form->setTitle($lng->txt('dcl_view_viewdefinition'));
		
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

		if($this->form->checkInput())
		{
			//Get fields
			require_once("./Modules/DataCollection/classes/class.ilDataCollectionField.php");
			$fields = ilDataCollectionField::getAll($this->table_id);

			//TODO tabledefs global definieren
			$tabledefinition = array(
				"id" => array("title" => $lng->txt("id")), 
				"table_id" => array("title" => $lng->txt("dcl_table_id")), 
				"create_date" => array("title" => $lng->txt("create_date")), 
				"last_update" => array("title" => $lng->txt("last_update")), 
				"owner" => array("title" => $lng->txt("owner"))
								);
			// Array zusammenführen TODO
			foreach($fields as $key => $value) 
			{
				$tabledefinition[$key] = $value;
			}
			
			foreach($tabledefinition as $key => $field)
			{
				if($this->form->getInput("visible_".$key))
				{
					$this->view_obj->setArrFieldOrder($this->form->getInput("order_".$key),$key);
				}
			}

			$this->view_obj->doCreate();

			ilUtil::sendSuccess($lng->txt("msg_obj_modified"),true);

			$ilCtrl->redirect($this, "edit");
		}
		else
		{
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"),false);
			$this->form_gui->setValuesByPost();
			$this->tpl->setContent($this->form_gui->getHTML());
		}
	}


	/**
	 *  doTableSwitch
	 *
	 */
	public function doTableSwitch()
	{
		global $ilCtrl;

		$ilCtrl->setParameterByClass("ilObjDataCollectionGUI","table_id", $_POST['table_id']);
 		$ilCtrl->redirect($this, "edit"); 			

	}

}

?>