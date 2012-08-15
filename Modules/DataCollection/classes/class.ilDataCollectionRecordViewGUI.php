<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Modules/DataCollection/classes/class.ilDataCollectionTable.php');
include_once('./Services/COPage/classes/class.ilPageObjectGUI.php');
include_once('./Modules/DataCollection/classes/class.ilDataCollectionRecord.php');
include_once('./Modules/DataCollection/classes/class.ilDataCollectionField.php');
include_once('./Modules/DataCollection/classes/class.ilDataCollectionRecordViewViewdefinition.php');
/**
* Class ilDataCollectionRecordViewGUI
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
* @version $Id: 
*
* @ilCtrl_Calls ilDataCollectionRecordViewGUI: ilPageObjectGUI, ilEditClipboardGUI
*/


class ilDataCollectionRecordViewGUI
{
	function __construct($a_dcl_object)
	{
		global $lng, $tpl;
		
		$this->record_id = $_GET['record_id'];
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;

		$cmd = $ilCtrl->getCmd();

		switch($cmd)
		{
			default:
				$this->$cmd();
				break;
		}
	}
	
	
	/**
	* showRecord
	* a_val = 
	*/
	public function renderRecord()
	{
		global $ilTabs, $tpl;
		
		$ilTabs->setTabActive("id_content");

		$record_obj = new ilDataCollectionRecord($this->record_id);

		$pageObj = new ilPageObjectGUI("dclf", ilDataCollectionRecordViewViewdefinition::getIdByTableId($record_obj->getTableId()));

		$html = $pageObj->getHTML();
		$table = new ilDataCollectionTable($record_obj->getTableId());

		foreach($table->getFields() as $field)
		{
			$html = str_ireplace("[".$field->getTitle()."]", $record_obj->getRecordFieldHTML($field->getId()), $html);
		}

		$tpl->setContent($html);
	}
}

?>