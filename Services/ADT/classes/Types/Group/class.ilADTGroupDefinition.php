<?php

class ilADTGroupDefinition extends ilADTDefinition
{
    protected $elements = []; // [array]
    
    public function __clone()
    {
        if (is_array($this->elements)) {
            foreach ($this->elements as $id => $element) {
                $this->elements[$id] = clone $element;
            }
        }
    }

    
    // defaults
    
    public function reset()
    {
        parent::reset();
        
        $this->elements = array();
    }
    
    
    // properties
    
    public function addElement($a_name, ilADTDefinition $a_def)
    {
        $this->elements[$a_name] = $a_def;
    }
    
    public function hasElement($a_name)
    {
        return array_key_exists($a_name, $this->elements);
    }
    
    public function getElement($a_name)
    {
        if ($this->hasElement($a_name)) {
            return $this->elements[$a_name];
        }
    }
    
    public function getElements()
    {
        return $this->elements;
    }
    
    
    // comparison
        
    public function isComparableTo(ilADT $a_adt)
    {
        // has to be group-based
        return ($a_adt instanceof ilADTGroup);
    }
}
