<?php

class ilADTInternalLink extends ilADT
{
    /**
     * @var int
     */
    protected $value;
    
    /**
     * @param ilADTDefinition $a_def
     * @return bool
     */
    protected function isValidDefinition(ilADTDefinition $a_def) : bool
    {
        return $a_def instanceof ilADTInternalLinkDefinition;
    }

    /**
     * Reset
     */
    public function reset() : void
    {
        parent::reset();
        $this->value = null;
    }
    
    /**
     * Set id of target object
     * @param type $a_value
     */
    public function setTargetRefId($a_value)
    {
        $this->value = $a_value;
    }
    
    /**
     * @return int get target ref_id
     */
    public function getTargetRefId()
    {
        return $this->value;
    }

    /**
     * @param ilADT $a_adt
     * @return bool
     */
    public function equals(ilADT $a_adt) : ?bool
    {
        if ($this->getDefinition()->isComparableTo($a_adt)) {
            return strcmp($this->getCheckSum(), $a_adt->getCheckSum()) === 0;
        }
        return null;
    }

    /**
     * Is larger
     * @param ilADT $a_adt
     */
    public function isLarger(ilADT $a_adt) : ?bool
    {
        return null;
    }

    public function isSmaller(ilADT $a_adt) : ?bool
    {
        return null;
    }

    /**
     * is null
     * @return bool
     */
    public function isNull() : bool
    {
        return (bool) !$this->getTargetRefId();
    }
    

    public function isValid() : bool
    {
        $valid = parent::isValid();
        if (!$this->isNull()) {
            $tree = $GLOBALS['DIC']->repositoryTree();
            if (
                !$tree->isInTree($this->getTargetRefId()) ||
                $tree->isDeleted($this->getTargetRefId())
            ) {
                $this->valid = false;
                $this->addValidationError(self::ADT_VALIDATION_ERROR_INVALID_NODE);
            }
        }
        return $valid;
    }

    public function getCheckSum() : ?string
    {
        if (!$this->isNull()) {
            return md5($this->getTargetRefId());
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function exportStdClass() : ?stdClass
    {
        if (!$this->isNull()) {
            $obj = new stdClass();
            $obj->target_ref_id = $this->getTargetRefId();

            return $obj;
        }
        return null;
    }


    /**
     * @inheritDoc
     */
    public function importStdClass(?stdClass $a_std) : void
    {
        if (is_object($a_std)) {
            $this->setTargetRefId($a_std->target_ref_id);
        }
    }
}
