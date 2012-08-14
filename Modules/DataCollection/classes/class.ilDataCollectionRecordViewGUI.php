<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


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
		global $ilTabs, $tpl, $ilCtrl, $lng;
		
		$ilTabs->setTabActive("id_content");
		
		//$html = 'Hier erscheint der einzelne gerenderte Record - ACHTUNG Design wird als HTML (via Killing) in DB hinterlegt sein.<br><br>';
		include_once('./Services/COPage/classes/class.ilPageObjectGUI.php');
		include_once('./Modules/DataCollection/classes/class.ilDataCollectionRecord.php');
		include_once('./Modules/DataCollection/classes/class.ilDataCollectionField.php');
		include_once('./Modules/DataCollection/classes/class.ilDataCollectionRecordViewViewdefinition.php');
		
		

		$record_obj = new ilDataCollectionRecord($this->record_id);

		$pageObj = new ilPageObjectGUI("dclf", ilDataCollectionRecordViewViewdefinition::getIdByTableId($record_obj->getTableId()));

		$html = $pageObj->getHTML();
		//echo "<pre>".print_r($record_obj->getRecordFieldValuesAsObject(),1)."</pre>";
		foreach($record_obj->getRecordFieldValuesAsObject() as $key => $value)
		{
			//echo "<pre>".print_r($value,1)."</pre>";
			$field_obj = new ilDataCollectionField($key);

			$html = str_ireplace("[".$field_obj->getTitle()."]", $value->getValue(), $html);
		}
		
		
		/*$allp = ilDataCollectionRecordViewViewdefinition::getAvailablePlaceholders($this->table_id, true);
		foreach($allp as $id => $item)
		{
			$parsed_item = new ilTextInputGUI("", "fields[".$item->getId()."]");
			$parsed_item = $parsed_item->getToolbarHTML();
			
			$a_output = str_replace($id, $item->getTitle().": ".$parsed_item, $a_output);
		}
		
		*/
		
		
		$tpl->setContent($html);
	}
}

?>