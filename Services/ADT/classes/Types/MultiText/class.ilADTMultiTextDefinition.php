<?php declare(strict_types=1);

class ilADTMultiTextDefinition extends ilADTDefinition
{
    protected ?int $max_length;
    protected ?int $max_size;

    // properties

    public function getMaxLength() : ?int
    {
        return $this->max_length;
    }

    public function setMaxLength(int $a_value) : void
    {
        if ($a_value < 1) {
            $a_value = null;
        }
        $this->max_length = $a_value;
    }

    public function getMaxSize() : ?int
    {
        return $this->max_size;
    }

    public function setMaxSize(int $a_value) : void
    {
        if ($a_value < 1) {
            $a_value = null;
        }
        $this->max_size = $a_value;
    }

    // comparison

    public function isComparableTo(ilADT $a_adt) : bool
    {
        // has to be text-based
        return ($a_adt instanceof ilADTMultiText);
    }
}
