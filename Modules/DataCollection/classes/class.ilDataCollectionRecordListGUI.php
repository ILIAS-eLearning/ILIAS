<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilDataCollectionRecordListGUI
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
* @version $Id: 
*
*
* @ingroup ModulesDataCollection
*/


class ilDataCollectionRecordListGUI
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
		

		If($table_id) 
		{
			$this->table_id = $table_id;
		} 
		else 
		{
			$this->table_id = $this->mainTableId;
		}
		
		include_once("./Modules/DataCollection/classes/class.ilDataCollectionRecord.php");

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
	 * List Records
     *
     * 
	 */
	public function listRecords()
	{
		global $ilTabs, $tpl, $lng, $ilCtrl, $ilToolbar;
    
		$ilTabs->setTabActive("id_records");

		//TODO Falls Reihenfolge festgelegt Reihenfolge und Felder festgelegt in DB abfragen. Andernfalls alle Felder anzeigen
		//if
       
		//$tabledefinition = array("id","table_id","create_date","last_update","owner","record_field_5","record_field_2","record_field_1");
		//$recordsfields = array(0 => array('id' => 1, 'storage_location' => 1), 1 => array('id' => 2, 'storage_location' => 2), 2 => array('id' => 5, 'storage_location' => 1));
		//...
		//else
		require_once("./Modules/DataCollection/classes/class.ilDataCollectionField.php");
		$recordsfields = ilDataCollectionField::getAll($this->table_id);
  
		$tabledefinition = array(
								"id" => array("title" => $lng->txt("id")), 
								"dcl_table_id" => array("title" => $lng->txt("dcl_table_id")), 
								"create_date" => array("title" => $lng->txt("create_date")), 
								"last_update" => array("title" => $lng->txt("last_update")), 
								"owner" => array("title" => $lng->txt("owner"))
							);
		
		foreach($recordsfields as $recordsfield) 
		{
			$tabledefinition["record_field_".$recordsfield['id']] = array("title" => $recordsfield['title'], "datatype_id" => $recordsfield['datatype_id']);
		}
		
	    $records = ilDataCollectionRecord::getAll($this->table_id, $recordsfields);

	    require_once('./Modules/DataCollection/classes/class.ilDataCollectionRecordListTableGUI.php');
		$list = new ilDataCollectionRecordListTableGUI($this, $ilCtrl->getCmd(), $records, $tabledefinition);

		$tpl->setContent($list->getHTML());
	}
	
}

?>