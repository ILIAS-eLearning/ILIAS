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
 * Class ilDclTableListTableGUI
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilDclTableListTableGUI extends ilTable2GUI
{
    /**
     * ilDclTableListTableGUI constructor.
     */
    public function __construct(ilDclTableListGUI $parent_obj)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        parent::__construct($parent_obj);

        $this->parent_obj = $parent_obj;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;

        $this->setId('dcl_table_list');
        $this->addColumn('', '', '1', true);
        $this->addColumn($lng->txt('dcl_order'), "", '30px');
        $this->addColumn($lng->txt('title'), "", 'auto');
        $this->addColumn($lng->txt('dcl_visible'), "", '250px', false, '', $this->lng->txt('dcl_visible_desc'));
        $this->addColumn($lng->txt('dcl_comments'), "", '200px', false, '',
            $this->lng->txt('dcl_public_comments_desc'));
        $this->addColumn($lng->txt('actions'), "", '30px');

        $this->setSelectAllCheckbox('dcl_table_ids[]');
        $this->addMultiCommand('confirmDeleteTables', $lng->txt('dcl_delete_tables'));

        $this->setFormAction($ilCtrl->getFormActionByClass('ildcltablelistgui'));
        $this->addCommandButton('save', $lng->txt('dcl_save'));

        $this->setFormAction($ilCtrl->getFormAction($parent_obj));
        $this->setFormName('table_list');

        //those two are important as we get our data as objects not as arrays.
        $this->setExternalSegmentation(true);
        $this->setExternalSorting(true);

        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setShowRowsSelector(false);
        $this->setShowTemplates(false);
        $this->setEnableHeader(true);
        $this->setEnableTitle(true);
        $this->setDefaultOrderDirection('asc');

        $this->setTitle($lng->txt('dcl_table_list_tables'));
        $this->setRowTemplate('tpl.table_list_row.html', 'Modules/DataCollection');
        $this->setStyle('table', $this->getStyle('table') . ' ' . 'dcl_record_list');

        $tables = $this->parent_obj->getDataCollectionObject()->getTables();
        $this->setData($tables);
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

    public function fillRowFromObject(ilDclTable $a_set) : void
    {
        $this->tpl->setVariable("ID", $a_set->getId());
        $this->tpl->setVariable("ORDER_NAME", "order[{$a_set->getId()}]");
        $this->tpl->setVariable("ORDER_VALUE", $a_set->getOrder());
        $this->tpl->setVariable("TITLE", $a_set->getTitle());

        $this->ctrl->setParameterByClass('ildclfieldlistgui', 'table_id', $a_set->getId());
        $this->tpl->setVariable("TITLE_LINK", $this->ctrl->getLinkTargetByClass('ildclfieldlistgui'));

        $this->tpl->setVariable("CHECKBOX_NAME_VISIBLE", 'visible[' . $a_set->getId() . ']');
        if ($a_set->getIsVisible()) {
            $this->tpl->setVariable("CHECKBOX_CHECKED_VISIBLE", 'checked');
        }
        $this->tpl->setVariable("CHECKBOX_NAME_COMMENTS", 'comments[' . $a_set->getId() . ']');
        if ($a_set->getPublicCommentsEnabled()) {
            $this->tpl->setVariable("CHECKBOX_CHECKED_COMMENTS", 'checked');
        }
        $this->tpl->setVariable('ACTIONS', $this->buildActions($a_set->getId()));
    }

    protected function buildActions(int $id) : string
    {
        $alist = new ilAdvancedSelectionListGUI();
        $alist->setId($id);
        $alist->setListTitle($this->lng->txt('actions'));
        $this->ctrl->setParameterByClass('ildclfieldlistgui', 'table_id', $id);
        $this->ctrl->setParameterByClass('ildcltableviewgui', 'table_id', $id);
        $this->ctrl->setParameterByClass('ildcltableeditgui', 'table_id', $id);
        $alist->addItem($this->lng->txt('settings'), '',
            $this->ctrl->getLinkTargetByClass('ildcltableeditgui', 'edit'));
        $alist->addItem($this->lng->txt('dcl_list_fields'), '',
            $this->ctrl->getLinkTargetByClass('ildclfieldlistgui', 'listFields'));
        $alist->addItem($this->lng->txt('dcl_tableviews'), '', $this->ctrl->getLinkTargetByClass('ildcltableviewgui'));
        $alist->addItem($this->lng->txt('delete'), '',
            $this->ctrl->getLinkTargetByClass('ildcltableeditgui', 'confirmDelete'));

        return $alist->getHTML();
    }
}
