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

class ilADTTextActiveRecordBridge extends ilADTActiveRecordBridge
{
    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTText);
    }

    public function getActiveRecordFields(): array
    {
        $def = $this->getADT()->getCopyOfDefinition();

        $field = new arField();
        $field->setHasField(true);
        $field->setNotNull(!$def->isNullAllowed());
        $field->setFieldType(arField::FIELD_TYPE_TEXT);
        $field->setName($this->getElementId());

        $max = $def->getMaxLength();
        if ($max !== null) {
            $field->setLength($max);
        }

        return array($field);
    }

    public function getFieldValue(string $a_field_name)
    {
        return $this->getADT()->getText();
    }

    public function setFieldValue(string $a_field_name, $a_field_value): void
    {
        $this->getADT()->setText($a_field_value);
    }
}
