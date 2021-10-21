<?php declare(strict_types=1);

class ilADTGroupDefinition extends ilADTDefinition
{
    protected array $elements = [];

    public function __clone()
    {
        if (is_array($this->elements)) {
            foreach ($this->elements as $id => $element) {
                $this->elements[$id] = clone $element;
            }
        }
    }

    // defaults

    public function reset() : void
    {
        parent::reset();
        $this->elements = array();
    }

    // properties

    public function addElement($a_name, ilADTDefinition $a_def) : void
    {
        $this->elements[$a_name] = $a_def;
    }

    public function hasElement($a_name) : bool
    {
        return array_key_exists($a_name, $this->elements);
    }

    public function getElement(string $a_name) : ?ilADTDefinition
    {
        if ($this->hasElement($a_name)) {
            return $this->elements[$a_name];
        }
        return null;
    }

    public function getElements() : array
    {
        return $this->elements;
    }

    // comparison

    public function isComparableTo(ilADT $a_adt) : bool
    {
        // has to be group-based
        return ($a_adt instanceof ilADTGroup);
    }
}
