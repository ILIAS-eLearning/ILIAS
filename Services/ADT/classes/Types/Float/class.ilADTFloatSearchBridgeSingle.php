<?php

declare(strict_types=1);

class ilADTFloatSearchBridgeSingle extends ilADTSearchBridgeSingle
{
    protected function isValidADTDefinition(ilADTDefinition $a_adt_def): bool
    {
        return ($a_adt_def instanceof ilADTFloatDefinition);
    }

    // table2gui / filter

    public function loadFilter(): void
    {
        $value = $this->readFilter();
        if ($value !== null) {
            $this->getADT()->setNumber($value);
        }
    }

    // form

    public function addToForm(): void
    {
        $def = $this->getADT()->getCopyOfDefinition();

        $number = new ilNumberInputGUI($this->getTitle(), $this->getElementId());
        $number->setSize(10);
        $number->setDecimals($def->getDecimals());

        $min = $def->getMin();
        if ($min !== null) {
            $number->setMinValue($min);
        }

        $max = $def->getMax();
        if ($max !== null) {
            $number->setMaxValue($max);

            $length = strlen($max);
            $number->setSize($length);
            $number->setMaxLength($length);
        }

        $number->setValue($this->getADT()->getNumber());

        $this->addToParentElement($number);
    }

    public function importFromPost(array $a_post = null): bool
    {
        $post = $this->extractPostValues($a_post);

        if ($post && $this->shouldBeImportedFromPost($post)) {
            $item = $this->getForm()->getItemByPostVar($this->getElementId());
            $item->setValue($post);

            $this->getADT()->setNumber($post);
        } else {
            $this->getADT()->setNumber();
        }
        return true;
    }

    // db

    public function getSQLCondition(string $a_element_id, int $mode = self::SQL_LIKE, array $quotedWords = []): string
    {
        if (!$this->isNull() && $this->isValid()) {
            return $a_element_id . " = " . $this->db->quote($this->getADT()->getNumber(), "float");
        }
        return '';
    }

    public function isInCondition(ilADT $a_adt): bool
    {
        assert($a_adt instanceof ilADTFloat);

        return $this->getADT()->equals($a_adt);
    }

    //  import/export

    public function getSerializedValue(): string
    {
        if (!$this->isNull() && $this->isValid()) {
            return serialize(array($this->getADT()->getNumber()));
        }
        return '';
    }

    public function setSerializedValue(string $a_value): void
    {
        $a_value = unserialize($a_value);
        if (is_array($a_value)) {
            $this->getADT()->setNumber($a_value[0]);
        }
    }
}
