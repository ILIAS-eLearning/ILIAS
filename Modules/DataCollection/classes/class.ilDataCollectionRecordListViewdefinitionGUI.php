<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

//require_once "...";

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
	public function __construct($a_parent_obj, $table_id = NULL)
	{
		$this->main_table_id = $a_parent_obj->object->getMainTableId();
		$this->obj_id = $a_parent_obj->obj_id;
		include_once("class.ilDataCollectionDatatype.php");
		if($table_id)
		{
			$this->table_id = $table_id;
		} 
		else 
		{
			$this->table_id = $this->main_table_id;
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
	 * a_val = 
	 */
	public function initForm($a_mode = "create")
	{
		global $lng, $ilCtrl;
		//Get fields
		require_once("./Modules/DataCollection/classes/class.ilDataCollectionField.php");
		$fields = ilDataCollectionField::getAll($this->table_id);
		echo "<pre>".print_r($fields,1)."</pre>";
		
		// Form
		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		
		$this->form->addCommandButton('save', 		$lng->txt('dcl_listviewdefinition_'.$a_mode));
		$this->form->addCommandButton('cancel', 	$lng->txt('cancel'));
		
		$this->form->setFormAction($ilCtrl->getFormAction($this, "save"));
	
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
	 * saveRecordListViewdefinition
	 * 
	 */
	public function save()
	{
		global $x;
	
		
		return true;
	}
	

//
// Methoden FilterViewdefinition
//
	
	/**
	 * createRecordListFilterViewdefinition
	 * a_val = 
	 */
	public function createRecordListFilterViewdefinition()
	{
		global $x;
	
		
		return true;
	}
	
	/**
	 * initRecordListFilterViewdefinitionForm
	 * a_val = 
	 */
	public function initRecordListFilterViewdefinitionForm()
	{
		global $x;
	
		
		return true;
	}
	
	/**
	 * getRecordListFilterdefinitionValues
	 * a_val = 
	 */
	public function getRecordListFilterdefinitionValues()
	{
		global $x;
	
		
		return true;
	}
	
	/**
	 * saveRecordListFilterViewdefinition
	 * a_val = 
	 */
	public function saveRecordListFilterViewdefinition()
	{
		global $x;
	
		
		return true;
	}
	
	/**
	 * updateRecordListFilterViewdefinition
	 * a_val = 
	 */
	public function updateRecordListFilterViewdefinition()
	{
		global $x;
	
		
		return true;
	}
	
	
	
	
}

?>