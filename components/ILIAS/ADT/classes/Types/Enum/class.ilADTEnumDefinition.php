<?php

declare(strict_types=1);

class ilADTEnumDefinition extends ilADTDefinition
{
    protected array $options = [];
    protected bool $numeric; // [bool]

    public function getType(): string
    {
        return "Enum";
    }

    // default

    public function reset(): void
    {
        parent::reset();

        $this->options = array();
        $this->setNumeric(true);
    }

    // properties

    public function isNumeric(): bool
    {
        return $this->numeric;
    }

    public function setNumeric(bool $a_value): void
    {
        $this->numeric = $a_value;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $a_values)
    {
        if ($this->isNumeric()) {
            foreach (array_keys($a_values) as $key) {
                if (!is_numeric($key)) {
                    throw new Exception("ilADTEnum was expecting numeric option keys");
                }
            }
        }

        $this->options = $a_values;
    }

    // comparison

    public function isComparableTo(ilADT $a_adt): bool
    {
        // has to be enum-based
        return ($a_adt instanceof ilADTEnum);
    }

    public function getADTInstance(): ilADTEnum
    {
        if ($this->isNumeric()) {
            $class = "ilADTEnumNumeric";
        } else {
            $class = "ilADTEnumText";
        }
        return new $class($this);
    }
}
