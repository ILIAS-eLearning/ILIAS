<?php

declare(strict_types=1);

class ilADTMultiEnumDefinition extends ilADTDefinition
{
    protected array $options = [];
    protected bool $numeric = false;

    // default

    public function reset(): void
    {
        parent::reset();

        $this->options = array();
        $this->setNumeric(true);
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $a_values): void
    {
        if ($this->isNumeric()) {
            foreach (array_keys($a_values) as $key) {
                if (!is_numeric($key)) {
                    throw new InvalidArgumentException("ilADTMultiEnum was expecting numeric option keys");
                }
            }
        }
        $this->options = $a_values;
    }

    public function isNumeric(): bool
    {
        return $this->numeric;
    }

    public function setNumeric(bool $a_value): void
    {
        $this->numeric = $a_value;
    }

    public function isComparableTo(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTMultiEnum);
    }

    // ADT instance

    public function getADTInstance(): ilADT
    {
        if ($this->isNumeric()) {
            $class = "ilADTMultiEnumNumeric";
        } else {
            $class = "ilADTMultiEnumText";
        }
        return new $class($this);
    }
}
