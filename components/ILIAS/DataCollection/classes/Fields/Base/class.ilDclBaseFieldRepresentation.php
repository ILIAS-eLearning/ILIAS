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

use ILIAS\HTTP\Services;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Factory;

abstract class ilDclBaseFieldRepresentation
{
    protected ilDclBaseFieldModel $field;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilObjUser $user;
    protected Services $http;
    protected ILIAS\Refinery\Factory $refinery;

    protected ilComponentRepository $component_repository;
    protected ilComponentFactory $component_factory;
    protected Factory $factory;

    public function __construct(ilDclBaseFieldModel $field)
    {
        global $DIC;

        $this->field = $field;
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->user = $DIC->user();
        $this->refinery = $DIC->refinery();
        $this->factory = $DIC->ui()->factory();
        $this->component_repository = $DIC["component.repository"];
        $this->component_factory = $DIC["component.factory"];
    }

    public function addFilterInputFieldToTable(ilTable2GUI $table): mixed
    {
        return null;
    }

    protected function setupFilterInputField(?ilTableFilterItem $input): void
    {
        $input?->setTitle($this->getField()->getTitle());
    }

    public function parseSortingValue(string $value, bool $link = true): mixed
    {
        return $value;
    }

    abstract public function getInputField(): ?FormInput;

    protected function getFilterInputFieldValue(ilTableFilterItem $input): mixed
    {
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

    public function addFieldCreationForm(
        ilSubEnabledFormPropertyGUI $form,
        ilObjDataCollection $dcl,
        string $mode = "create"
    ): void {
        $opt = $this->buildFieldCreationInput($dcl, $mode);
        if ($opt !== null) {
            $form->addOption($opt);
        }
    }

    protected function buildFieldCreationInput(ilObjDataCollection $dcl, string $mode = 'create'): ?ilRadioOption
    {
        $opt = null;
        if ($this->getField()->getDatatypeId() !== null) {
            $title = $this->field->getPresentationTitle();
            $info = $this->field->getPresentationDescription();
            $opt = new ilRadioOption($title, (string) $this->getField()->getDatatypeId());
            $opt->setInfo($info);
        }

        return $opt;
    }

    public function getPropertyInputFieldId(string $property): string
    {
        return "prop_" . $property;
    }

    public function getField(): ilDclBaseFieldModel
    {
        return $this->field;
    }
}
