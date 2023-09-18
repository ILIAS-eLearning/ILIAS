<?php

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
