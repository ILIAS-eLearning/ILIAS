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
 * Class ilDclCreateViewTableGUI
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class ilDclCreateViewTableGUI extends ilTable2GUI
{
    public const VALID_DEFAULT_VALUE_TYPES = [
        ilDclDatatype::INPUTFORMAT_NUMBER,
        ilDclDatatype::INPUTFORMAT_TEXT,
        ilDclDatatype::INPUTFORMAT_BOOLEAN,
    ];

    public function __construct(ilDclCreateViewDefinitionGUI $a_parent_obj)
    {
        parent::__construct($a_parent_obj);
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->setId('dcl_tableviews');
        $this->setTitle($lng->txt('dcl_tableview_fieldsettings'));
        $this->addColumn($lng->txt('dcl_tableview_fieldtitle'), "", 'auto');
        $this->addColumn($lng->txt('dcl_tableview_field_access'), "", 'auto');
        $this->addColumn($lng->txt('dcl_tableview_default_value'), "", 'auto');

        $ilCtrl->saveParameter($this, 'tableview_id');
        $this->setFormAction($ilCtrl->getFormActionByClass('ildclcreateviewdefinitiongui'));
        $this->addCommandButton('saveTable', $lng->txt('dcl_save'));

        $this->setExternalSegmentation(true);
        $this->setExternalSorting(true);

        $this->setRowTemplate('tpl.tableview_create_view.html', 'Modules/DataCollection');
        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setShowRowsSelector(false);
        $this->setShowTemplates(false);
        $this->setEnableHeader(true);
        $this->setEnableTitle(true);
        $this->setDefaultOrderDirection('asc');

        $this->parseData($a_parent_obj->tableview->getFieldSettings());
    }

    public function parseData(array $data): void
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

    public function fillRowFromObject(ilDclTableViewFieldSetting $a_set): void
    {
        $lng = $this->lng;
        $field = $a_set->getFieldObject();
        $match = ilDclTableViewBaseDefaultValue::findSingle($field->getDataTypeId(), $a_set->getId());

        /** @var ilDclTextInputGUI $item */
        $item = ilDclCache::getFieldRepresentation($field)->getInputField(new ilPropertyFormGUI());

        if (!is_null($match)) {
            if ($item instanceof ilDclCheckboxInputGUI) {
                $item->setChecked($match->getValue());
            } else {
                $item->setValue($match->getValue());
            }
        }

        if (!$field->isStandardField()) {
            $this->tpl->setVariable('TEXT_VISIBLE', $lng->txt('dcl_tableview_visible'));
            $this->tpl->setVariable('TEXT_REQUIRED_VISIBLE', $lng->txt('dcl_tableview_required_visible'));
            $this->tpl->setVariable('TEXT_LOCKED_VISIBLE', $lng->txt('dcl_tableview_locked_visible'));
            $this->tpl->setVariable('TEXT_NOT_VISIBLE', $lng->txt('dcl_tableview_not_visible'));
            $this->tpl->setVariable('IS_LOCKED', $a_set->isLockedCreate() ? 'checked' : '');
            $this->tpl->setVariable('IS_REQUIRED', $a_set->isRequiredCreate() ? 'checked' : '');
            $this->tpl->setVariable('DEFAULT_VALUE', $a_set->getDefaultValue());
            $this->tpl->setVariable('IS_VISIBLE', $a_set->isVisibleCreate() ? 'checked' : '');
            $this->tpl->setVariable('IS_NOT_VISIBLE', !$a_set->isVisibleCreate() ? 'checked' : '');
            if (!is_null($item) && in_array($field->getDatatypeId(), self::VALID_DEFAULT_VALUE_TYPES)) {
                $name = "default_" . $a_set->getId() . "_" . $field->getDatatypeId();
                $item->setPostVar($name);
                if ($item instanceof ilTextAreaInputGUI) {
                    $replacement_box = new ilTextInputGUI();
                    $replacement_box->setPostVar($item->getPostVar());
                    $replacement_box->setValue($item->getValue());
                    $this->tpl->setVariable('INPUT', $replacement_box->render());
                } else {
                    $this->tpl->setVariable('INPUT', $item->render());
                }

                // Workaround as empty checkboxes do not get posted
                if ($item instanceof ilDclCheckboxInputGUI) {
                    $this->tpl->setVariable('EXTRA_INPUT', "<input type=\"hidden\" name=\"$name\" value=\"0\" />");
                }
            }
        } else {
            $this->tpl->setVariable('HIDDEN', 'hidden');
        }

        $this->tpl->setVariable('FIELD_ID', $a_set->getField());
        $this->tpl->setVariable('TITLE', $field->getTitle());
        $this->tpl->parseCurrentBlock();
    }
}
