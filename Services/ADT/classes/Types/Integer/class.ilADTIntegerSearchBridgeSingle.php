<?php

require_once "Services/ADT/classes/Bridges/class.ilADTSearchBridgeSingle.php";

class ilADTIntegerSearchBridgeSingle extends ilADTSearchBridgeSingle
{
    protected function isValidADTDefinition(ilADTDefinition $a_adt_def)
    {
        return ($a_adt_def instanceof ilADTIntegerDefinition);
    }
    
    
    // table2gui / filter
    
    public function loadFilter()
    {
        $value = $this->readFilter();
        if ($value !== null) {
            $this->getADT()->setNumber($value);
        }
    }
    
    
    // form
    
    public function addToForm()
    {
        $def = $this->getADT()->getCopyOfDefinition();
        
        $number = new ilNumberInputGUI($this->getTitle(), $this->getElementId());
        $number->setSize(10);
        
        $min = $def->getMin();
        if ($min !== null) {
            $number->setMinValue($min);
        }
        
        $max = $def->getMax();
        if ($max !== null) {
            $number->setMaxValue($max);
            
            $length = strlen($max);
            $number->setSize($length);
            $number->setMaxLength($length);
        }
        
        $number->setValue($this->getADT()->getNumber());
        
        $this->addToParentElement($number);
    }
    
    public function importFromPost(array $a_post = null)
    {
        $post = $this->extractPostValues($a_post);
                
        if ($post && $this->shouldBeImportedFromPost($post)) {
            $item = $this->getForm()->getItemByPostVar($this->getElementId());
            $item->setValue($post);
            
            $this->getADT()->setNumber($post);
        } else {
            $this->getADT()->setNumber();
        }
    }
    
    
    // db
    
    public function getSQLCondition($a_element_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$this->isNull() && $this->isValid()) {
            return $a_element_id . " = " . $ilDB->quote($this->getADT()->getNumber(), "integer");
        }
    }
    
    public function isInCondition(ilADT $a_adt)
    {
        assert($a_adt instanceof ilADTInteger);
        
        return $this->getADT()->equals($a_adt);
    }
    
    
    //  import/export
        
    public function getSerializedValue()
    {
        if (!$this->isNull() && $this->isValid()) {
            return serialize(array($this->getADT()->getNumber()));
        }
    }
    
    public function setSerializedValue($a_value)
    {
        $a_value = unserialize($a_value);
        if (is_array($a_value)) {
            $this->getADT()->setNumber($a_value[0]);
        }
    }
}
