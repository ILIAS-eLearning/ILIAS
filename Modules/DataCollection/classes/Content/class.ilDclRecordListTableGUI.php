<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclBaseFieldModel
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDclRecordListTableGUI extends ilTable2GUI
{
    const EXPORT_EXCEL_ASYNC = 10;
    /**
     * @var ilDclTable
     */
    protected $table;
    /**
     * @var ilDclTableView
     */
    protected $tableview;
    /**
     * @var ilDclBaseRecordModel[]
     */
    protected $object_data;
    /**
     * @var array
     */
    protected $numeric_fields = array();
    /**
     * @var array
     */
    protected $filter = array();
    /**
     * @var int
     */
    protected $mode;


    /**
     * @param ilDclRecordListGUI $a_parent_obj
     * @param string             $a_parent_cmd
     * @param ilDclTable         $table
     * @param int                $mode
     */
    public function __construct(ilDclRecordListGUI $a_parent_obj, $a_parent_cmd, ilDclTable $table, $tableview_id, $mode = ilDclRecordListGUI::MODE_VIEW)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->tableview = ilDclTableView::find($tableview_id);
        $identifier = 'dcl_rl_' . $table->getId() . '_' . $tableview_id;
        $this->setPrefix($identifier);
        $this->setFormName($identifier);
        $this->setId($identifier);
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->table = $table;
        $this->parent_obj = $a_parent_obj;
        $this->setRowTemplate("tpl.record_list_row.html", "Modules/DataCollection");
        $this->mode = $mode;

        // Setup columns and sorting columns
        if ($this->mode == ilDclRecordListGUI::MODE_MANAGE) {
            // Add checkbox columns
            $this->addColumn("", "", "1", true);
            $this->setSelectAllCheckbox("record_ids[]");
            $this->addMultiCommand("confirmDeleteRecords", $lng->txt('dcl_delete_records'));
        }

        if (ilDclDetailedViewDefinition::isActive($this->tableview->getId())) {
            $this->addColumn("", "_front", '15px');
        }

        $this->numeric_fields = array();
        foreach ($this->tableview->getVisibleFields() as $field) {
            $title = $field->getTitle();
            $sort_field = ($field->getRecordQuerySortObject() != null) ? $field->getSortField() : '';

            if ($field->hasNumericSorting()) {
                $this->numeric_fields[] = $title;
            }

            $this->addColumn($title, $sort_field);

            if ($field->hasProperty(ilDclBaseFieldModel::PROP_LEARNING_PROGRESS)) {
                $this->addColumn($lng->txt("dcl_status"), "_status_" . $field->getTitle());
            }
        }
        $this->addColumn($lng->txt("actions"), "", "30px");
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
            if (ilDclStandardField::_isStandardField($fieldId)) {
                /** @var $stdField ilDclStandardField */
                foreach (ilDclStandardField::_getStandardFields($this->table->getId()) as $stdField) {
                    if ($stdField->getId() == $fieldId) {
                        $default_sort_title = $stdField->getTitle();
                        break;
                    }
                }
            } else {
                $default_sort_title = ilDclCache::getFieldCache($fieldId)->getTitle();
            }
            $this->setDefaultOrderField($default_sort_title);
        }

        if (($this->table->getExportEnabled() || ilObjDataCollectionAccess::hasAccessToFields($this->parent_obj->parent_obj->object->getRefId(), $this->table->getId()))) {
            $this->setExportFormats(array(self::EXPORT_EXCEL, self::EXPORT_EXCEL_ASYNC));
        }

        $ilCtrl->saveParameter($a_parent_obj, 'tableview_id');
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, "applyFilter"));
        $this->setStyle('table', $this->getStyle('table') . ' ' . 'dcl_record_list');
    }


    /**
     * @description Return array of fields that are currently stored in the filter. Return empty array if no filtering is required.
     *
     * @return array
     */
    public function getFilter()
    {
        return $this->filter;
    }


    public function setRecordData($data)
    {
        $this->object_data = $data;
        $this->buildData($data);
    }


    public function numericOrdering($field)
    {
        return in_array($field, $this->numeric_fields);
    }


    /**
     * @description Parse data from record objects to an array that is then set to this table with ::setData()
     */
    private function buildData()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $data = array();
        foreach ($this->object_data as $record) {
            $record_data = array();
            $record_data["_front"] = null;
            $record_data['_record'] = $record;

            foreach ($this->tableview->getVisibleFields() as $field) {
                $title = $field->getTitle();
                $record_data[$title] = $record->getRecordFieldHTML($field->getId());

                // Additional column filled in ::fillRow() method, showing the learning progress
                if ($field->getProperty(ilDclBaseFieldModel::PROP_LEARNING_PROGRESS)) {
                    $record_data["_status_" . $title] = $this->getStatus($record, $field);
                }

                if ($field->getId() == 'comments') {
                    $record_data['n_comments'] = count($record->getComments());
                }
            }

            $ilCtrl->setParameterByClass("ildclfieldeditgui", "record_id", $record->getId());
            $ilCtrl->setParameterByClass("ilDclDetailedViewGUI", "record_id", $record->getId());
            $ilCtrl->setParameterByClass("ilDclDetailedViewGUI", "tableview_id", $this->tableview->getId());
            $ilCtrl->setParameterByClass("ildclrecordeditgui", "record_id", $record->getId());
            $ilCtrl->setParameterByClass("ildclrecordeditgui", "tableview_id", $this->tableview->getId());
            $ilCtrl->setParameterByClass("ildclrecordeditgui", "mode", $this->mode);

            if (ilDclDetailedViewDefinition::isActive($this->tableview->getId())) {
                $record_data["_front"] = $ilCtrl->getLinkTargetByClass("ilDclDetailedViewGUI", 'renderRecord');
            }

            $alist = new ilAdvancedSelectionListGUI();
            $alist->setId($record->getId());
            $alist->setListTitle($lng->txt("actions"));

            if (ilDclDetailedViewDefinition::isActive($this->tableview->getId())) {
                $alist->addItem($lng->txt('view'), 'view', $ilCtrl->getLinkTargetByClass("ilDclDetailedViewGUI", 'renderRecord'));
            }

            if ($record->hasPermissionToEdit($this->parent_obj->parent_obj->ref_id)) {
                $alist->addItem($lng->txt('edit'), 'edit', $ilCtrl->getLinkTargetByClass("ildclrecordeditgui", 'edit'));
            }

            if ($record->hasPermissionToDelete($this->parent_obj->parent_obj->ref_id)) {
                $alist->addItem($lng->txt('delete'), 'delete', $ilCtrl->getLinkTargetByClass("ildclrecordeditgui", 'confirmDelete'));
            }

            if ($this->table->getPublicCommentsEnabled()) {
                $alist->addItem($lng->txt('dcl_comments'), 'comment', '', '', '', '', '', '', $this->getCommentsAjaxLink($record->getId()));
            }

            $record_data["_actions"] = $alist->getHTML();
            $data[] = $record_data;
        }
        $this->setData($data);
    }


    /**
     * @param array $record_data
     *
     * @return bool|void
     */
    public function fillRow($record_data)
    {
        $record_obj = $record_data['_record'];

        /**
         * @var $record_obj ilDclBaseRecordModel
         * @var $ilAccess   ilAccessHandler
         */
        foreach ($this->tableview->getVisibleFields() as $field) {
            $title = $field->getTitle();
            $this->tpl->setCurrentBlock("field");
            $content = $record_data[$title];
            if ($content === false || $content === null) {
                $content = '';
            } // SW - This ensures to display also zeros in the table...

            $this->tpl->setVariable("CONTENT", $content);
            $this->tpl->parseCurrentBlock();

            if ($field->getProperty(ilDclBaseFieldModel::PROP_LEARNING_PROGRESS)) {
                $this->tpl->setCurrentBlock("field");
                $this->tpl->setVariable("CONTENT", $record_data["_status_" . $title]);
                $this->tpl->parseCurrentBlock();
            }
        }

        if ($record_data["_front"]) {
            $this->tpl->setCurrentBlock('view');
            $this->tpl->setVariable("VIEW_IMAGE_LINK", $record_data["_front"]);
            $this->tpl->setVariable("VIEW_IMAGE_SRC", ilUtil::img(ilUtil::getImagePath("enlarge.svg"), $this->lng->txt('dcl_display_record_alt')));
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setVariable("ACTIONS", $record_data["_actions"]);

        if ($this->mode == ilDclRecordListGUI::MODE_MANAGE) {
            if ($record_obj->hasPermissionToDelete($this->parent_obj->parent_obj->ref_id)) {
                $this->tpl->setCurrentBlock('mode_manage');
                $this->tpl->setVariable('RECORD_ID', $record_obj->getId());
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->touchBlock('mode_manage_no_owner');
            }
        }

        return true;
    }


    /**
     * @description This adds the collumn for status.
     *
     * @param ilDclBaseRecordModel $record
     * @param ilDclBaseFieldModel  $field
     *
     * @return string
     */
    protected function getStatus(ilDclBaseRecordModel $record, ilDclBaseFieldModel $field)
    {
        $record_field = ilDclCache::getRecordFieldCache($record, $field);
        $return = "";
        if ($status = $record_field->getStatus()) {
            $return = "<img src='" . ilLearningProgressBaseGUI::_getImagePathForStatus($status->status) . "'>";
        }

        return $return;
    }


    /**
     * init filters with values from tableview
     */
    public function initFilterFromTableView()
    {
        $this->filters = [];
        $this->filter = [];
        foreach ($this->tableview->getFilterableFieldSettings() as $field_set) {
            $field = $field_set->getFieldObject();
            ilDclCache::getFieldRepresentation($field)->addFilterInputFieldToTable($this);

            //set filter values
            $filter = &end($this->filters);
            $value = $field_set->getFilterValue();
            $filter->setValueByArray($value);
            $this->applyFilter($field->getId(), empty(array_filter($value)) ? null : $filter->getValue());

            //Disable filters
            if (!$field_set->isFilterChangeable()) {
                $filter->setDisabled(true);
                if ($filter instanceof ilCombinationInputGUI) {
                    $filter->__call('setDisabled', array(true));
                }
            }
        }
    }


    /**
     * normally initialize filters - used by applyFilter and resetFilter
     */
    public function initFilter()
    {
        foreach ($this->tableview->getFilterableFieldSettings() as $field_set) {
            $field = $field_set->getFieldObject();
            $value = ilDclCache::getFieldRepresentation($field)->addFilterInputFieldToTable($this);

            //Disable filters
            $filter = &end($this->filters);
            if (!$field_set->isFilterChangeable()) {
                //always set tableview-filtervalue with disabled fields, so resetFilter won't reset it
                $value = $field_set->getFilterValue();
                $filter->setValueByArray($value);
                $value = $filter->getValue();

                $filter->setDisabled(true);
                if ($filter instanceof ilCombinationInputGUI) {
                    $filter->__call('setDisabled', array(true));
                }
            }

            $this->applyFilter($field->getId(), $value);
        }
    }


    public function applyFilter($field_id, $filter_value)
    {
        if ($filter_value) {
            $this->filter["filter_" . $field_id] = $filter_value;
        }
    }


    /**
     * @param string $type
     *
     * @return mixed
     */
    public function loadProperty($type)
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        if ($ilUser instanceof ilObjUser and $this->getId()) {
            $tab_prop = new ilTablePropertiesStorage();

            return $tab_prop->getProperty($this->getId(), $ilUser->getId(), $type);
        }
    }


    /**
     * @description Get the ajax link for displaying the comments in the right panel (to be wrapped in an onclick attr)
     *
     * @param int $recordId Record-ID
     *
     * @return string
     */
    protected function getCommentsAjaxLink($recordId)
    {
        $ajax_hash = ilCommonActionDispatcherGUI::buildAjaxHash(1, $_GET['ref_id'], 'dcl', $this->parent_obj->obj_id, 'dcl', $recordId);

        return ilNoteGUI::getListCommentsJSCall($ajax_hash, '');
    }


    /**
     * Exports the table
     *
     * @param int         $format
     * @param bool|false  $send
     * @param null|string $filepath
     *
     * @return null|string
     */
    public function exportData($format, $send = false, $filepath = null)
    {
        if ($this->dataExists()) {
            // #9640: sort
            /*if (!$this->getExternalSorting() && $this->enabled["sort"]) {
                $this->determineOffsetAndOrder(true);

                $this->row_data = ilUtil::sortArray($this->row_data, $this->getOrderField(), $this->getOrderDirection(), $this->numericOrdering($this->getOrderField()));
            }*/

            $exporter = new ilDclContentExporter($this->parent_obj->parent_obj->object->getRefId(), $this->table->getId(), $this->filter);
            $exporter->export(ilDclContentExporter::EXPORT_EXCEL, null, true);
        }
    }
}
