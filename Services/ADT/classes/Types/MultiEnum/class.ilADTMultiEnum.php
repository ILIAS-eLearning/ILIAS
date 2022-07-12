<?php declare(strict_types=1);

abstract class ilADTMultiEnum extends ilADT
{
    protected ?array $values;

    public function getType() : string
    {
        return "MultiEnum";
    }

    // definition

    protected function isValidDefinition(ilADTDefinition $a_def) : bool
    {
        return $a_def instanceof ilADTMultiEnumDefinition;
    }

    public function reset() : void
    {
        parent::reset();
        $this->values = null;
    }

    // properties

    /**
     * @param string|int $a_value
     * @return string|int
     */
    abstract protected function handleSelectionValue($a_value);

    public function addSelection(int $value_index) : void
    {
        if (!$this->isValidOption($value_index)) {
            return;
        }
        $this->values[] = $value_index;
    }

    public function setSelections(array $a_values = null) : void
    {
        if ($a_values === null) {
            return;
        }
        $checked_values = [];
        foreach ($a_values as $value_index) {
            $clean_value = $this->handleSelectionValue($value_index);
            if ($this->isValidOption($clean_value)) {
                $checked_values[] = (int) $clean_value;
            }
        }
        $this->values = count($checked_values) ? $checked_values : null;
    }

    public function getSelections() : ?array
    {
        return $this->values;
    }

    /**
     * @param string|int $a_value
     * @return bool
     */
    public function isValidOption($a_value) : bool
    {
        $a_value = $this->handleSelectionValue($a_value);
        return array_key_exists($a_value, $this->getDefinition()->getOptions());
    }

    // comparison

    public function equals(ilADT $a_adt) : ?bool
    {
        if ($this->getDefinition()->isComparableTo($a_adt)) {
            return ($this->getCheckSum() === $a_adt->getCheckSum());
        }
        return null;
    }

    public function isLarger(ilADT $a_adt) : ?bool
    {
        return null;
    }

    public function isSmaller(ilADT $a_adt) : ?bool
    {
        return null;
    }

    // null

    public function isNull() : bool
    {
        return $this->getSelections() === null;
    }

    public function getCheckSum() : ?string
    {
        if (!$this->isNull()) {
            $current = $this->getSelections();
            sort($current);
            return md5(implode(",", $current));
        }
        return null;
    }

    // stdClass

    public function exportStdClass() : ?stdClass
    {
        if (!$this->isNull()) {
            $obj = new stdClass();
            $obj->value = $this->getSelections();
            return $obj;
        }
        return null;
    }

    public function importStdClass(?stdClass $a_std) : void
    {
        if (is_object($a_std)) {
            $this->setSelections($a_std->value);
        }
    }
}
