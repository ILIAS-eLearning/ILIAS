<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once ("./Modules/DataCollection/classes/class.ilDataCollectionField.php");

/**
* Class ilDataCollectionFieldListGUI
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
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
	public function  __construct($a_parent_obj, $table_id = NULL)
	{
		$this->mainTableId = $a_parent_obj->object->getMainTableId();
		$this->obj_id = $a_parent_obj->obj_id;
		include_once("class.ilDataCollectionDatatype.php");
		if($table_id)
		{
			$this->table_id = $table_id;
		} 
		else 
		{
			$this->table_id = $this->mainTableId;
		}

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
	 * list fields
	*/
	public function listFields()
	{
		global $tpl, $lng, $ilCtrl, $ilToolbar;

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
        $ilToolbar->addInputItem($table_selection);
		$ilToolbar->addFormButton($lng->txt('change'),'doTableSwitch');

		$ilCtrl->setParameterByClass("ildatacollectiontableeditgui","table_id", $this->table_id);
		$ilToolbar->addButton($lng->txt("dcl_add_new_table"), $ilCtrl->getLinkTargetByClass("ildatacollectiontableeditgui", "create"));
		
		$records = ilDataCollectionField::getAll($this->table_id);

		require_once('./Modules/DataCollection/classes/class.ilDataCollectionFieldListTableGUI.php');
		$list = new ilDataCollectionFieldListTableGUI($this, $ilCtrl->getCmd(), $records);

		$tpl->setContent($list->getHTML());

	}

	public function doTableSwitch() {
		return;
	}
}

?>