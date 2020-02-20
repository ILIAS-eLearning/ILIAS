<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclBaseFieldRepresentation
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
abstract class ilDclBaseFieldRepresentation
{
    protected $field;
    /**
     * @var ilLanguage
     */
    protected $lng;
    /**
     * @var ilCtrl $ctrl ;
     */
    protected $ctrl;


    public function __construct(ilDclBaseFieldModel $field)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $this->field = $field;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
    }


    /**
     * Add filter input to TableGUI
     *
     * @param ilTable2GUI $table
     *
     * @return null
     */
    public function addFilterInputFieldToTable(ilTable2GUI $table)
    {
        return null;
    }


    /**
     * Set basic settings for filter-input-gui
     *
     * @param ilFormPropertyGUI $input
     */
    protected function setupFilterInputField(ilFormPropertyGUI $input)
    {
        if ($input != null) {
            $input->setTitle($this->getField()->getTitle());
        }
    }


    /**
     * Checks if a filter affects a record
     *
     * @param ilDclBaseRecordModel $record
     * @param                      $filter
     *
     * @return bool
     */
    public function passThroughFilter(ilDclBaseRecordModel $record, $filter)
    {
        $value = $record->getRecordFieldValue($this->getField()->getId());
        $pass = true;

        if (($this->getField()->getId() == "owner" || $this->getField()->getId() == "last_edit_by") && $filter) {
            $pass = false;
            $user = new ilObjUser($value);
            if (strpos($user->getFullname(), $filter) !== false) {
                $pass = true;
            }
        }

        return $pass;
    }


    /**
     *
     * @param      $value
     * @param bool $link
     *
     * @return mixed
     */
    public function parseSortingValue($value, $link = true)
    {
        return $value;
    }


    /**
     * Returns field-input
     *
     * @param ilPropertyFormGUI $form
     * @param int               $record_id
     *
     * @return null
     */
    public function getInputField(ilPropertyFormGUI $form, $record_id = 0)
    {
        return null;
    }


    /**
     * Sets basic settings on field-input
     *
     * @param ilFormPropertyGUI   $input
     * @param ilDclBaseFieldModel $field
     */
    protected function setupInputField(ilFormPropertyGUI $input, ilDclBaseFieldModel $field)
    {
        $input->setRequired($field->getRequired());
        $input->setInfo($field->getDescription() . ($input->getInfo() ? '<br>' . $input->getInfo() : ''));
    }


    /**
     * @param $input
     *
     * @return null
     */
    protected function getFilterInputFieldValue(/*ilPropertyFormGUI*/
        $input
    ) {
        $value = $input->getValue();
        if (is_array($value)) {
            if ($value['from'] || $value['to']) {
                return $value;
            }
        } else {
            if ($value != '') {
                return $value;
            }
        }

        return null;
    }


    /**
     * Adds the options for the field-types to the field-creation form
     *
     * @param                     $form
     * @param ilObjDataCollection $dcl
     * @param string              $mode
     */
    public function addFieldCreationForm($form, ilObjDataCollection $dcl, $mode = "create")
    {
        $opt = $this->buildFieldCreationInput($dcl, $mode);

        if ($mode != 'create' && $this->getField()->getDatatypeId() == ilDclDatatype::INPUTFORMAT_PLUGIN) {
            $new_plugin_title = $opt->getTitle();
            $plugin_name = ilDclFieldFactory::getPluginNameFromFieldModel($this->getField());
            if ($plugin_name !== "DclBase") {
                $new_plugin_title .= ': ' . $plugin_name;
            }
            $opt->setTitle($new_plugin_title);
        }

        $form->addOption($opt);
    }


    /**
     * Build the creation-input-field
     *
     * @param ilObjDataCollection $dcl
     * @param string              $mode
     *
     * @return ilPropertyFormGUI
     */
    protected function buildFieldCreationInput(ilObjDataCollection $dcl, $mode = 'create')
    {
        $opt = new ilRadioOption($this->lng->txt('dcl_' . $this->getField()->getDatatype()->getTitle()), $this->getField()->getDatatypeId());
        $opt->setInfo($this->lng->txt('dcl_' . $this->getField()->getDatatype()->getTitle() . '_desc'));

        return $opt;
    }


    /**
     * Return post-var for property-fields
     *
     * @param $property
     *
     * @return string
     */
    public function getPropertyInputFieldId($property)
    {
        return "prop_" . $property;
    }


    /**
     * Return BaseFieldModel
     *
     * @return ilDclBaseFieldModel
     */
    public function getField()
    {
        return $this->field;
    }
}
