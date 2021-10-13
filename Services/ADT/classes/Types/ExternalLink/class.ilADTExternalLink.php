<?php

class ilADTExternalLink extends ilADT
{
    public const MAX_LENGTH = 500;
    
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
    protected function isValidDefinition(ilADTDefinition $a_def) : bool
    {
        return $a_def instanceof ilADTExternalLinkDefinition;
    }

    /**
     * Reset
     */
    public function reset() : void
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
     * @return string
     */
    public function getUrl()
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
    

    public function isValid() : bool
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
    public function getCheckSum() : ?string
    {
        if (!$this->isNull()) {
            return md5($this->getUrl() . $this->getTitle());
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
            $obj->url = $this->getUrl();
            $obj->title = $this->getTitle();
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
            $this->setTitle($a_std->title);
            $this->setUrl($a_std->url);
        }
    }
}
