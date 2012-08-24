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
	public function __construct($a_dcl_object)
	{
		$this->record_id = $_GET['record_id'];
		$this->record_obj = new ilDataCollectionRecord($this->record_id);
	}

	/**
	 * execute command
	 */
	public function &executeCommand()
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
	 * @param $record_obj ilDataCollectionRecord
	 * @return int|null returns the id of the viewdefinition if one is declared and null otherwise
	 */
	public static function _getViewDefinitionId($record_obj)
	{
		return ilDataCollectionRecordViewViewdefinition::getIdByTableId($record_obj->getTableId());
	}

	/**
	 * showRecord
	 * a_val = 
	 */
	public function renderRecord()
	{
		global $ilTabs, $tpl, $ilCtrl;
		
		$ilTabs->setTabActive("id_content");

		$view_id = self::_getViewDefinitionId($this->record_obj);

		if(!$view_id){
			$ilCtrl->redirectByClass("ildatacollectionrecordlistgui", "listRecords");
		}

		$pageObj = new ilPageObjectGUI("dclf", $view_id);

		$html = $pageObj->getHTML();
		$table = new ilDataCollectionTable($this->record_obj->getTableId());
		foreach($table->getFields() as $field)
		{
			$html = str_ireplace("[".$field->getTitle()."]", $this->record_obj->getRecordFieldHTML($field->getId()), $html);
		}

		$tpl->setContent($html);
	}
}

?>