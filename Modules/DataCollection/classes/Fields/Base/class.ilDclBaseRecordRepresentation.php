<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclBaseRecordRepresentation
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclBaseRecordRepresentation
{
    protected $record_field;
    protected $lng;
    protected $access;
    protected $ctrl;


    public function __construct(ilDclBaseRecordFieldModel $record_field)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->lng = $lng;
        $this->access = $ilAccess;
        $this->ctrl = $ilCtrl;

        $this->record_field = $record_field;
    }


    public function getFormGUI(ilPropertyFormGUI $formGUI)
    {
        // Apply form-elements to record-entry-gui
    }


    /**
     * function parses stored value to the variable needed to fill into the form for editing.
     *
     * @param $value
     *
     * @return mixed
     */
    public function parseFormInput($value)
    {
        return $value;
    }


    /**
     * Fills the form with the value of a record
     *
     * @param $form
     */
    public function fillFormInput($form)
    {
        $input_field = $form->getItemByPostVar('field_' . $this->getRecordField()->getField()->getId());
        if ($input_field) {
            $value = $this->getFormInput();
            $input_field->setValueByArray(array("field_" . $this->getRecordField()->getField()->getId() => $value));
        }
    }


    /**
     * Gets the value from from the record field
     *
     * @return mixed
     */
    protected function getFormInput()
    {
        return $this->parseFormInput($this->getRecordField()->getValue());
    }


    /**
     * Outputs html of a certain field
     *
     * @param mixed     $value
     * @param bool|true $link
     *
     * @return string
     */
    public function getHTML($link = true)
    {
        return $this->getRecordField()->getValue();
    }


    /**
     * Returns data for single record view
     *
     * @param array|NULL $options
     * @param bool       $link
     *
     * @return string
     */
    public function getSingleHTML(array $options = null, $link = true)
    {
        return $this->getHTML($link);
    }


    /**
     * Returns data for confirmation list
     * When returning false, attribute is ignored in list
     *
     * @return string
     */
    public function getConfirmationHTML()
    {
        return $this->getHTML();
    }


    /**
     * Fills row with record data
     *
     * @param ilTemplate $tpl
     */
    public function fillRow(ilTemplate $tpl)
    {
    }


    /**
     * Get Record Field
     *
     * @return ilDclBaseRecordFieldModel
     */
    public function getRecordField()
    {
        return $this->record_field;
    }


    /**
     * Getter shortcut for field
     *
     * @return ilDclBaseFieldModel
     */
    public function getField()
    {
        return $this->record_field->getField();
    }


    /**
     * Getter shortcut for record
     *
     * @return ilDclBaseRecordModel
     */
    public function getRecord()
    {
        return $this->record_field->getRecord();
    }
}
