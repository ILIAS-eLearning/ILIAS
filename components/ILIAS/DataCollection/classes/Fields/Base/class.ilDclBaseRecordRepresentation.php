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
 *********************************************************************/

declare(strict_types=1);

class ilDclBaseRecordRepresentation
{
    protected \ILIAS\UI\Factory $factory;
    protected ilDclBaseRecordFieldModel $record_field;
    protected ilLanguage $lng;
    protected ilAccess $access;
    protected ilCtrl $ctrl;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\Refinery\Factory $refinery;
    protected \ILIAS\UI\Renderer $renderer;
    protected ilObjUser $user;

    public function __construct(ilDclBaseRecordFieldModel $record_field)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->user = $DIC->user();

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
    public function fillFormInput(ilPropertyFormGUI $form): void
    {
        $input_field = $form->getItemByPostVar('field_' . $this->getRecordField()->getField()->getId());
        if ($input_field) {
            $value = $this->getFormInput();
            if (!is_null($value)) {
                $input_field->setValueByArray(["field_" . $this->getRecordField()->getField()->getId() => $value]);
            }
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
    public function getHTML(bool $link = true, array $options = []): string
    {
        return (string) $this->getRecordField()->getValue();
    }

    /**
     * Returns data for single record view
     */
    public function getSingleHTML(?array $options = null, bool $link = true): string
    {
        return $this->getHTML($link, $options);
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
