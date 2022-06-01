<?php declare(strict_types=1);

class ilADTIntegerDefinition extends ilADTDefinition
{
    protected ?int $min_value;
    protected ?int $max_value;
    protected string $suffix = '';

    // properties

    public function handleNumber(int $a_value) : ?int
    {
        if (!is_numeric($a_value)) {
            $a_value = null;
        }
        if ($a_value !== null) {
            // round?
            $a_value = (int) $a_value;
        }
        return $a_value;
    }

    public function getMin() : ?int
    {
        return $this->min_value;
    }

    public function setMin(int $a_value) : void
    {
        $this->min_value = $this->handleNumber($a_value);
    }

    public function getMax() : ?int
    {
        return $this->max_value;
    }

    public function setMax(int $a_value) : void
    {
        $this->max_value = $this->handleNumber($a_value);
    }

    public function getSuffix() : string
    {
        return $this->suffix;
    }

    public function setSuffix(?string $a_value) : void
    {
        $this->suffix = $a_value === null ? '' : trim($a_value);
    }

    public function isComparableTo(ilADT $a_adt) : bool
    {
        // has to be number-based
        return ($a_adt instanceof ilADTInteger);
    }
}
