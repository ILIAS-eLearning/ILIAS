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
    protected function isValidDefinition(ilADTDefinition $a_def)
    {
        return $a_def instanceof ilADTInternalLinkDefinition;
    }

    /**
     * Reset
     */
    public function reset()
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
     *
     * @param ilADT $a_adt
     * @return type
     */
    public function equals(ilADT $a_adt)
    {
        if ($this->getDefinition()->isComparableTo($a_adt)) {
            return strcmp($this->getCheckSum(), $a_adt->getCheckSum()) === 0;
        }
    }

    /**
     * Is larger
     * @param ilADT $a_adt
     */
    public function isLarger(ilADT $a_adt)
    {
    }

    /**
     * Is smaller
     * @param ilADT $a_adt
     */
    public function isSmaller(ilADT $a_adt)
    {
    }

    /**
     * is null
     * @return bool
     */
    public function isNull()
    {
        return (bool) !$this->getTargetRefId();
    }
    

    /**
     * is valid
     * @return boolean
     */
    public function isValid()
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

    /**
     * get checksum
     * @return string
     */
    public function getCheckSum()
    {
        if (!$this->isNull()) {
            return md5($this->getTargetRefId());
        }
    }
}
