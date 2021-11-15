<?php declare(strict_types=1);

class ilADTTextDefinition extends ilADTDefinition
{
    protected ?int $max_length;

    // properties

    public function getMaxLength() : ?int
    {
        return $this->max_length;
    }

    public function setMaxLength(?int $a_value) : void
    {
        $a_value = (int) $a_value;
        if ($a_value < 1) {
            $a_value = null;
        }
        $this->max_length = $a_value;
    }

    // comparison

    public function isComparableTo(ilADT $a_adt) : bool
    {
        // has to be text-based
        return ($a_adt instanceof ilADTText);
    }
}
