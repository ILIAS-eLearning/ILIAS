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
 * Class ilDclFieldListTableGUI
 * @author       Martin Studer <ms@studer-raimann.ch>
 * @author       Marcel Raimann <mr@studer-raimann.ch>
 * @author       Fabian Schmid <fs@studer-raimann.ch>
 * @author       Oskar Truffer <ot@studer-raimann.ch>
 * @version      $Id:
 * @extends      ilTable2GUI
 * @ilCtrl_Calls ilDateTime
 */
class ilDclFieldListTableGUI extends ilTable2GUI
{
    private $order = null;

    protected ilDclTable $table;

    public function __construct(ilDclFieldListGUI $a_parent_obj, string $a_parent_cmd, int $table_id)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->parent_obj = $a_parent_obj;
        $this->table = ilDclCache::getTableCache($table_id);

        $this->setId('dcl_field_list');
        $this->addColumn('', '', '1', true);
        $this->addColumn($lng->txt('dcl_order'), '', '30px');
        $this->addColumn($lng->txt('dcl_fieldtitle'), '', 'auto');
        $this->addColumn($lng->txt('dcl_in_export'), '', '30px');
        $this->addColumn($lng->txt('dcl_description'), '', 'auto');
        $this->addColumn($lng->txt('dcl_field_datatype'), '', 'auto');
        $this->addColumn($lng->txt('dcl_unique'), '', 'auto');
        $this->addColumn($lng->txt('actions'), '', '30px');
        // Only add mutli command for custom fields
        if (count($this->table->getRecordFields())) {
            $this->setSelectAllCheckbox('dcl_field_ids[]');
            $this->addMultiCommand('confirmDeleteFields', $lng->txt('dcl_delete_fields'));
        }

        $ilCtrl->setParameterByClass('ildclfieldeditgui', 'table_id', $this->parent_obj->getTableId());
        $ilCtrl->setParameterByClass('ildclfieldlistgui', 'table_id', $this->parent_obj->getTableId());

        $this->setFormAction($ilCtrl->getFormActionByClass('ildclfieldlistgui'));
        $this->addCommandButton('save', $lng->txt('dcl_save'));

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setFormName('field_list');

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

        $this->setTitle($lng->txt('dcl_table_list_fields'));
        $this->setRowTemplate('tpl.field_list_row.html', 'Modules/DataCollection');
        $this->setStyle('table', $this->getStyle('table') . ' ' . 'dcl_record_list');

        $this->setData($this->table->getFields());
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

    public function fillRowFromObject(ilDclBaseFieldModel $a_set): void
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        if (!$a_set->isStandardField()) {
            $this->tpl->setVariable('FIELD_ID', $a_set->getId());
        }

        $this->tpl->setVariable('NAME', 'order[' . $a_set->getId() . ']');
        $this->tpl->setVariable('VALUE', $this->order);

        /* Don't enable setting filter for MOB fields or reference fields that reference a MOB field */
        $show_exportable = true;

        if ($a_set->getId() == 'comments') {
            $show_exportable = false;
        }

        if ($show_exportable) {
            $this->tpl->setVariable('CHECKBOX_EXPORTABLE', 'exportable[' . $a_set->getId() . ']');
            if ($a_set->getExportable()) {
                $this->tpl->setVariable('CHECKBOX_EXPORTABLE_CHECKED', 'checked');
            }
        } else {
            $this->tpl->setVariable('NO_FILTER_EXPORTABLE', '');
        }

        $this->order = $this->order + 10;
        $this->tpl->setVariable('ORDER_NAME', 'order[' . $a_set->getId() . ']');
        $this->tpl->setVariable('ORDER_VALUE', $this->order);

        $this->tpl->setVariable('TITLE', $a_set->getTitle());
        $this->tpl->setVariable('DESCRIPTION', $a_set->getDescription());
        $this->tpl->setVariable('DATATYPE', $a_set->getDatatypeTitle());

        if (!$a_set->isStandardField()) {
            switch ($a_set->isUnique()) {
                case 0:
                    $uniq = ilUtil::getImagePath('icon_not_ok_monochrome.svg', "/Modules/DataCollection");
                    break;
                case 1:
                    $uniq = ilUtil::getImagePath('icon_ok_monochrome.svg', "/Modules/DataCollection");
                    break;
            }
            $this->tpl->setVariable('UNIQUE', $uniq);
        } else {
            $this->tpl->setVariable('NO_UNIQUE', '');
        }

        $ilCtrl->setParameterByClass('ildclfieldeditgui', 'field_id', $a_set->getId());

        if (!$a_set->isStandardField()) {
            $alist = new ilAdvancedSelectionListGUI();
            $alist->setId($a_set->getId());
            $alist->setListTitle($lng->txt('actions'));

            if (ilObjDataCollectionAccess::hasAccessToFields(
                $this->parent_obj->getDataCollectionObject()->getRefId(),
                $this->table->getId()
            )) {
                $alist->addItem($lng->txt('edit'), 'edit', $ilCtrl->getLinkTargetByClass('ildclfieldeditgui', 'edit'));
                $alist->addItem(
                    $lng->txt('delete'),
                    'delete',
                    $ilCtrl->getLinkTargetByClass('ildclfieldeditgui', 'confirmDelete')
                );
            }

            $this->tpl->setVariable('ACTIONS', $alist->getHTML());
        }
    }
}
