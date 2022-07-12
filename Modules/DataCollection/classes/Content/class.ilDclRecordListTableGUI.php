<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

/**
 * Class ilDclBaseFieldModel
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 * @ingroup ModulesDataCollection
 */
class ilDclRecordListTableGUI extends ilTable2GUI
{
    const EXPORT_EXCEL_ASYNC = 10;
    /** @var object|ilDclRecordListGUI|null */
    protected ?object $parent_obj;
    protected ilDclTable $table;
    protected ?ilDclTableView $tableview;
    /**
     * @var ilDclBaseRecordModel[]
     */
    protected array $object_data;
    protected array $numeric_fields = array();
    protected array $filter = array();
    protected int $mode;
    protected int $userId;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;

    public function __construct(
        ilDclRecordListGUI $a_parent_obj,
        string $a_parent_cmd,
        ilDclTable $table,
        int $tableview_id,
        int $mode = ilDclRecordListGUI::MODE_VIEW
    ) {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->userId = $DIC->user()->getId();
        $this->lng = $DIC->language();

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
            $this->addMultiCommand("confirmDeleteRecords", $this->lng->txt('dcl_delete_records'));
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
                $this->addColumn($this->lng->txt("dcl_status"), "_status_" . $field->getTitle());
            }
        }
        $this->addColumn($this->lng->txt("actions"), "", "30px");
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

        if (($this->table->getExportEnabled() || ilObjDataCollectionAccess::hasAccessToFields(
            $this->parent_obj->getRefId(),
            $this->table->getId()
        ))) {
            $this->setExportFormats(array(self::EXPORT_EXCEL, self::EXPORT_EXCEL_ASYNC));
        }

        $this->ctrl->saveParameter($a_parent_obj, 'tableview_id');
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, "applyFilter"));
        $this->setStyle('table', $this->getStyle('table') . ' ' . 'dcl_record_list');
    }

    /**
     * @description Return array of fields that are currently stored in the filter. Return empty array if no filtering is required.
     */
    public function getFilter() : array
    {
        return $this->filter;
    }

    public function setRecordData(array $data) : void
    {
        $this->object_data = $data;
        $this->buildData();
    }

    public function numericOrdering(string $a_field) : bool
    {
        return in_array($a_field, $this->numeric_fields);
    }

    /**
     * @description Parse data from record objects to an array that is then set to this table with ::setData()
     */
    private function buildData() : void
    {
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
                    $record_data['n_comments'] = $record->getNrOfComments();
                }
            }

            $this->ctrl->setParameterByClass("ildclfieldeditgui", "record_id", $record->getId());
            $this->ctrl->setParameterByClass("ilDclDetailedViewGUI", "record_id", $record->getId());
            $this->ctrl->setParameterByClass("ilDclDetailedViewGUI", "tableview_id", $this->tableview->getId());
            $this->ctrl->setParameterByClass("ildclrecordeditgui", "record_id", $record->getId());
            $this->ctrl->setParameterByClass("ildclrecordeditgui", "tableview_id", $this->tableview->getId());
            $this->ctrl->setParameterByClass("ildclrecordeditgui", "mode", $this->mode);

            if (ilDclDetailedViewDefinition::isActive($this->tableview->getId())) {
                $record_data["_front"] = $this->ctrl->getLinkTargetByClass("ilDclDetailedViewGUI", 'renderRecord');
            }

            $alist = new ilAdvancedSelectionListGUI();
            $alist->setId($record->getId());
            $alist->setListTitle($this->lng->txt("actions"));

            if (ilDclDetailedViewDefinition::isActive($this->tableview->getId())) {
                $alist->addItem(
                    $this->lng->txt('view'),
                    'view',
                    $this->ctrl->getLinkTargetByClass("ilDclDetailedViewGUI", 'renderRecord')
                );
            }

            if ($record->hasPermissionToEdit($this->parent_obj->getRefId())) {
                $alist->addItem(
                    $this->lng->txt('edit'),
                    'edit',
                    $this->ctrl->getLinkTargetByClass("ildclrecordeditgui", 'edit')
                );
            }

            if ($record->hasPermissionToDelete($this->parent_obj->getRefId())) {
                $alist->addItem(
                    $this->lng->txt('delete'),
                    'delete',
                    $this->ctrl->getLinkTargetByClass("ildclrecordeditgui", 'confirmDelete')
                );
            }

            if ($this->table->getPublicCommentsEnabled()) {
                $alist->addItem(
                    $this->lng->txt('dcl_comments'),
                    'comment',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $this->getCommentsAjaxLink($record->getId())
                );
            }

            $record_data["_actions"] = $alist->getHTML();
            $data[] = $record_data;
        }
        $this->setData($data);
    }

    protected function fillRow(array $a_set) : void
    {
        $record_obj = $a_set['_record'];

        /**
         * @var $record_obj ilDclBaseRecordModel
         * @var $ilAccess   ilAccessHandler
         */
        foreach ($this->tableview->getVisibleFields() as $field) {
            $title = $field->getTitle();
            $this->tpl->setCurrentBlock("field");
            $content = $a_set[$title];
            if ($content === false || $content === null) {
                $content = '';
            } // SW - This ensures to display also zeros in the table...

            $this->tpl->setVariable("CONTENT", $content);
            $this->tpl->parseCurrentBlock();

            if ($field->getProperty(ilDclBaseFieldModel::PROP_LEARNING_PROGRESS)) {
                $this->tpl->setCurrentBlock("field");
                $this->tpl->setVariable("CONTENT", $a_set["_status_" . $title]);
                $this->tpl->parseCurrentBlock();
            }
        }

        if ($a_set["_front"]) {
            $this->tpl->setCurrentBlock('view');
            $this->tpl->setVariable("VIEW_IMAGE_LINK", $a_set["_front"]);
            $this->tpl->setVariable(
                "VIEW_IMAGE_SRC",
                ilUtil::img(
                    ilUtil::getImagePath("enlarge.svg"),
                    $this->lng->txt('dcl_display_record_alt')
                )
            );
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setVariable("ACTIONS", $a_set["_actions"]);

        if ($this->mode == ilDclRecordListGUI::MODE_MANAGE) {
            if ($record_obj->hasPermissionToDelete($this->parent_obj->getRefId())) {
                $this->tpl->setCurrentBlock('mode_manage');
                $this->tpl->setVariable('RECORD_ID', $record_obj->getId());
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->touchBlock('mode_manage_no_owner');
            }
        }
    }

    /**
     * @description This adds the column for status.
     */
    protected function getStatus(ilDclBaseRecordModel $record, ilDclBaseFieldModel $field) : string
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
    public function initFilterFromTableView() : void
    {
        $this->filters = [];
        $this->filter = [];
        foreach ($this->tableview->getFilterableFieldSettings() as $field_set) {
            $field = $field_set->getFieldObject();
            ilDclCache::getFieldRepresentation($field)->addFilterInputFieldToTable($this);

            //set filter values
            $filter = end($this->filters);
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
    public function initFilter() : void
    {
        foreach ($this->tableview->getFilterableFieldSettings() as $field_set) {
            $field = $field_set->getFieldObject();
            $value = ilDclCache::getFieldRepresentation($field)->addFilterInputFieldToTable($this);

            //Disable filters
            $filter = end($this->filters);
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
     * @return string
     */
    public function loadProperty(string $type) : string
    {
        if ($this->getId() && $this->userId > 0) {
            $tab_prop = new ilTablePropertiesStorageGUI();
            return $tab_prop->getProperty($this->getId(), $this->userId, $type);
        }
        return "";
    }

    /**
     * @description Get the ajax link for displaying the comments in the right panel (to be wrapped in an onclick attr)
     */
    protected function getCommentsAjaxLink(int $recordId) : string
    {
        $ajax_hash = ilCommonActionDispatcherGUI::buildAjaxHash(
            1,
            $this->parent_obj->getRefId(),
            'dcl',
            $this->parent_obj->getObjId(),
            'dcl',
            $recordId
        );

        return ilNoteGUI::getListCommentsJSCall($ajax_hash, '');
    }

    /**
     * Exports the table
     */
    public function exportData(
        string $format,
        bool $send = false
    ) : void {
        if ($this->dataExists()) {
            $exporter = new ilDclContentExporter(
                $this->parent_obj->getRefId(),
                $this->table->getId(),
                $this->filter
            );
            $exporter->export(ilDclContentExporter::EXPORT_EXCEL, null, true);
        }
    }
}
