<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ADT base class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesADT
 */
abstract class ilADT
{
    protected $definition; // [ilADTDefinition]
    protected $validation_errors = []; // [array]
    
    
    // :TODO: error codes for ALL types - see self::translateErrorMessage()
    
    const ADT_VALIDATION_ERROR_NULL_NOT_ALLOWED = "adt1";
    
    // text-based
    const ADT_VALIDATION_ERROR_MAX_LENGTH = "adt2";
    
    // multi
    const ADT_VALIDATION_ERROR_MAX_SIZE = "adt3";
    
    // number-based
    const ADT_VALIDATION_ERROR_MIN = "adt4";
    const ADT_VALIDATION_ERROR_MAX = "adt5";
    
    // date-based
    const ADT_VALIDATION_DATE = "adt6";
    
    // invalid target node for internal link
    const ADT_VALIDATION_ERROR_INVALID_NODE = 'adt7';
    
    /**
     * Constructor
     *
     * @return self
     */
    public function __construct(ilADTDefinition $a_def)
    {
        $this->setDefinition($a_def);
        $this->reset();
    }
    
    /**
     * Get type (from class/instance)
     *
     * @return string
     */
    public function getType()
    {
        return $this->getDefinition()->getType();
    }
            
    /**
     * Init property defaults
     */
    protected function reset()
    {
    }
    
    
    //
    // definition
    //
    
    /**
     * Check if definition is valid for ADT
     *
     * @return bool;
     */
    abstract protected function isValidDefinition(ilADTDefinition $a_def);
    
    /**
     * Set definition
     *
     * @throws ilException
     * @param ilADTDefinition $a_def
     */
    protected function setDefinition(ilADTDefinition $a_def)
    {
        if ($this->isValidDefinition($a_def)) {
            $this->definition = clone $a_def;
        } else {
            throw new ilException("ilADT invalid definition");
        }
    }
    
    /**
     * Get definition
     *
     * @return ilADTDefinition $a_def
     */
    protected function getDefinition()
    {
        return $this->definition;
    }
    
    /**
     * Get copy of definition
     *
     * @return ilADTDefinition $a_def
     */
    public function getCopyOfDefinition()
    {
        return (clone $this->definition);
    }
            
    
    //
    // comparison
    //
        
    /**
     * Check if given ADT equals self
     *
     * @param ilADT $a_adt
     * @return bool
     */
    abstract public function equals(ilADT $a_adt);
    
    /**
     * Check if given ADT is larger than self
     *
     * @param ilADT $a_adt
     * @return bool
     */
    abstract public function isLarger(ilADT $a_adt);
    
    /**
     * Check if given ADT is larger or equal than self
     *
     * @param ilADT $a_adt
     * @return bool
     */
    public function isLargerOrEqual(ilADT $a_adt)
    {
        return ($this->equals($a_adt) ||
            $this->isLarger($a_adt));
    }
    
    /**
     * Check if given ADT is smaller than self
     *
     * @param ilADT $a_adt
     * @return bool
     */
    abstract public function isSmaller(ilADT $a_adt);
    
    /**
     * Check if given ADT is smaller or equal than self
     *
     * @param ilADT $a_adt
     * @return bool
     */
    public function isSmallerOrEqual(ilADT $a_adt)
    {
        return ($this->equals($a_adt) ||
            $this->isSmaller($a_adt));
    }
    
    /**
     * Check if self is inbetween given ADTs (exclusive)
     *
     * @param ilADT $a_adt_from
     * @param ilADT $a_adt_to
     * @return bool
     */
    public function isInbetween(ilADT $a_adt_from, ilADT $a_adt_to)
    {
        return ($this->isLarger($a_adt_from) &&
            $this->isSmaller($a_adt_to));
    }
    
    /**
     * Check if self is inbetween given ADTs (inclusive)
     *
     * @param ilADT $a_adt_from
     * @param ilADT $a_adt_to
     * @return bool
     */
    public function isInbetweenOrEqual(ilADT $a_adt_from, ilADT $a_adt_to)
    {
        return ($this->equals($a_adt_from) ||
            $this->equals($a_adt_to) ||
            $this->isInbetween($a_adt_from, $a_adt_to));
    }
    
    
    //
    // null
    //
    
    /**
     * Is currently null
     *
     * @return bool
     */
    abstract public function isNull();
    
    
    //
    // validation
    //
    
    /**
     * Is currently valid
     *
     * @return boolean
     */
    public function isValid()
    {
        $this->validation_errors = array();
        
        if (!$this->getDefinition()->isNullAllowed() && $this->isNull()) {
            $this->addValidationError(self::ADT_VALIDATION_ERROR_NULL_NOT_ALLOWED);
            return false;
        }
        return true;
    }
    
    /**
     * Add validation error code
     *
     * @param int $a_error_code
     */
    protected function addValidationError($a_error_code)
    {
        $this->validation_errors[] = (string) $a_error_code;
    }
    
    /**
     * Get all validation error codes
     *
     * @see isValid()
     * @return array
     */
    public function getValidationErrors()
    {
        if (is_array($this->validation_errors) &&
            sizeof($this->validation_errors)) {
            return array_unique($this->validation_errors);
        }
        return array();
    }
    
    /**
     * Translate error-code to human-readable message
     *
     * @throws Exception
     * @param int $a_code
     * @return string
     */
    public function translateErrorCode($a_code)
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        // $lng->txt("msg_wrong_format");
        
        switch ($a_code) {
            case self::ADT_VALIDATION_ERROR_NULL_NOT_ALLOWED:
                return $lng->txt("msg_input_is_required");
                
            case self::ADT_VALIDATION_ERROR_MAX_LENGTH:
                return $lng->txt("adt_error_max_length");
                
            case self::ADT_VALIDATION_ERROR_MAX_SIZE:
                return $lng->txt("adt_error_max_size");
            
            case self::ADT_VALIDATION_ERROR_MIN:
                return $lng->txt("form_msg_value_too_low");
                
            case self::ADT_VALIDATION_ERROR_MAX:
                return $lng->txt("form_msg_value_too_high");
                
            // :TODO: currently not used - see ilDateTimeInputGUI
            case self::ADT_VALIDATION_DATE:
                return $lng->txt("exc_date_not_valid");
                
            default:
                throw new Exception("ADT unknown error code");
        }
    }
    
    /**
     * Get unique checksum
     *
     * @return string
     */
    abstract public function getCheckSum();
}
