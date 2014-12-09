<?php

require_once "Services/ADT/classes/class.ilADT.php";
require_once "Services/ADT/classes/class.ilADTDefinition.php";

class ilADTFactory
{
	protected static $instance; // [ilADTFactory]
	
	/**
	 * Constructor
	 * 
	 * @return self
	 */
	protected function __construct() 
	{
		
	}
	
	/**
	 * Get singleton 
	 * 
	 * @return self
	 */
	public static function getInstance()
	{
		if(self::$instance === null)
		{
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/**
	 * Get all ADT types
	 * 
	 * @return array
	 */
	public function getValidTypes()
	{
		return array("Float", "Integer", "Location", "Text", "Boolean", 
			"MultiText", "Date", "DateTime", "Enum", "MultiEnum", "Group");	
	}
	
	/**
	 * Check if given type is valid
	 * 
	 * @param string $a_type
	 * @return bool
	 */
	public function isValidType($a_type)
	{
		return in_array((string)$a_type, $this->getValidTypes());
	}
	
	/**
	 * Init type-specific class
	 * 
	 * @throws Exception
	 * @param string $a_type
	 * @param string $a_class
	 * @return string	
	 */
	public function initTypeClass($a_type, $a_class = null)
	{
		if($this->isValidType($a_type))
		{
			$class = "ilADT".$a_type.$a_class;
			$file = "Services/ADT/classes/Types/".$a_type."/class.".$class.".php";
			if(file_exists($file))
			{
				require_once $file;
				return $class;
			}			
		}
		
		throw new Exception("ilADTFactory unknown type");
	}
	
	/**
	 * Get instance of ADT definition
	 * 
	 * @throws Exception
	 * @param string $a_type
	 * @return ilADTDefinition 
	 */
	public function getDefinitionInstanceByType($a_type)
	{
		$class = $this->initTypeClass($a_type, "Definition");		
		return new $class();		
	}	
	
	/**
	 * Get instance of ADT
	 * 
	 * @throws Exception
	 * @param ilADTDefinition $a_def
	 * @return ilADT	 
	 */
	public function getInstanceByDefinition(ilADTDefinition $a_def)
	{
		if(!method_exists($a_def, "getADTInstance"))
		{			
			$class = $this->initTypeClass($a_def->getType());		
			return new $class($a_def);		
		}
		else
		{
			return $a_def->getADTInstance();
		}
	}			
	
	
	//
	// bridges
	// 
	
	/**
	 * Get form bridge instance for ADT
	 * 
	 * @throws Exception
	 * @param ilADT $a_adt
	 * @return ilADTFormBridge	 
	 */
	public function getFormBridgeForInstance(ilADT $a_adt)
	{		
		$class = $this->initTypeClass($a_adt->getType(), "FormBridge");		
		return new $class($a_adt);
	}
	
	/**
	 * Get DB bridge instance for ADT
	 * 
	 * @throws Exception
	 * @param ilADT $a_adt
	 * @return ilADTDBBridge	 
	 */
	public function getDBBridgeForInstance(ilADT $a_adt)
	{
		$class = $this->initTypeClass($a_adt->getType(), "DBBridge");		
		return new $class($a_adt);	
	}
	
	/**
	 * Get presentation bridge instance for ADT
	 * 
	 * @throws Exception
	 * @param ilADT $a_adt
	 * @return ilADTPresentationBridge
	 */
	public function getPresentationBridgeForInstance(ilADT $a_adt)
	{
		$class = $this->initTypeClass($a_adt->getType(), "PresentationBridge");		
		return new $class($a_adt);	
	}
	
	/**
	 * Get search bridge instance for ADT definition
	 * 
	 * @param ilADTDefinition $a_adt_def
	 * @param bool $a_range
	 * @param bool $a_multi
	 * @return ilADTSearchBridge
	 */
	public function getSearchBridgeForDefinitionInstance(ilADTDefinition $a_adt_def, $a_range = true, $a_multi = true)
	{
		if($a_range)
		{
			try
			{
				$class = $this->initTypeClass($a_adt_def->getType(), "SearchBridgeRange");	
				return new $class($a_adt_def);	
			}
			catch(Exception $e)
			{
				
			}
		}
		
		// multi enum search (single) == enum search (multi)		
		if(!$a_multi &&
			$a_adt_def->getType() == "MultiEnum")
		{
			$class = $this->initTypeClass("Enum", "SearchBridgeMulti");	
			return new $class($a_adt_def);	
		}	
		
		if($a_multi)
		{
			try
			{													
				$class = $this->initTypeClass($a_adt_def->getType(), "SearchBridgeMulti");	
				return new $class($a_adt_def);	
			}
			catch(Exception $e)
			{

			}
		}			
		$class = $this->initTypeClass($a_adt_def->getType(), "SearchBridgeSingle");	
		return new $class($a_adt_def);					
	}
	
	
	/**
	 * Get active record instance for ADT
	 * 
	 * @param ilADT $a_adt
	 * @return ilADTActiveRecordBridge
	 */
	public static function getActiveRecordBridgeForInstance(ilADT $a_adt)
	{
		$class = $this->initTypeClass($a_adt->getType(), "ActiveRecordBridge");		
		return new $class($a_adt);
	}
	
	
	//
	// active records
	// 
	
	/**
	 * Get active record instance
	 * 
	 * @param ilADTGroupDBBridge $a_properties
	 * @return ilADTActiveRecord
	 */
	public static function getActiveRecordInstance(ilADTGroupDBBridge $a_properties)
	{
		require_once "Services/ADT/classes/ActiveRecord/class.ilADTActiveRecord.php";
		return new ilADTActiveRecord($a_properties);
	}
	
	/**
	 * Init active record by type 
	 */
	public static function initActiveRecordByType()
	{
		require_once "Services/ADT/classes/ActiveRecord/class.ilADTActiveRecordByType.php";
	}
	
	/**
	 * Get active record by type instance
	 * 
	 * @param ilADTGroupDBBridge $a_properties
	 * @return ilADTActiveRecordByType
	 */
	public static function getActiveRecordByTypeInstance(ilADTGroupDBBridge $a_properties)
	{
		self::initActiveRecordByType();
		return new ilADTActiveRecordByType($a_properties);
	}
}

?>