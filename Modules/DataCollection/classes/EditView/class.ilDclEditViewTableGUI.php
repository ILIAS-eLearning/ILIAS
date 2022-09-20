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
 * Class ilDclEditViewTableGUI
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class ilDclEditViewTableGUI extends ilTable2GUI
{
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;

    public function __construct(ilDclEditViewDefinitionGUI $a_parent_obj)
    {
        parent::__construct($a_parent_obj);

        $this->setId('dcl_tableviews');
        $this->setTitle($this->lng->txt('dcl_tableview_fieldsettings'));
        $this->addColumn($this->lng->txt('dcl_tableview_fieldtitle'), "", 'auto');
        $this->addColumn($this->lng->txt('dcl_tableview_field_access'), "", 'auto');

        $this->ctrl->saveParameter($this, 'tableview_id');
        $this->setFormAction($this->ctrl->getFormActionByClass('ildcleditviewdefinitiongui'));
        $this->addCommandButton('saveTable', $this->lng->txt('dcl_save'));

        $this->setExternalSegmentation(true);
        $this->setExternalSorting(true);

        $this->setRowTemplate('tpl.tableview_edit_view.html', 'Modules/DataCollection');
        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setShowRowsSelector(false);
        $this->setShowTemplates(false);
        $this->setEnableHeader(true);
        $this->setEnableTitle(true);
        $this->setDefaultOrderDirection('asc');

        $this->parseData($a_parent_obj->tableview->getFieldSettings());
    }

    public function parseData($data)
    {
        $this->setData($data);
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

    public function fillRowFromObject(object $a_set): void
    {
        global $DIC;
        $lng = $DIC['lng'];
        $field = $a_set->getFieldObject();

        if (!$field->isStandardField() || $field->getId() === 'owner') {
            $this->tpl->setVariable('TEXT_VISIBLE', $lng->txt('dcl_tableview_visible'));
            $this->tpl->setVariable('TEXT_REQUIRED_VISIBLE', $lng->txt('dcl_tableview_required_visible'));
            $this->tpl->setVariable('TEXT_LOCKED_VISIBLE', $lng->txt('dcl_tableview_locked_visible'));
            $this->tpl->setVariable('TEXT_NOT_VISIBLE', $lng->txt('dcl_tableview_not_visible'));
            $this->tpl->setVariable('IS_LOCKED', $a_set->isLockedEdit() ? 'checked' : '');
            $this->tpl->setVariable('IS_REQUIRED', $a_set->isRequiredEdit() ? 'checked' : '');
            $this->tpl->setVariable('DEFAULT_VALUE', $a_set->getDefaultValue());
            $this->tpl->setVariable('IS_VISIBLE', $a_set->isVisibleEdit() ? 'checked' : '');
            $this->tpl->setVariable('IS_NOT_VISIBLE', !$a_set->isVisibleEdit() ? 'checked' : '');
        } else {
            $this->tpl->setVariable('HIDDEN', 'hidden');
        }

        $this->tpl->setVariable('FIELD_ID', $a_set->getField());
        $this->tpl->setVariable('TITLE', $field->getTitle());
        $this->tpl->parseCurrentBlock();
    }
}
