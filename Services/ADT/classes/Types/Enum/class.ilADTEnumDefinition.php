<?php declare(strict_types=1);

class ilADTEnumDefinition extends ilADTDefinition
{
    protected array $options = [];
    protected bool $numeric; // [bool]

    public function getType() : string
    {
        return "Enum";
    }

    // default

    public function reset() : void
    {
        parent::reset();

        $this->options = array();
        $this->setNumeric(true);
    }

    // properties

    public function isNumeric() : bool
    {
        return $this->numeric;
    }

    public function setNumeric(bool $a_value) : void
    {
        $this->numeric = $a_value;
    }
    //Todo-PHP8-Review Begin: Missing return type declaration
    public function getOptions()
    //Todo-PHP8-Review End
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

    public function isComparableTo(ilADT $a_adt) : bool
    {
        // has to be enum-based
        return ($a_adt instanceof ilADTEnum);
    }

    // ADT instance
    //Todo-PHP8-Review Begin: Missing return type declaration
    public function getADTInstance()
    //Todo-PHP8-Review End
    {
        if ($this->isNumeric()) {
            $class = "ilADTEnumNumeric";
        } else {
            $class = "ilADTEnumText";
        }
        return new $class($this);
    }
}
