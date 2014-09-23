<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/Table/classes/class.ilTable2GUI.php';
require_once 'class.ilDataCollectionRecordViewGUI.php';
require_once 'class.ilDataCollectionField.php';
require_once './Services/Tracking/classes/class.ilLPStatus.php';
require_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
require_once 'class.ilDataCollectionDatatype.php';
require_once ('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');

/**
 * Class ilDataCollectionField
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Marcel Raimann <mr@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDataCollectionRecordListTableGUI  extends ilTable2GUI
{

    private $table;

    /**
     * @var ilDataCollectionRecord[]
     */
    protected $object_data;
    protected $numeric_fields;

    protected $filter = array();

    protected $mode;

    /**
     * @param ilDataCollectionRecordListGUI $a_parent_obj
     * @param string $a_parent_cmd
     * @param ilDataCollectionTable $table
     * @param int $mode
     */
    public function  __construct(ilDataCollectionRecordListGUI $a_parent_obj, $a_parent_cmd, ilDataCollectionTable $table, $mode=ilDataCollectionRecordListGUI::MODE_VIEW)
    {
        global $lng, $ilCtrl;

        $this->setPrefix("dcl_record_list");
        $this->setFormName('record_list');
        $this->setId("dcl_record_list" . $table->getId());
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->table = $table;
        $this->parent_obj = $a_parent_obj;
        $this->setRowTemplate("tpl.record_list_row.html", "Modules/DataCollection");
        $this->mode = $mode;

        // Setup columns and sorting columns
        if ($this->mode == ilDataCollectionRecordListGUI::MODE_MANAGE) {
            // Add checkbox columns
            $this->addColumn("", "", "1", true);
            $this->setSelectAllCheckbox("record_ids[]");
            $this->addMultiCommand("confirmDeleteRecords", $lng->txt('dcl_delete_records'));
        }
        $this->addColumn("", "_front", '15px');
        $this->numeric_fields = array();
        foreach($this->table->getVisibleFields() as $field)
        {
            $title = $field->getTitle();
            $sort_field = $title;
            if ($field->getId() == 'comments') {
                $sort_field = 'n_comments';
                $this->numeric_fields[] = $title;
            }
            $this->addColumn($title, $sort_field);
            if ($field->getLearningProgress()) {
                $this->addColumn($lng->txt("dcl_status"), "_status_".$field->getTitle());
            }
            if ($field->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_NUMBER) {
                $this->numeric_fields[] = $title;
            }
        }
        $this->addColumn($lng->txt("actions"), "", 	 "30px");

        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setShowRowsSelector(true);
        $this->setShowTemplates(true);
        $this->setEnableHeader(true);
        $this->setEnableTitle(true);
        $this->setDefaultOrderDirection($this->table->getDefaultSortFieldOrder());
        // Set a default sorting?
        $default_sort_title = 'id';
        if ($fieldId = $this->table->getDefaultSortField()) {
            if (ilDataCollectionStandardField::_isStandardField($fieldId)) {
                /** @var $stdField ilDataCollectionStandardField */
                foreach (ilDataCollectionStandardField::_getStandardFields($this->table->getId()) as $stdField) {
                    if ($stdField->getId() == $fieldId) {
                        $default_sort_title = $stdField->getTitle();
                        break;
                    }
                }
            } else {
                $default_sort_title = ilDataCollectionCache::getFieldCache($fieldId)->getTitle();
            }
            $this->setDefaultOrderField($default_sort_title);
        }
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, "applyFilter"));
        $this->initFilter();
        $this->setStyle('table', $this->getStyle('table') . ' ' . 'dcl_record_list');
    }

    /**
     * Return array of fields that are currently stored in the filter. Return empty array if no filtering is required.
     * @return array
     */
    public function getFilter() {
        return $this->filter;
    }

    public function setRecordData($data) {
        $this->object_data = $data;
        $this->buildData($data);
    }

    /*
     * fillHeaderExcel
     */
    public function fillHeaderExcel($worksheet, &$row)
    {
        $this->writeFilterToSession();
        $this->initFilter();
        $col = 0;

        foreach($this->table->getFields() as $field)
        {
            if($field->getExportable())
            {
                $worksheet->writeString($row, $col, $field->getTitle());
                $col++;
            }
        }
    }

    public function numericOrdering($field){
        return in_array($field, $this->numeric_fields);
    }

    /**
     * Parse data from record objects to an array that is then set to this table with ::setData()
     */
    private function buildData(){
        global $ilCtrl, $lng;

        $data = array();
        foreach($this->object_data as $record){
            $record_data = array();
            $record_data["_front"] = null;
            $record_data['_record'] = $record;

            foreach($this->table->getVisibleFields() as $field)
            {
                $title = $field->getTitle();
                $record_data[$title] = $record->getRecordFieldHTML($field->getId());

                // Additional column filled in ::fillRow() method, showing the learning progress
                if ($field->getLearningProgress()) {
                    $record_data["_status_".$title] = $this->getStatus($record, $field);
                }
                if ($field->getId() == 'comments') {
                    $record_data['n_comments'] = count($record->getComments());
                }
            }

            $ilCtrl->setParameterByClass("ildatacollectionfieldeditgui", "record_id", $record->getId());
            $ilCtrl->setParameterByClass("ildatacollectionrecordviewgui", "record_id", $record->getId());
            $ilCtrl->setParameterByClass("ildatacollectionrecordeditgui","record_id", $record->getId());

            if(ilDataCollectionRecordViewGUI::_getViewDefinitionId($record))
            {
                $record_data["_front"] = $ilCtrl->getLinkTargetByClass("ildatacollectionrecordviewgui", 'renderRecord');
            }

            $alist = new ilAdvancedSelectionListGUI();
            $alist->setId($record->getId());
            $alist->setListTitle($lng->txt("actions"));

            if(ilDataCollectionRecordViewGUI::_getViewDefinitionId($record))
            {
                $alist->addItem($lng->txt('view'), 'view', $ilCtrl->getLinkTargetByClass("ildatacollectionrecordviewgui", 'renderRecord'));
            }

            if($record->hasPermissionToEdit($this->parent_obj->parent_obj->ref_id))
            {
                $alist->addItem($lng->txt('edit'), 'edit', $ilCtrl->getLinkTargetByClass("ildatacollectionrecordeditgui", 'edit'));
            }

            if($record->hasPermissionToDelete($this->parent_obj->parent_obj->ref_id))
            {
                $alist->addItem($lng->txt('delete'), 'delete', $ilCtrl->getLinkTargetByClass("ildatacollectionrecordeditgui", 'confirmDelete'));
            }

            if ($this->table->getPublicCommentsEnabled()) {
                $alist->addItem($lng->txt('dcl_comments'), 'comment', '', '', '', '', '', '', $this->getCommentsAjaxLink($record->getId()));
            }

            $record_data["_actions"] = $alist->getHTML();
            $data[] = $record_data;
        }
        $this->setData($data);
    }


    /*
     * fillRowExcel
     */
    public function fillRowExcel($worksheet, &$row, $record)
    {
        $col = 0;
        foreach($this->table->getFields() as $field)
        {
            if($field->getExportable())
            {
                $worksheet->writeString($row, $col, $record["_record"]->getRecordFieldExportValue($field->getId()));
                $col++;
            }
        }
    }

    /**
     * fill row
     *
     * @access public
     * @param $a_set
     */
    public function fillRow($record_data)
    {
        foreach($this->table->getVisibleFields() as $field)
        {
            $title = $field->getTitle();
            $this->tpl->setCurrentBlock("field");
            $content = $record_data[$title];
            if ($content === false || $content === null) $content = ''; // SW - This ensures to display also zeros in the table...
            $this->tpl->setVariable("CONTENT", $content);
            $this->tpl->parseCurrentBlock();
            if($field->getLearningProgress()){
                $this->tpl->setCurrentBlock("field");
                $this->tpl->setVariable("CONTENT", $record_data["_status_".$title]);
                $this->tpl->parseCurrentBlock();
            }
        }

        if ($record_data["_front"])
        {
            $this->tpl->setVariable("VIEW_IMAGE_LINK", $record_data["_front"]);
            $this->tpl->setVariable("VIEW_IMAGE_SRC", ilUtil::img(ilUtil::getImagePath("cmd_view_s.png")));
        }
        $this->tpl->setVariable("ACTIONS", $record_data["_actions"]);

        if ($this->mode == ilDataCollectionRecordListGUI::MODE_MANAGE) {
            $this->tpl->setCurrentBlock('mode_manage');
            $this->tpl->setVariable('RECORD_ID', $record_data['_record']->getId());
            $this->tpl->parseCurrentBlock();
        }

        return true;
    }

    /**
     * This adds the collumn for status.
     * @param ilDataCollectionRecord $record
     * @param ilDataCollectionField $field
     */
    private function getStatus(ilDataCollectionRecord $record, ilDataCollectionField $field){
        $record_field = ilDataCollectionCache::getRecordFieldCache($record, $field);
        $return = "";
        if($status = $record_field->getStatus()){
            $return = "<img src='".ilLearningProgressBaseGUI::_getImagePathForStatus($status->status)."'>";
        }
        return $return;
    }

    /*
     * initFilter
     */
    public function initFilter()
    {
        foreach($this->table->getFilterableFields() as $field) {
            $input = ilDataCollectionDatatype::addFilterInputFieldToTable($field, $this);
            $input->readFromSession();
            $value = $input->getValue();
            if (is_array($value)) {
                if ($value['from'] || $value['to']) {
                    $this->filter["filter_".$field->getId()] = $value;
                }
            } else {
                if ($value != '') {
                    $this->filter["filter_".$field->getId()] = $value;
                }
            }
        }
    }

    /**
     * Get the ajax link for displaying the comments in the right panel (to be wrapped in an onclick attr)
     * @param int $recordId Record-ID
     * @return string
     */
    private function getCommentsAjaxLink($recordId) {
        $ajax_hash = ilCommonActionDispatcherGUI::buildAjaxHash(1, $_GET['ref_id'], 'dcl', $this->parent_obj->obj_id, 'dcl', $recordId);
        return ilNoteGUI::getListCommentsJSCall($ajax_hash, '');
    }

}

?>