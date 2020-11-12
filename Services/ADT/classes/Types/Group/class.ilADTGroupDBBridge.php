<?php

require_once "Services/ADT/classes/Bridges/class.ilADTDBBridge.php";

class ilADTGroupDBBridge extends ilADTDBBridge
{
    protected $elements = []; // [array]
    
    protected function isValidADT(ilADT $a_adt)
    {
        return ($a_adt instanceof ilADTGroup);
    }
    
    
    // elements
    
    protected function prepareElements()
    {
        if (sizeof($this->elements)) {
            return;
        }
        
        $this->elements = array();
        $factory = ilADTFactory::getInstance();
        
        // convert ADTs to DB bridges
        
        foreach ($this->getADT()->getElements() as $name => $element) {
            $this->elements[$name] = $factory->getDBBridgeForInstance($element);
            $this->elements[$name]->setElementId($name);
            $this->elements[$name]->setTable($this->getTable());
            $this->elements[$name]->setPrimary($this->getPrimary());
        }
    }

    /**
     * @return ilADTDBBridge[]
     */
    public function getElements()
    {
        $this->prepareElements();
        return $this->elements;
    }

    public function getElement($a_element_id)
    {
        if (array_key_exists($a_element_id, $this->getElements())) {
            return $this->elements[$a_element_id];
        }
    }
    
    
    // properties
    
    public function setTable($a_table)
    {
        parent::setTable($a_table);
        
        if (sizeof($this->elements)) {
            foreach (array_keys($this->getADT()->getElements()) as $name) {
                $this->elements[$name]->setTable($this->getTable());
            }
        }
    }
    
    public function setPrimary(array $a_value)
    {
        parent::setPrimary($a_value);
        
        if (sizeof($this->elements)) {
            foreach (array_keys($this->getADT()->getElements()) as $name) {
                $this->elements[$name]->setPrimary($this->getPrimary());
            }
        }
    }
    
    
    // CRUD
    
    public function readRecord(array $a_row)
    {
        foreach ($this->getElements() as $element) {
            $element->readRecord($a_row);
        }
    }
    
    public function prepareInsert(array &$a_fields)
    {
        foreach ($this->getElements() as $element) {
            $element->prepareInsert($a_fields);
        }
    }

    public function afterInsert()
    {
        foreach ($this->getElements() as $element) {
            $element->afterInsert();
        }
    }
    
    public function afterUpdate()
    {
        foreach ($this->getElements() as $element) {
            $element->afterUpdate();
        }
    }

    /**
     * @param string $field_type
     * @param string $field_name
     * @param int    $field_id
     */
    public function afterUpdateElement(string $field_type, string $field_name, int $field_id)
    {
        $element = $this->getElement($field_id);
        if (!$element) {
            return;
        }
        $element->setPrimary(
            array_merge(
                $this->getPrimary(),
                [
                     $field_name => [$field_type,$field_id]
                ]
            )
        );
        $element->setElementId($field_id);
        $element->afterUpdate();
    }


    public function afterDelete()
    {
        foreach ($this->getElements() as $element) {
            $element->afterDelete();
        }
    }
}
