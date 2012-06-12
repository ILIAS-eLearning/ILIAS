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
		$record_obj = new ilDataCollectionRecord(1);
		$pageObj = new ilPageObjectGUI("wpg", 2);
		//echo "<pre>".print_r(get_class_methods($pageObj), 1)."</pre>";
		$fields = $record_obj->getFieldvalues(1);
		//echo "<pre>".print_r($fields,1)."</pre>";
		
		$html = $pageObj->getHTML();

		foreach($record_obj->getFieldvalues(1) as $key => $value)
		{
			$html = str_ireplace("[FIELD".$key."]", $value, $html);
		}
		
		
		//echo $html;
		//echo "<pre>".print_r($pageObj,1)."</pre>";

/* /		echo $pageObj->read(); */
		
		//FIXME zum Testen zeige ich derzeit record Nr. 1 an.
		
		
		//DEBUG
		/*$html .= $record_obj->getTableId();
		$html .= "<br>".$record_obj->getLastUpdate();
		$html .= "<br>".$record_obj->getCreateDate();
		$html .= "<br>".$record_obj->getOwner();
		$html .= "<br>Dynmische Felder:";*/
		
		/*foreach($record_obj->getFieldvalues() as $key => $value)
		{
			$html .= "<br>".$key.": ".$value;
		}*/
	
		$tpl->setContent($html);
	}
}

?>