<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once ("./Modules/DataCollection/classes/class.ilDataCollectionField.php");
require_once ("./Modules/DataCollection/classes/class.ilDataCollectionTable.php");
include_once("class.ilDataCollectionDatatype.php");
require_once "class.ilDataCollectionCache.php";


/**
* Class ilDataCollectionFieldListGUI
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Oskar Truffer <ot@studer-raimann.ch>
* @version $Id: 
*
*
* @ingroup ModulesDataCollection
*/
class ilDataCollectionFieldListGUI
{
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
		$this->parent_obj = $a_parent_obj;
		$this->obj_id = $a_parent_obj->obj_id;
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
	
	/*
	 * save
	 */
	public function save()
	{
		global $lng;
		$table = ilDataCollectionCache::getTableCache($_GET['table_id']);
		$fields = &$table->getFields();

		foreach($fields as &$field)
		{
			$field->setVisible($_POST['visible'][$field->getId()] == "on");
			$field->setEditable($_POST['editable'][$field->getId()] == "on");
			$field->setFilterable($_POST['filterable'][$field->getId()] == "on");
			$field->setLocked($_POST['locked'][$field->getId()] == "on");
			$field->setOrder($_POST['order'][$field->getId()]);
			$field->doUpdate();
		}
		$table->buildOrderFields();
		ilUtil::sendSuccess($lng->txt("dcl_table_settings_saved"));
		$this->listFields();
	}
	
	/**
	 * list fields
	 */
	public function listFields()
	{
		global $tpl, $lng, $ilCtrl, $ilToolbar;

		// Show tables
		require_once("./Modules/DataCollection/classes/class.ilDataCollectionTable.php");
		$tables = $this->parent_obj->object->getTables();

		foreach($tables as $table)
		{
				$options[$table->getId()] = $table->getTitle();
		}
		include_once './Services/Form/classes/class.ilSelectInputGUI.php';
		$table_selection = new ilSelectInputGUI('', 'table_id');
		$table_selection->setOptions($options);
		$table_selection->setValue($this->table_id);

		$ilToolbar->setFormAction($ilCtrl->getFormActionByClass("ilDataCollectionFieldListGUI", "doTableSwitch"));
        $ilToolbar->addText($lng->txt("dcl_table"));
		$ilToolbar->addInputItem($table_selection);
		$ilToolbar->addFormButton($lng->txt('change'),'doTableSwitch');
        $ilToolbar->addSeparator();
		$ilToolbar->addButton($lng->txt("dcl_add_new_table"), $ilCtrl->getLinkTargetByClass("ildatacollectiontableeditgui", "create"));
        $ilToolbar->addSeparator();
        $ilCtrl->setParameterByClass("ildatacollectiontableeditgui", "table_id", $this->table_id);
		$ilToolbar->addButton($lng->txt("dcl_table_settings"), $ilCtrl->getLinkTargetByClass("ildatacollectiontableeditgui", "edit"));
		$ilToolbar->addButton($lng->txt("dcl_delete_table"), $ilCtrl->getLinkTargetByClass("ildatacollectiontableeditgui", "confirmDelete"));
        $ilToolbar->addButton($lng->txt("dcl_add_new_field"), $ilCtrl->getLinkTargetByClass("ildatacollectionfieldeditgui", "create"));

        // requested not to implement this way...
//        $tpl->addJavaScript("Modules/DataCollection/js/fastTableSwitcher.js");

		require_once('./Modules/DataCollection/classes/class.ilDataCollectionFieldListTableGUI.php');
		$list = new ilDataCollectionFieldListTableGUI($this, $ilCtrl->getCmd(), $this->table_id);

		$tpl->setContent($list->getHTML());

	}
	
	/*
	 * doTableSwitch
	 */
	public function doTableSwitch()
	{
		global $ilCtrl;

		$ilCtrl->setParameterByClass("ilObjDataCollectionGUI", "table_id", $_POST['table_id']);
		$ilCtrl->redirectByClass("ilDataCollectionFieldListGUI", "listFields"); 			
	}
}

?>