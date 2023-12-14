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


declare(strict_types=1);

/**
 * @ilCtrl_Calls ilDateTime
 */
class ilDclFieldListTableGUI extends ilTable2GUI
{
    private ?int $order = null;

    protected ilDclTable $table;

    protected \ILIAS\UI\Renderer $renderer;
    protected \ILIAS\UI\Factory $ui_factory;


    public function __construct(ilDclFieldListGUI $a_parent_obj, string $a_parent_cmd, int $table_id)
    {
        global $DIC;

        $this->ui_factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->parent_obj = $a_parent_obj;
        $this->table = ilDclCache::getTableCache($table_id);

        $this->setId('dcl_field_list');
        $this->addColumn('', '', '1', true);
        $this->addColumn($this->lng->txt('dcl_order'), '', '30px');
        $this->addColumn($this->lng->txt('dcl_fieldtitle'), '', 'auto');
        $this->addColumn($this->lng->txt('dcl_in_export'), '', '30px');
        $this->addColumn($this->lng->txt('dcl_description'), '', 'auto');
        $this->addColumn($this->lng->txt('dcl_field_datatype'), '', 'auto');
        $this->addColumn($this->lng->txt('dcl_unique'), '', 'auto');
        $this->addColumn($this->lng->txt('actions'), '', '');
        // Only add mutli command for custom fields
        if (count($this->table->getRecordFields())) {
            $this->setSelectAllCheckbox('dcl_field_ids[]');
            $this->addMultiCommand('confirmDeleteFields', $this->lng->txt('dcl_delete_fields'));
        }

        $this->ctrl->setParameterByClass('ildclfieldeditgui', 'table_id', $this->parent_obj->getTableId());
        $this->ctrl->setParameterByClass('ildclfieldlistgui', 'table_id', $this->parent_obj->getTableId());

        $this->setFormAction($this->ctrl->getFormActionByClass('ildclfieldlistgui'));
        $this->addCommandButton('save', $this->lng->txt('dcl_save'));

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
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

        $this->setTitle($this->table->getTitle());
        $this->setRowTemplate('tpl.field_list_row.html', 'Modules/DataCollection');
        $this->setStyle('table', $this->getStyle('table') . ' ' . 'dcl_record_list');

        $this->setData($this->table->getFields());
    }

    /**
     * Get HTML
     */
    public function getHTML(): string
    {
        if ($this->getExportMode()) {
            $this->exportData($this->getExportMode(), true);
        }

        $this->prepareOutput();

        if (is_object($this->getParentObject()) && $this->getId() == "") {
            $this->ctrl->saveParameter($this->getParentObject(), $this->getNavParameter());
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
                : $this->lng->txt("no_items");

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
            $this->tpl->setVariable('NO_FILTER_EXPORTABLE');
        }

        $this->order = $this->order + 10;
        $this->tpl->setVariable('ORDER_NAME', 'order[' . $a_set->getId() . ']');
        $this->tpl->setVariable('ORDER_VALUE', $this->order);

        $this->tpl->setVariable('TITLE', $a_set->getTitle());
        $this->tpl->setVariable('DESCRIPTION', $a_set->getDescription());
        $this->tpl->setVariable('DATATYPE', $a_set->getDatatypeTitle());

        if (!$a_set->isStandardField()) {
            if ($a_set->isUnique()) {
                $icon = $this->ui_factory->symbol()->icon()->custom(ilUtil::getImagePath('standard/icon_ok_monochrome.svg'), $this->lng->txt("yes"));
            } else {
                $icon = $this->ui_factory->symbol()->icon()->custom(ilUtil::getImagePath('standard/icon_not_ok_monochrome.svg'), $this->lng->txt("no"));
            }
            $this->tpl->setVariable('ICON_UNIQUE', $this->renderer->render($icon));
        } else {
            $this->tpl->setVariable('NO_UNIQUE');
        }

        $this->ctrl->setParameterByClass('ildclfieldeditgui', 'field_id', $a_set->getId());

        if (!$a_set->isStandardField()) {
            if (ilObjDataCollectionAccess::hasAccessToFields(
                $this->parent_obj->getDataCollectionObject()->getRefId(),
                $this->table->getId()
            )) {
                $dropdown_items = [];
                $dropdown_items[] = $this->ui_factory->link()->standard(
                    $this->lng->txt('edit'),
                    $this->ctrl->getLinkTargetByClass(ilDclFieldEditGUI::class, 'edit')
                );
                $dropdown_items[] = $this->ui_factory->link()->standard(
                    $this->lng->txt('delete'),
                    $this->ctrl->getLinkTargetByClass(ilDclFieldEditGUI::class, 'confirmDelete')
                );
                $dropdown = $this->ui_factory->dropdown()->standard($dropdown_items)->withLabel($this->lng->txt('actions'));

                $this->tpl->setVariable('ACTIONS', $this->renderer->render($dropdown));
            } else {
                $this->tpl->setVariable('ACTIONS');
            }
        }
    }
}
