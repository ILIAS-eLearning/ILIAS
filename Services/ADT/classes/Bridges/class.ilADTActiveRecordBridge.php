<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/ActiveRecord/classes/Fields/class.arField.php";

/**
 * ADT DB bridge base class
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesADT
 */
abstract class ilADTActiveRecordBridge
{
	protected $adt; // [ilADT]
	protected $id; // [string]
	protected $tabe; // [string]
	protected $primary; // [array]
	
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
		if(!$this->isValidADT($a_adt))
		{
			throw new Exception('ADTActiveRecordBridge Type mismatch.');
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
	 * Set table name
	 * 
	 * @param string $a_table
	 */
	public function setTable($a_table)
	{
		$this->table = (string)$a_table;
	}
	
	/**
	 * Get table name
	 * 
	 * @return string 
	 */
	public function getTable()
	{
		return $this->table;
	}
	
	/**
	 * Set element id (aka DB column[s] [prefix])
	 * 
	 * @param string $a_value
	 */
	public function setElementId($a_value)
	{
		$this->id = (string)$a_value;
	}
	
	/**
	 * Get element id
	 * 
	 * @return string 
	 */
	public function getElementId()
	{
		return $this->id;
	}
	
	/**
	 * Set primary fields (in MDB2 format)
	 * 
	 * @param array $a_value
	 */
	public function setPrimary(array $a_value)
	{
		$this->primary = $a_value;
	}
	
	/**
	 * Get primary fields
	 * 
	 * @return array 
	 */
	public function getPrimary()
	{
		return $this->primary;
	}
	
	
	// 
	// active record
	// 
	
	/**
	 * Convert ADT to active record fields 
	 * 
	 * @return array
	 */
	abstract public function getActiveRecordFields();	
	
	
	/**
	 * Get field value
	 * 
	 * @param string $a_field_name
	 * @return mixed
	 */
	abstract public function getFieldValue($a_field_name);
	
	/**
	 * Set field value
	 * 
	 * @param string $a_field_name
	 * @param mixed $a_field_value
	 */
	abstract public function setFieldValue($a_field_name, $a_field_value);
}
