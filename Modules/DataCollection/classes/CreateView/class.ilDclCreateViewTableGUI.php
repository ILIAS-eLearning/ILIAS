<?php

/**
 * Class ilDclCreateViewTableGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class ilDclCreateViewTableGUI extends ilTable2GUI
{
    const VALID_DEFAULT_VALUE_TYPES = [
        ilDclDatatype::INPUTFORMAT_NUMBER,
        ilDclDatatype::INPUTFORMAT_TEXT,
        ilDclDatatype::INPUTFORMAT_BOOLEAN,
    ];

    public function __construct(ilDclCreateViewDefinitionGUI $a_parent_obj)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        parent::__construct($a_parent_obj);

        $this->setId('dcl_tableviews');
        $this->setTitle($lng->txt('dcl_tableview_fieldsettings'));
        $this->addColumn($lng->txt('dcl_tableview_fieldtitle'), null, 'auto');
        $this->addColumn($lng->txt('dcl_tableview_field_access'), null, 'auto');
        $this->addColumn($lng->txt('dcl_tableview_default_value'), null, 'auto');

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


    public function parseData($data)
    {
        $this->setData($data);
    }


    /**
     * @param ilDclTableViewFieldSetting $a_set
     */
    public function fillRow($a_set)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $field = $a_set->getFieldObject();
        $match = ilDclTableViewBaseDefaultValue::findSingle(intval($field->getDataTypeId()), $a_set->getId());

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