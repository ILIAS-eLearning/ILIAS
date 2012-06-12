<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Modules/DataCollection/classes/class.ilDataCollectionTable.php");

/**
* Class ilDataCollectionTableEditGUI
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
*
* @ingroup ModulesDataCollection
*/
	
class ilDataCollectionTableEditGUI
{
	
	/**
	 * Constructor
	 *
	 * @param	object	$a_parent_obj
	 */
	function __construct($a_parent_obj)
	{
		$this->obj_id = $a_parent_obj->obj_id;
	}

	
	/**
	 * execute command
	 */
	function executeCommand()
	{
		global $tpl, $ilCtrl;
		
		$cmd = $ilCtrl->getCmd();
		$tpl->getStandardTemplate();
		
		switch($cmd)
		{
			default:
				$this->$cmd();
				break;
		}

		return true;
	}

	/**
	 * create table add form
	*/
	public function create()
	{
		global $ilTabs, $tpl;
		
		$this->initForm();
		
		$tpl->setContent($this->form->getHTML());
	}

	/**
	 * create field edit form
	*/
	public function edit()
	{
		global $ilTabs, $tpl;
		
		$this->initForm("edit");
		//$this->getFieldValues();
		$tpl->setContent($this->form->getHTML());
	}
	
	
	/**
	 * initEditCustomForm
	 *
	 * @param string $a_mode
	 */
	public function initForm($a_mode = "create")
	{
		global $ilCtrl, $ilErr, $lng;
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		$item = new ilTextInputGUI($lng->txt('title'),'title');
		$this->form->addItem($item);
		
		$this->form->addCommandButton('save', 	$lng->txt('dcl_table_'.$mode));
		$this->form->addCommandButton('cancel', 	$lng->txt('cancel'));
		
		$this->form->setFormAction($ilCtrl->getFormAction($this, "save"));
	
		$this->form->setTitle($lng->txt('dcl_new_table'));
	}
	
	
	/**
	 * save
	 *
	 * @param string $a_mode values: create | edit
	*/
	public function save($a_mode = "create")
	{
		global $ilCtrl, $ilTabs, $lng;
		
		//TODO Check Permissons
		//$this->dcl_object->checkPermission("write");
		$ilTabs->activateTab("id_fields");
		
		$this->initForm($a_mode);
		if ($this->form->checkInput())
		{
			$table_obj = new ilDataCollectionTable();
		
			$table_obj->setTitle($this->form->getInput("title"));
			$table_obj->setObjId($this->obj_id);
			$table_obj->doCreate();

			ilUtil::sendSuccess($lng->txt("msg_obj_modified"),true);			
			$ilCtrl->redirect($this, "create");
		}
		else
		{
			$this->form_gui->setValuesByPost();
			$this->tpl->setContent($this->form_gui->getHTML());
		}
	}

}

?>