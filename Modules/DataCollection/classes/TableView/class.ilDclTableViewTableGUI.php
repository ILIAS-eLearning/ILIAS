<?php

/**
 * Class ilDclTableViewTableGUI
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @ingroup ModulesDataCollection
 */
class ilDclTableViewTableGUI extends ilTable2GUI
{
    protected ilDclTable $table;

    /**
     * ilDclTableViewTableGUI constructor.
     * @param object     $a_parent_obj //object|ilDclTableViewGUI
     * @param string     $a_parent_cmd
     * @param ilDclTable $table
     */
    public function __construct(object $a_parent_obj, $a_parent_cmd, ilDclTable $table)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->parent_obj = $a_parent_obj;
        $this->table = $table;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;

        $this->setExternalSegmentation(true);
        $this->setExternalSorting(true);

        if ($this->parent_obj instanceof ilDclTableViewGUI) {
            $ilCtrl->setParameterByClass('ildcltableviewgui', 'table_id', $table->getId());
            $this->setFormAction($ilCtrl->getFormActionByClass('ildcltableviewgui'));
            $this->addMultiCommand('confirmDeleteTableviews', $lng->txt('dcl_delete_views'));
            $this->addCommandButton('saveTableViewOrder', $lng->txt('dcl_save_order'));

            $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
            $this->setFormName('tableview_list');

            $this->addColumn('', '', '1', true);
            $this->addColumn($lng->txt('dcl_order'), '', '30px');

            $this->setRowTemplate('tpl.tableview_list_row.html', 'Modules/DataCollection');
            $this->setData($this->table->getTableViews());
        } elseif ($this->parent_obj instanceof ilDclDetailedViewGUI) {
            $this->setRowTemplate('tpl.detailview_list_row.html', 'Modules/DataCollection');
            $this->setData($this->table->getVisibleTableViews($this->parent_obj->parent_obj->ref_id, true));
        }

        $this->addColumn($lng->txt('title'), '', 'auto');
        $this->addColumn($lng->txt('description'), '', 'auto');
        $this->addColumn($lng->txt('dcl_configuration_complete'), '', 'auto');
        $this->addColumn($lng->txt('actions'), '', '30px');

        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setShowRowsSelector(false);
        $this->setShowTemplates(false);
        $this->setEnableHeader(true);
        $this->setEnableTitle(true);
        $this->setDefaultOrderDirection('asc');
        $this->setLimit(0);

        $this->setId('dcl_tableviews');
        $this->setTitle($lng->txt("dcl_tableviews_table"));
        $this->setStyle('table', $this->getStyle('table') . ' ' . 'dcl_record_list');
    }

    /**
     * Get HTML
     */
    public function getHTML(): string
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
     * @param ilDclTableView $a_set
     */
    public function fillRowFromObject(ilDclTableView $a_set): void
    {
        if ($this->parent_obj instanceof ilDclTableViewGUI) {
            $this->tpl->setVariable("ID", $a_set->getId());
            $this->tpl->setVariable("ORDER_NAME", "order[{$a_set->getId()}]");
            $this->tpl->setVariable("ORDER_VALUE", $a_set->getOrder());
        }
        $this->tpl->setVariable("TITLE", $a_set->getTitle());
        $this->ctrl->setParameterByClass('ilDclTableViewEditGUI', 'tableview_id', $a_set->getId());
        $this->tpl->setVariable("TITLE_LINK", $this->ctrl->getLinkTargetByClass('ilDclTableViewEditGUI'));
        $this->tpl->setVariable("DESCRIPTION", $a_set->getDescription());
        $this->tpl->setVariable(
            "DCL_CONFIG",
            $a_set->validateConfigCompletion() ? ilUtil::getImagePath(
                'icon_ok_monochrome.svg',
                "/Modules/DataCollection"
            ) : ilUtil::getImagePath(
                'icon_not_ok_monochrome.svg',
                "/Modules/DataCollection"
            )
        );
        $this->tpl->setVariable('ACTIONS', $this->buildAction($a_set->getId()));
    }

    /**
     * build either actions menu or view button
     */
    protected function buildAction(int $id): string
    {
        if ($this->parent_obj instanceof ilDclTableViewGUI) {
            $alist = new ilAdvancedSelectionListGUI();
            $alist->setId($id);
            $alist->setListTitle($this->lng->txt('actions'));
            $this->ctrl->setParameterByClass('ildcltableviewgui', 'tableview_id', $id);
            $this->ctrl->setParameterByClass('ilDclDetailedViewDefinitionGUI', 'tableview_id', $id);
            $alist->addItem(
                $this->lng->txt('edit'),
                '',
                $this->ctrl->getLinkTargetByClass('ildcltablevieweditgui', 'editGeneralSettings')
            );
            $alist->addItem(
                $this->lng->txt('copy'),
                '',
                $this->ctrl->getLinkTargetByClass('ildcltablevieweditgui', 'copy')
            );
            $alist->addItem(
                $this->lng->txt('delete'),
                '',
                $this->ctrl->getLinkTargetByClass('ildcltablevieweditgui', 'confirmDelete')
            );

            return $alist->getHTML();
        } elseif ($this->parent_obj instanceof ilDclDetailedViewGUI) {
            $button = ilDclLinkButton::getInstance();
            $this->ctrl->setParameterByClass('ilDclDetailedViewGUI', 'tableview_id', $id);
            $this->ctrl->saveParameterByClass('ilDclDetailedViewGUI', 'record_id');
            $button->setUrl($this->ctrl->getLinkTargetByClass('ilDclDetailedViewGUI', 'renderRecord'));
            $button->setCaption('view');

            return $button->getToolbarHTML();
        }
        return "";
    }
}
