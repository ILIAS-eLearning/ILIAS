<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Modules/DataCollection/classes/class.ilDataCollectionRecordViewViewdefinition.php");

/**
* Class ilDataCollectionRecordViewViewdefinitionGUI
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
*
*/


class ilDataCollectionRecordViewViewdefinitionGUI
{
	public function __construct()
	{
		//TODO Permission-Check
		$this->table_id = $_GET[table_id];
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
	 * create Record View Viewdefinition
	 *
	 */
	public function create()
	{
		global $tpl;
		
		$this->initForm("create");
		$this->getFormValues();
		
		$tpl->setContent($this->form->getHTML());
	}

	/**
	 * edit Record View Viewdefinition
	*/
	public function edit()
	{
		global $tpl;
		
		$this->initForm("edit");
		$this->getFormValues();
		
		$tpl->setContent($this->form->getHTML());
	}	

	/**
	 * init Form Record View ViewdefinitionForm
	 *
	 * @param string $a_mode values: create | edit
	 */
	public function initForm($a_mode = "create")
	{
		global $ilCtrl, $lng;


		//Get fields
		require_once("./Modules/DataCollection/classes/class.ilDataCollectionField.php");
		$fields = ilDataCollectionField::getAll($this->table_id);
		//TODO das Array enthält die Felder der Tabelle. Diese sind als Platzhalterwerte darzustellen.
		//Bezeichnung des Platzhalters: Title; Wert welcher beim Speichern übermittelt werden soll id

		// Wir schlagen vor: für die Felder werden einfach Klammern genutzt: [Name], [Anrede], ...
		//Für die Formular-Funkionen werden #...# verwendet:
		//	#More#
		//	#Save#
		//	#Approve#
		//	#Delete#
		//	#Edit#
		//	#Search#


		
		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		
		$this->form->addCommandButton('save', 	$lng->txt('dcl__field_'.$mode));
		$this->form->addCommandButton('cancel', 	$lng->txt('cancel'));
		
		$this->form->setFormAction($ilCtrl->getFormAction($this, "save"));
	
		$this->form->setTitle($lng->txt('dcl_view_viewdefinition'));
		
		$hidden_prop = new ilHiddenInputGUI("table_id");
		$hidden_prop ->setValue($this->table_id);
		$this->form->addItem($hidden_prop );
		
		//TODO hier sollte statt des Textarea-Feldes der ILIAS-Page-Editor eingeblendet werden
		$text_prop = new ilTextAreaInputGUI($lng->txt("dcl_view_viewdefinition_description"), "viewdefinition");
		$this->form->addItem($text_prop);

	}

	/**
	 * get Form Values
	 *
	 */
	public function getFormValues()
	{
	
		
	}

	/**
	 * save
	 *
	 * @param string $a_mode values: create | edit
	 */
	public function save($a_mode = "create")
	{
		global $lng, $ilCtrl;

		$this->initForm($this->table_id);
		if ($this->form->checkInput())
		{
			$viewdefinition_obj = new ilDataCollectionRecordViewViewdefinition();

			$viewdefinition_obj->setTableId($this->table_id);
			$viewdefinition_obj->doCreate($all_fields);

			ilUtil::sendSuccess($lng->txt("msg_obj_modified"),true);

			$ilCtrl->setParameter($this, "table_id", $this->table_id);
			$ilCtrl->redirect($this, "create");
		}
	}


}

?>