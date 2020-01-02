<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ADT presentation bridge base class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesADT
 */
abstract class ilADTPresentationBridge
{
    protected $adt; // [ilADT]
    protected $decorator; // [String|Array]
    
    /**
     * Constructor
     *
     * @param ilADT $a_adt
     * @return self
     */
    public function __construct(ilADT $a_adt)
    {
        $this->setADT($a_adt);
    }
    
    
    //
    // properties
    //
    
    /**
     * Check if given ADT is valid
     *
     * :TODO: This could be avoided with type-specifc constructors
     * :TODO: bridge base class?
     *
     * @param ilADT $a_adt
     */
    abstract protected function isValidADT(ilADT $a_adt);
    
    /**
     * Set ADT
     *
     * @throws Exception
     * @param ilADT $a_adt
     */
    protected function setADT(ilADT $a_adt)
    {
        if (!$this->isValidADT($a_adt)) {
            throw new Exception('ADTPresentationBridge Type mismatch.');
        }
        
        $this->adt = $a_adt;
    }
    
    /**
     * Get ADT
     *
     * @return ilADT
     */
    public function getADT()
    {
        return $this->adt;
    }
    
    /**
     * Get list presentation
     *
     * @return string
     */
    public function getList()
    {
        return $this->getHTML();
    }
        
    /**
     * Get HTML presentation
     *
     * @return string
     */
    abstract public function getHTML();
    
    /**
     * Get sortable value presentation
     *
     * @return mixed
     */
    abstract public function getSortable();
    
    /**
     * Set decorator callback
     *
     * @param string|array $a_callback
     */
    public function setDecoratorCallBack($a_callback)
    {
        $this->decorator = $a_callback;
    }
    
    /**
     * Decorate value
     *
     * @param mixed $a_value
     * @return mixed
     */
    protected function decorate($a_value)
    {
        if (is_callable($this->decorator)) {
            $a_value = call_user_func($this->decorator, $a_value);
        }
        return $a_value;
    }
}
