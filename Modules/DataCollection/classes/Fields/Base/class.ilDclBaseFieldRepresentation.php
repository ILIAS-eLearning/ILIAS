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
 * Class ilDclBaseFieldRepresentation
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
abstract class ilDclBaseFieldRepresentation
{
    protected ilDclBaseFieldModel $field;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\Refinery\Factory $refinery;

    protected ilComponentRepository $component_repository;
    protected ilComponentFactory $component_factory;

    public function __construct(ilDclBaseFieldModel $field)
    {
        global $DIC;

        $this->field = $field;
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->component_repository = $DIC["component.repository"];
        $this->component_factory = $DIC["component.factory"];
    }

    /**
     * Add filter input to TableGUI
     * @param ilTable2GUI $table
     * @return null
     */
    public function addFilterInputFieldToTable(ilTable2GUI $table)
    {
        return null;
    }

    /**
     * Set basic settings for filter-input-gui
     */
    protected function setupFilterInputField(?ilTableFilterItem $input) : void
    {
        if ($input != null) {
            $input->setTitle($this->getField()->getTitle());
        }
    }

    /**
     * Checks if a filter affects a record
     * @param int|string|array $filter
     */
    public function passThroughFilter(ilDclBaseRecordModel $record, $filter) : bool
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
     * @param mixed $value
     * @return mixed
     */
    public function parseSortingValue(string $value, bool $link = true)
    {
        return $value;
    }

    /**
     * Returns field-input
     */
    public function getInputField(ilPropertyFormGUI $form, int $record_id = 0) : ?ilFormPropertyGUI
    {
        return null;
    }

    /**
     * Sets basic settings on field-input
     * @param ilFormPropertyGUI $input
     * @param ilDclBaseFieldModel $field
     */
    protected function setupInputField(ilFormPropertyGUI $input, ilDclBaseFieldModel $field) : void
    {
        $input->setInfo($field->getDescription() . ($input->getInfo() ? '<br>' . $input->getInfo() : ''));
    }

    /**
     * @return string|array|null
     */
    protected function getFilterInputFieldValue(ilTableFilterItem $input
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
     */
    public function addFieldCreationForm(
        ilSubEnabledFormPropertyGUI $form,
        ilObjDataCollection $dcl,
        string $mode = "create"
    ) : void {
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
     */
    protected function buildFieldCreationInput(ilObjDataCollection $dcl, string $mode = 'create') : ilRadioOption
    {
        $opt = new ilRadioOption($this->lng->txt('dcl_' . $this->getField()->getDatatype()->getTitle()),
            $this->getField()->getDatatypeId());
        $opt->setInfo($this->lng->txt('dcl_' . $this->getField()->getDatatype()->getTitle() . '_desc'));

        return $opt;
    }

    /**
     * Return post-var for property-fields
     */
    public function getPropertyInputFieldId(string $property) : string
    {
        return "prop_" . $property;
    }

    /**
     * Return BaseFieldModel
     */
    public function getField() : ilDclBaseFieldModel
    {
        return $this->field;
    }
}
