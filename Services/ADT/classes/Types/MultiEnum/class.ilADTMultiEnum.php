<?php

abstract class ilADTMultiEnum extends ilADT
{
    protected $values; // [array]
        
    public function getType()
    {
        return "MultiEnum";
    }
    
    
    // definition
    
    protected function isValidDefinition(ilADTDefinition $a_def)
    {
        return ($a_def instanceof ilADTMultiEnumDefinition);
    }
    
    public function reset()
    {
        parent::reset();
        
        $this->values = null;
    }
    
    
    // properties
    
    abstract protected function handleSelectionValue($a_value);

    public function addSelection(int $value_index)
    {
        if (!$this->isValidOption($value_index)) {
            return;
        }
        $this->values[] = $value_index;
    }


    public function setSelections(array $a_values = null)
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
    
    public function getSelections()
    {
        return $this->values;
    }
                
    public function isValidOption($a_value)
    {
        $a_value = $this->handleSelectionValue($a_value);
        return array_key_exists($a_value, $this->getDefinition()->getOptions());
    }
    
    
    // comparison
    
    public function equals(ilADT $a_adt)
    {
        if ($this->getDefinition()->isComparableTo($a_adt)) {
            return ($this->getCheckSum() === $a_adt->getCheckSum());
        }
    }
                
    public function isLarger(ilADT $a_adt)
    {
        // return null?
    }

    public function isSmaller(ilADT $a_adt)
    {
        // return null?
    }
    
    
    // null
    
    public function isNull()
    {
        return ($this->getSelections() === null);
    }
        
    
    // check
    
    public function getCheckSum()
    {
        if (!$this->isNull()) {
            $current = $this->getSelections();
            sort($current);
            return md5(implode(",", $current));
        }
    }
    
    
    // stdClass
    
    public function exportStdClass()
    {
        if (!$this->isNull()) {
            $obj = new stdClass();
            $obj->value = $this->getSelections();
            return $obj;
        }
    }
    
    public function importStdClass($a_std)
    {
        if (is_object($a_std)) {
            $this->setSelections($a_std->value);
        }
    }
}
