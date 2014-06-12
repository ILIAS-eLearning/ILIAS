<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ADT based-object base class
 * 
 * Currently "mixed" with ActiveRecord-pattern, could be splitted
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesADT
 */
abstract class ilADTBasedObject
{
	protected $properties = array(); // [array]
	protected $db_errors = array(); // [array]
		
	/**
	 * Constructor
	 * 
	 * Tries to read record from DB, in accordance to current ILIAS behaviour
	 * 
	 * @return self
	 */
	public function __construct()
	{		
		$this->properties = $this->initProperties();	
		
		// :TODO: to keep constructor "open" we COULD use func_get_args()
		$this->parsePrimary(func_get_args());
		$this->read();
	}
	
	
	//
	// properties
	//
	
	/**
	 * Init properties (aka set ADT definition)
	 * 
	 * @return ilADT
	 */
	abstract protected function initProperties();
	
	/**
	 * Get all properties
	 *
	 * @return array ilADT
	 */
	public function getProperties()
	{
		return $this->properties;
	}
	
	/**
	 * Validate
	 * 
	 * @return bool
	 */
	public function isValid()
	{
		return $this->properties->isValid();
	}
		
	/**
	 * Get property magic method ("get<PropertyName>()")
	 * 
	 * Setters are type-specific and cannot be magic
	 * 
	 * @throws Exception
	 * @param string $a_method
	 * @param mixed $a_value
	 * @return ilADT
	 */
	public function __call($a_method, $a_value)
	{
		$type = substr($a_method, 0, 3);
		switch($type)
		{
			case "get":
				$parsed = strtolower(preg_replace("/([A-Z])/", " $1", substr($a_method, 3)));
				$parsed = str_replace(" ", "_", trim($parsed));
				if(!$this->properties->hasElement($parsed))
				{
					throw new Exception("ilADTObject unknown property ".$parsed);
				}
				return $this->properties->getElement($parsed);
			
			default:
				throw new Exception("ilADTObject unknown method ".$parsed);				
		}
	}	
	
	
	//
	// CRUD / active record
	//
	
	/**
	 * Parse incoming primary key
	 * 
	 * @see __construct()	 
	 * @param array $a_args
	 */
	abstract protected function parsePrimary(array $a_args);
	
	/**
	 * Check if currently has primary
	 * 
	 * @return bool
	 */
	abstract protected function hasPrimary();
	
	/**
	 * Create new primary key, e.g. sequence
	 * 
	 * @return bool
	 */
	abstract protected function createPrimaryKey();
	
	/**
	 * Init (properties) DB bridge
	 * 
	 * @param ilADTGroupDBBridge $a_adt_db
	 */
	abstract protected function initDBBridge(ilADTGroupDBBridge $a_adt_db);
	
	/**
	 * Init active record helper for current table, primary and properties
	 * 
	 * @return ilADTActiveRecord
	 */
	protected function initActiveRecordInstance()
	{
		global $ilDB;
		
		if(!$this->hasPrimary())
		{
			throw new Exception("ilADTBasedObject no primary");
		}
		
		$factory = ilADTFactory::getInstance();
		$this->adt_db = $factory->getDBBridgeForInstance($this->properties);
		$this->initDBBridge($this->adt_db);
		
		// use custom error handling
		include_once "Services/ADT/classes/class.ilADTDBException.php";
		$ilDB->exception = "ilADTDBException";
						
		return $factory->getActiveRecordInstance($this->adt_db);
	}
	
	/**
	 * Read record 
	 * 
	 * @return boolean
	 */
	public function read()
	{
		if($this->hasPrimary())
		{			
			$rec = $this->initActiveRecordInstance();
			return $rec->read();				
		}
		return false;
	}
	
	/**
	 * Create record (only if valid)
	 * 
	 * @return boolean
	 */
	public function create()
	{		
		if($this->hasPrimary())
		{
			return $this->update();
		}				
		
		if($this->isValid())
		{								
			if($this->createPrimaryKey())
			{			
				try
				{
					$rec = $this->initActiveRecordInstance();
					$rec->create();	
				}
				catch(ilADTDBException $e)
				{				
					$this->db_errors[$e->getColumn()][] = $e->getCode();
					return false;
				}
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Update record (only if valid)
	 * 
	 * @return boolean
	 */
	public function update()
	{				
		if(!$this->hasPrimary())
		{
			return $this->create();
		}
		
		if($this->isValid())
		{					
			try
			{
				$rec = $this->initActiveRecordInstance();								
				$rec->update();	
			}
			catch(ilADTDBException $e)
			{			
				$this->db_errors[$e->getColumn()][] = $e->getCode();
				return false;
			}
			return true;			
		}
		return false;
	}
	
	/**
	 * Delete record
	 * 
	 * @return boolean
	 */
	public function delete()
	{
		if($this->hasPrimary())
		{			
			$rec = $this->initActiveRecordInstance();
			$rec->delete();
			return true;
		}
		return false;
	}
	
	/**
	 * Get DB errors
	 * 
	 * @return array
	 */
	public function getDBErrors()
	{		
		return $this->db_errors;
	}
	
	/**
	 * Translate DB error codes
	 * 
	 * @param array $a_codes
	 * @return array
	 */
	public function translateDBErrorCodes(array $a_codes)
	{
		global $lng;
		
		$res = array();
	
		foreach($a_codes as $code)
		{
			switch($code)
			{
				case MDB2_ERROR_CONSTRAINT:
					$res[] = $lng->txt("adt_error_db_constraint");
					break;
					
				default:
					$res[] = "Unknown ADT error code ".$code;
					break;
			}
		}
		
		return $res;
	}
	
	/**
	 * Get translated error codes (DB, Validation)
	 * 
	 * @param type $delimiter
	 * @return string
	 */
	public function getAllTranslatedErrors($delimiter = "\n")
	{
		$tmp = array();			
		
		foreach($this->getProperties()->getValidationErrorsByElements() as $error_code => $element_id)
		{
			$tmp[] = $element_id." [validation]: ".$this->getProperties()->translateErrorCode($error_code);
		}		
		
		foreach($this->getDBErrors() as $element_id => $codes)
		{
			$tmp[] = $element_id." [db]: ".implode($delimiter, $this->translateDBErrorCodes($codes));
		}
		
		if(sizeof($tmp))
		{
			return get_class($this).$delimiter.implode($delimiter, $tmp);
		}
	}
}

?>