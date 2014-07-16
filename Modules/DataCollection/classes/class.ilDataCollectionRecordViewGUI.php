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

    /** @var  ilNoteGUI */
    protected $notesGui;

    /** @var  ilDataCollectionTable */
    protected $table;

    /** @var  ilDataCollectionRecord */
    protected $record_obj;

    protected $nextRecordId = 0;
    protected $prevRecordId = 0;
    protected $currentRecordPosition = 0;
    protected $recordIds = array();
    protected $isEnabledPaging = true;

    public function __construct($a_dcl_object)
    {
        global $tpl, $ilCtrl;
        $this->dcl_gui_object = $a_dcl_object;

        $this->record_id = (int) $_REQUEST['record_id'];
        $this->record_obj = ilDataCollectionCache::getRecordCache($this->record_id);

        if (!$this->record_obj->hasPermissionToView((int) $_GET['ref_id'])) {
            ilUtil::sendFailure('dcl_msg_no_perm_view', true);
            $ilCtrl->redirectByClass('ildatacollectionrecordlistgui', 'listRecords');
        }

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

        $this->table = $this->record_obj->getTable();

        // Comments
        include_once("./Services/Notes/classes/class.ilNoteGUI.php");
        $repId = $this->dcl_gui_object->getDataCollectionObject()->getId();
        $objId = (int) $this->record_id;
        $this->notesGUI = new ilNoteGUI($repId, $objId);
        $this->notesGUI->enablePublicNotes(true);
        $this->notesGUI->enablePublicNotesDeletion(true);
        $ilCtrl->setParameterByClass("ilnotegui", "record_id", $this->record_id);
        $ilCtrl->setParameterByClass("ilnotegui", "rep_id", $repId);

        if (isset($_GET['disable_paging']) && $_GET['disable_paging']) {
            $this->isEnabledPaging = false;
        }
        // Find current, prev and next records for navigation
        if ($this->isEnabledPaging) {
            $this->determineNextPrevRecords();
        }
    }

    /**
     * execute command
     */
    public function &executeCommand()
    {
        global $ilCtrl;

        $cmd = $ilCtrl->getCmd();
        $cmdClass = $ilCtrl->getCmdClass();
        switch ($cmdClass) {
            case 'ilnotegui':
                switch($cmd)
                {
                    case 'editNoteForm':
                        $this->renderRecord(true);
                        break;
                    case 'showNotes':
                        $this->renderRecord(false);
                        break;
                    case 'deleteNote':
                        $this->notesGUI->deleteNote();
                        $this->renderRecord();
                        break;
                    case 'cancelDelete':
                        $this->notesGUI->cancelDelete();
                        $this->renderRecord();
                        break;
                    default:
                        $this->notesGUI->$cmd();
                        break;
                }
                break;
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
    public function renderRecord($editComments=false)
    {
        global $ilTabs, $tpl, $ilCtrl, $lng;

        $rctpl = new ilTemplate("tpl.record_view.html", false, true, "Modules/DataCollection");

        $ilTabs->setTabActive("id_content");

        $view_id = self::_getViewDefinitionId($this->record_obj);

        if(!$view_id){
            $ilCtrl->redirectByClass("ildatacollectionrecordlistgui", "listRecords");
        }

        // see ilObjDataCollectionGUI->executeCommand about instantiation
        include_once("./Modules/DataCollection/classes/class.ilDataCollectionRecordViewViewdefinitionGUI.php");
        $pageObj = new ilDataCollectionRecordViewViewdefinitionGUI($this->record_obj->getTableId(), $view_id);
        include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
        $pageObj->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(0, "dcl"));


        $html = $pageObj->getHTML();
        $rctpl->addCss("./Services/COPage/css/content.css");
        $rctpl->fillCssFiles();
        $table = ilDataCollectionCache::getTableCache($this->record_obj->getTableId());
        foreach($table->getRecordFields() as $field)
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

            $html = str_ireplace("[".$field->getTitle()."]", $this->record_obj->getRecordFieldSingleHTML($field->getId()), $html);

        }
        foreach($table->getStandardFields() as $field) {
            $html = str_ireplace("[".$field->getId()."]", $this->record_obj->getRecordFieldSingleHTML($field->getId()), $html);
        }
        $rctpl->setVariable("CONTENT",$html);

        //Permanent Link
        include_once("./Services/PermanentLink/classes/class.ilPermanentLinkGUI.php");
        $perma_link = new ilPermanentLinkGUI("dcl", $_GET["ref_id"], "_".$_GET['record_id']);
        $rctpl->setVariable("PERMA_LINK", $perma_link->getHTML());

        // Buttons for previous/next records

        if ($this->isEnabledPaging) {
            $prevNextLinks = $this->renderPrevNextLinks();
            $rctpl->setVariable('PREV_NEXT_RECORD_LINKS', $prevNextLinks);
            $rctpl->setVariable('FORM_ACTION', $ilCtrl->getFormAction($this));
            $rctpl->setVariable('RECORD', $lng->txt('dcl_record'));
            $rctpl->setVariable('RECORD_FROM_TOTAL', sprintf($lng->txt('dcl_record_from_total'), $this->currentRecordPosition, count($this->recordIds)));
            $rctpl->setVariable('SELECT_OPTIONS', $this->renderSelectOptions());
        }

        // Comments
        if ($this->table->getPublicCommentsEnabled()) {
            $rctpl->setVariable('COMMENTS', $this->renderComments($editComments));
        }

        $tpl->setContent($rctpl->get());
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

    protected function renderComments($edit=false) {

        if (!$edit) {
            return $this->notesGUI->getOnlyCommentsHtml();
        } else {
            return $this->notesGUI->editNoteForm();
        }

    }

    /**
     * Find the previous/next record from the current position. Also determine position of current record in whole set.
     */
    protected function determineNextPrevRecords() {
        if (isset($_SESSION['dcl_record_ids']) && count($_SESSION['dcl_record_ids'])) {
            $this->recordIds = $_SESSION['dcl_record_ids'];
            foreach ($this->recordIds as $k => $recId) {
                if ($recId == $this->record_id) {
                    if ($k != 0) $this->prevRecordId = $this->recordIds[$k-1];
                    if (($k+1) < count($this->recordIds)) $this->nextRecordId = $this->recordIds[$k+1];
                    $this->currentRecordPosition = $k+1;
                    break;
                }
            }
        }
    }

    /**
     * Determine and return the markup for the previous/next records
     * @return string
     */
    protected function renderPrevNextLinks() {
        global $ilCtrl, $lng;
        $prevStr = $lng->txt('dcl_prev_record');
        $nextStr = $lng->txt('dcl_next_record');
        $ilCtrl->setParameter($this, 'record_id', $this->prevRecordId);
        $url = $ilCtrl->getLinkTarget($this, 'renderRecord');
        $out = ($this->prevRecordId) ? "<a href='{$url}'>{$prevStr}</a>" : "<span class='light'>{$prevStr}</span>";
        $out .= " | ";
        $ilCtrl->setParameter($this, 'record_id', $this->nextRecordId);
        $url = $ilCtrl->getLinkTarget($this, 'renderRecord');
        $out .= ($this->nextRecordId) ? "<a href='{$url}'>{$nextStr}</a>" : "<span class='light'>{$nextStr}</span>";
        return $out;
    }

    /**
     * Render select options
     * @return string
     */
    protected function renderSelectOptions() {
        $out = '';
        foreach ($this->recordIds as $k => $recId) {
            $selected = ($recId == $this->record_id) ? " selected" : "";
            $out .= "<option value='{$recId}'{$selected}>" . ($k+1) . "</option>";
        }
        return $out;
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