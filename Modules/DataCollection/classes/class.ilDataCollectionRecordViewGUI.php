<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('./Modules/DataCollection/classes/class.ilDataCollectionTable.php');
require_once('./Services/COPage/classes/class.ilPageObjectGUI.php');
require_once('./Modules/DataCollection/classes/class.ilDataCollectionRecord.php');
require_once('./Modules/DataCollection/classes/class.ilDataCollectionField.php');
require_once('./Modules/DataCollection/classes/class.ilDataCollectionRecordViewViewdefinition.php');



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
	 * @return int|NULL returns the id of the viewdefinition if one is declared and NULL otherwise
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
            //ILIAS_Ref_Links
            $pattern = '/\[dcliln field="'.$field->getTitle().'"\](.*?)\[\/dcliln\]/';
            if (preg_match($pattern,$html)) {
                $html = preg_replace($pattern, $this->record_obj->getRecordFieldHTML($field->getId(),$this->setOptions("$1")), $html);
            }

            //DataCollection Ref Links
            $pattern = '/\[dclrefln field="'.$field->getTitle().'"\](.*?)\[\/dclrefln\]/';
            if (preg_match($pattern,$html)) {
                $html = preg_replace($pattern, $this->record_obj->getRecordFieldHTML($field->getId(),$this->setOptions("$1")), $html);
            }


			$html = str_ireplace("[".$field->getTitle()."]", $this->record_obj->getRecordFieldHTML($field->getId()), $html);

		}

		$tpl->setContent($html);
	}


    /**
     * setOptions
     * string $link_name
     */
    private function setOptions($link_name)
    {
      $options = array();
      $options['link']['display'] = true;
      $options['link']['name'] = $link_name;
      return $options;
    }
}

?>