<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ADT definition base class
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesADT
 */
abstract class ilADTDefinition
{
	protected $allow_null; // [bool]
	
	/**
	 * Constructor
	 * 
	 * @return self
	 */
	public function __construct()
	{
		$this->reset();
	}
	
	/**
	 * Get type (from class/instance)
	 * 
	 * @return string
	 */
	public function getType()
	{
		return substr(substr(get_class($this), 5), 0, -10);
	}
	
	/**
	 * Init property defaults
	 */
	protected function reset()
	{
		$this->setAllowNull(true);
	}
	
	
	//
	// null
	//
	
	/**
	 * Toggle null allowed status
	 * 
	 * @param bool $a_value
	 */
	public function setAllowNull($a_value)
	{
		$this->allow_null = (bool)$a_value;
	}
	
	/**
	 * Is null currently allowed
	 * 
	 * @return bool
	 */
	public function isNullAllowed()
	{
		return $this->allow_null;
	}
	
	
	//
	// comparison
	//
	
	/**
	 * Check if given ADT is comparable to self
	 * 
	 * @param ilADT $a_adt
	 * @return bool
	 */
	abstract public function isComparableTo(ilADT $a_adt);		
}

?>