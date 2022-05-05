<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclBaseRecordRepresentation
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclBaseRecordRepresentation
{
    protected ilDclBaseRecordFieldModel $record_field;
    protected ilLanguage $lng;
    protected ilAccess $access;
    protected ilCtrl $ctrl;

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

    public function getFormGUI(ilPropertyFormGUI $formGUI): void
    {
        // Apply form-elements to record-entry-gui
    }

    /**
     * function parses stored value to the variable needed to fill into the form for editing.
     * @param string|int $value
     * @return string|int
     */
    public function parseFormInput($value)
    {
        return $value;
    }

    /**
     * Fills the form with the value of a record
     */
    public function fillFormInput(ilPropertyFormGUI $form)
    {
        $input_field = $form->getItemByPostVar('field_' . $this->getRecordField()->getField()->getId());
        if ($input_field) {
            $value = $this->getFormInput();
            $input_field->setValueByArray(array("field_" . $this->getRecordField()->getField()->getId() => $value));
        }
    }

    /**
     * Gets the value from from the record field
     * @return int|string
     */
    protected function getFormInput()
    {
        return $this->parseFormInput($this->getRecordField()->getValue());
    }

    /**
     * Outputs html of a certain field
     */
    public function getHTML(bool $link = true): string
    {
        return $this->getRecordField()->getValue();
    }

    /**
     * Returns data for single record view
     */
    public function getSingleHTML(?array $options = null, bool $link = true): string
    {
        return $this->getHTML($link);
    }

    /**
     * Returns data for confirmation list
     * When returning false, attribute is ignored in list
     */
    public function getConfirmationHTML(): string
    {
        return $this->getHTML();
    }

    /**
     * Fills row with record data
     * @param ilTemplate $tpl
     */
    public function fillRow(ilTemplate $tpl): void
    {
    }

    /**
     * Get Record Field
     */
    public function getRecordField(): ilDclBaseRecordFieldModel
    {
        return $this->record_field;
    }

    /**
     * Getter shortcut for field
     */
    public function getField(): ilDclBaseFieldModel
    {
        return $this->record_field->getField();
    }

    /**
     * Getter shortcut for record
     */
    public function getRecord(): ilDclBaseRecordModel
    {
        return $this->record_field->getRecord();
    }
}
