<?php

/**
 * Class ilDclTableViewEditFieldsTableGUI
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @ingroup ModulesDataCollection
 */
class ilDclTableViewEditFieldsTableGUI extends ilTable2GUI
{
    public function __construct(ilDclTableViewEditGUI $a_parent_obj)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        parent::__construct($a_parent_obj);

        $this->setId('dcl_tableviews');
        $this->setTitle($lng->txt('dcl_tableview_fieldsettings'));
        $this->addColumn($lng->txt('dcl_fieldtitle'), "", 'auto');
        $this->addColumn($lng->txt('dcl_field_visible'), "", 'auto');
        $this->addColumn($lng->txt('dcl_filter'), "", 'auto');
        $this->addColumn($lng->txt('dcl_std_filter'), "", 'auto');
        $this->addColumn($lng->txt('dcl_filter_changeable'), "", 'auto');

        $ilCtrl->saveParameter($this, 'tableview_id');
        $this->setFormAction($ilCtrl->getFormActionByClass('ildcltablevieweditgui'));
        $this->addCommandButton('saveTable', $lng->txt('dcl_save'));

        $this->setExternalSegmentation(true);
        $this->setExternalSorting(true);

        $this->setRowTemplate('tpl.tableview_fields_row.html', 'Modules/DataCollection');
        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setShowRowsSelector(false);
        $this->setShowTemplates(false);
        $this->setEnableHeader(true);
        $this->setEnableTitle(true);
        $this->setDefaultOrderDirection('asc');

        $this->parseData($a_parent_obj->tableview->getFieldSettings());
    }

    public function parseData(array $data) : void
    {
        //enable/disable comments
        if (!$this->parent_obj->table->getPublicCommentsEnabled()) {
            foreach ($data as $key => $rec) {
                if ($rec->getField() == 'comments') {
                    unset($data[$key]);
                }
            }
        }
        $this->setData($data);
    }

    /**
     * Get HTML
     */
    public function getHTML() : string
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if ($this->getExportMode()) {
            $this->exportData($this->getExportMode(), true);
        }

        $this->prepareOutput();

        if (is_object($ilCtrl) && is_object($this->getParentObject()) && $this->getId() == "") {
            $ilCtrl->saveParameter($this->getParentObject(), $this->getNavParameter());
        }

        if (!$this->getPrintMode()) {
            // set form action
            if ($this->form_action != "" && $this->getOpenFormTag()) {
                $hash = "";

                if ($this->form_multipart) {
                    $this->tpl->touchBlock("form_multipart_bl");
                }

                if ($this->getPreventDoubleSubmission()) {
                    $this->tpl->touchBlock("pdfs");
                }

                $this->tpl->setCurrentBlock("tbl_form_header");
                $this->tpl->setVariable("FORMACTION", $this->getFormAction() . $hash);
                $this->tpl->setVariable("FORMNAME", $this->getFormName());
                $this->tpl->parseCurrentBlock();
            }

            if ($this->form_action != "" && $this->getCloseFormTag()) {
                $this->tpl->touchBlock("tbl_form_footer");
            }
        }

        if (!$this->enabled['content']) {
            return $this->render();
        }

        if (!$this->getExternalSegmentation()) {
            $this->setMaxCount(count($this->row_data));
        }

        $this->determineOffsetAndOrder();

        $this->setFooter("tblfooter", $this->lng->txt("previous"), $this->lng->txt("next"));

        $data = $this->getData();
        if ($this->dataExists()) {
            // sort
            if (!$this->getExternalSorting() && $this->enabled["sort"]) {
                $data = ilArrayUtil::sortArray(
                    $data,
                    $this->getOrderField(),
                    $this->getOrderDirection(),
                    $this->numericOrdering($this->getOrderField())
                );
            }

            // slice
            if (!$this->getExternalSegmentation()) {
                $data = array_slice($data, $this->getOffset(), $this->getLimit());
            }
        }

        // fill rows
        if ($this->dataExists()) {
            if ($this->getPrintMode()) {
                ilDatePresentation::setUseRelativeDates(false);
            }

            $this->tpl->addBlockFile(
                "TBL_CONTENT",
                "tbl_content",
                $this->row_template,
                $this->row_template_dir
            );

            foreach ($data as $set) {
                $this->tpl->setCurrentBlock("tbl_content");
                $this->css_row = ($this->css_row !== "tblrow1")
                    ? "tblrow1"
                    : "tblrow2";
                $this->tpl->setVariable("CSS_ROW", $this->css_row);

                $this->fillRowFromObject($set);
                $this->tpl->setCurrentBlock("tbl_content");
                $this->tpl->parseCurrentBlock();
            }
        } else {
            // add standard no items text (please tell me, if it messes something up, alex, 29.8.2008)
            $no_items_text = (trim($this->getNoEntriesText()) != '')
                ? $this->getNoEntriesText()
                : $lng->txt("no_items");

            $this->css_row = ($this->css_row !== "tblrow1")
                ? "tblrow1"
                : "tblrow2";

            $this->tpl->setCurrentBlock("tbl_no_entries");
            $this->tpl->setVariable('TBL_NO_ENTRY_CSS_ROW', $this->css_row);
            $this->tpl->setVariable('TBL_NO_ENTRY_COLUMN_COUNT', $this->column_count);
            $this->tpl->setVariable('TBL_NO_ENTRY_TEXT', trim($no_items_text));
            $this->tpl->parseCurrentBlock();
        }

        if (!$this->getPrintMode()) {
            $this->fillFooter();

            $this->fillHiddenRow();

            $this->fillActionRow();

            $this->storeNavParameter();
        }

        return $this->render();
    }

    /**
     * @param array $a_set
     */
    public function fillRowFromObject(object $a_set) : void
    {
        $field = $a_set->getFieldObject();
        if ($field->getId() == 'comments' && !$this->parent_obj->table->getPublicCommentsEnabled()) {
            return;
        }

        $this->tpl->setVariable('FIELD_TITLE', $field->getTitle());
        $this->tpl->setVariable('ID', $a_set->getId());
        $this->tpl->setVariable('FIELD_ID', $a_set->getField());
        $this->tpl->setVariable('VISIBLE', $a_set->isVisibleInList() ? 'checked' : '');
        if ($field->allowFilterInListView()) {
            $this->tpl->setVariable('IN_FILTER', $a_set->isInFilter() ? 'checked' : '');
            $this->tpl->setVariable('FILTER_VALUE', $this->getStandardFilterHTML($field, $a_set->getFilterValue()));
            $this->tpl->setVariable('FILTER_CHANGEABLE', $a_set->isFilterChangeable() ? 'checked' : '');
        } else {
            $this->tpl->setVariable('NO_FILTER', '');
        }
    }

    /**
     * @throws ilDclException
     */
    protected function getStandardFilterHTML(ilDclBaseFieldModel $field, array $value) : string
    {
        $field_representation = ilDclFieldFactory::getFieldRepresentationInstance($field);
        $field_representation->addFilterInputFieldToTable($this);
        $filter = end($this->filters);
        $this->filters = array();
        $filter->setValueByArray($value);

        return $filter->render();
    }
}
