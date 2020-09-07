<?php

class ilADTExternalLink extends ilADT
{
    const MAX_LENGTH = 500;
    
    /**
     * @var string
     */
    protected $value;
    
    /**
     * @var string
     */
    protected $title;
    

    /**
     * @param ilADTDefinition $a_def
     * @return bool
     */
    protected function isValidDefinition(ilADTDefinition $a_def)
    {
        return $a_def instanceof ilADTExternalLinkDefinition;
    }

    /**
     * Reset
     */
    public function reset()
    {
        parent::reset();
        $this->value = null;
        $this->title = null;
    }
    
    /**
     * Set title
     * @param string $a_title
     */
    public function setTitle($a_title = null)
    {
        if ($a_title !== null) {
            $a_title = trim($a_title);
        }
        $this->title = $a_title;
    }
    
    /**
     * Getb title
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Set url
     * @param string $a_value
     */
    public function setUrl($a_value = null)
    {
        if ($a_value !== null) {
            $a_value = trim($a_value);
        }
        $this->value = $a_value;
    }

    /**
     * Get url
     * @return type
     */
    public function getUrl()
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
        return (bool) !$this->getLength();
    }
    
    /**
     * Get length
     * @return int
     */
    public function getLength()
    {
        if (function_exists("mb_strlen")) {
            return mb_strlen($this->getUrl() . $this->getTitle(), "UTF-8");
        } else {
            return strlen($this->getUrl() . $this->getTitle());
        }
    }
    

    /**
     * is valid
     * @return boolean
     */
    public function isValid()
    {
        $valid = parent::isValid();

        if (!$this->isNull()) {
            if (self::MAX_LENGTH < $this->getLength()) {
                $valid = false;
                $this->addValidationError(self::ADT_VALIDATION_ERROR_MAX_LENGTH);
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
            return md5($this->getUrl() . $this->getTitle());
        }
    }
}
