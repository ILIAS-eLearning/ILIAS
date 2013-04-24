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
    /**
     * @var ilObjDataCollectionGUI
     */
    protected $dcl_gui_object;

	public function __construct($a_dcl_object)
	{
        global $tpl;

        $this->dcl_gui_object = $a_dcl_object;

		$this->record_id = $_GET['record_id'];
		$this->record_obj = ilDataCollectionCache::getRecordCache($this->record_id);

        // content style (using system defaults)
        include_once("./Services/Style/classes/class.ilObjStyleSheet.php");

        $tpl->setCurrentBlock("SyntaxStyle");
        $tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
            ilObjStyleSheet::getSyntaxStylePath());
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("ContentStyle");
        $tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath(0));
        $tpl->parseCurrentBlock();
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
		global $ilTabs, $tpl, $ilCtrl, $lng;
		
		$ilTabs->setTabActive("id_content");

		$view_id = self::_getViewDefinitionId($this->record_obj);

		if(!$view_id){
			$ilCtrl->redirectByClass("ildatacollectionrecordlistgui", "listRecords");
		}

		// please do not use ilPageObjectGUI directly here, use derived class
		// ilDataCollectionRecordViewViewdefinitionGUI
		
		//$pageObj = new ilPageObjectGUI("dclf", $view_id);
		
		// see ilObjDataCollectionGUI->executeCommand about instantiation
		include_once("./Modules/DataCollection/classes/class.ilDataCollectionRecordViewViewdefinitionGUI.php");
		$pageObj = new ilDataCollectionRecordViewViewdefinitionGUI($this->record_obj->getTableId(), $view_id);
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$pageObj->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(0, "dcl"));
		

		$html = $pageObj->getHTML();
        $tpl->addCss("./Services/COPage/css/content.css");
        $tpl->fillCssFiles();
		$table = ilDataCollectionCache::getTableCache($this->record_obj->getTableId());
		foreach($table->getFields() as $field)
		{
            //ILIAS_Ref_Links
            $pattern = '/\[dcliln field="'.preg_quote($field->getTitle(), "/").'"\](.*?)\[\/dcliln\]/';
            if (preg_match($pattern,$html)) {
                $html = preg_replace($pattern, $this->record_obj->getRecordFieldSingleHTML($field->getId(),$this->setOptions("$1")), $html);
            }

            //DataCollection Ref Links
            $pattern = '/\[dclrefln field="'.preg_quote($field->getTitle(), "/").'"\](.*?)\[\/dclrefln\]/';
            if (preg_match($pattern ,$html)) {
                $this->currentField = $field;
                $html = preg_replace_callback($pattern, array($this, "doReplace"), $html);
            }

            $pattern = '/\[ext tableOf="'.preg_quote($field->getTitle(), "/").'" field="(.*?)"\]/';
            if (preg_match($pattern ,$html)) {
                $this->currentField = $field;
                $html = preg_replace_callback($pattern, array($this, "doExtReplace"), $html);
            }

			$html = str_ireplace("[".$field->getTitle()."]", $this->record_obj->getRecordFieldHTML($field->getId()), $html);

		}

		$tpl->setContent($html);
	}

    public function doReplace($found){
        return $this->record_obj->getRecordFieldSingleHTML($this->currentField->getId(),$this->setOptions($found[1]));
    }

    public function doExtReplace($found){
        $ref_rec_ids = $this->record_obj->getRecordFieldValue($this->currentField->getId());
        if(!is_array($ref_rec_ids))
            $ref_rec_ids = array($ref_rec_ids);
        if(!count($ref_rec_ids) || !$ref_rec_ids)
            return;
        $ref_recs = array();
        foreach($ref_rec_ids as $ref_rec_id)
            $ref_recs[] = ilDataCollectionCache::getRecordCache($ref_rec_id);
        $field = $ref_recs[0]->getTable()->getFieldByTitle($found[1]);

        $tpl = new ilTemplate("tpl.reference_list.html", true, true, "Modules/DataCollection");
        $tpl->setCurrentBlock("reference_list");

        if(!$field){
            if(ilObjDataCollection::_hasWriteAccess($this->dcl_gui_object->ref_id))
                ilUtil::sendInfo("Bad Viewdefinition at [ext tableOf=\"".$found[1]."\" ...]", true);
            return;
        }

        foreach($ref_recs as $ref_record){
                $tpl->setCurrentBlock("reference");
                $tpl->setVariable("CONTENT", $ref_record->getRecordFieldHTML($field->getId()));
                $tpl->parseCurrentBlock();
        }

        //$ref_rec->getRecordFieldHTML($field->getId())
        if($field)
            return $tpl->get();
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