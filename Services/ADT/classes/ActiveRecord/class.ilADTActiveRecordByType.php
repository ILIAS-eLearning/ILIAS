<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ADT Active Record by type helper class
 * 
 * This class expects a valid primary for all actions!
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesADT
 */
class ilADTActiveRecordByType
{
	protected $properties; // [ilADTGroupDBBridge]
	protected $element_column; // [string]
	protected $element_column_type; // [string]
	protected $tables_map; // [array]
	protected $tables_map_type; // [array]
	
	static protected $preloaded; // [array]
	
	const SINGLE_COLUMN_NAME = "value";
	
	/**
	 * Constructor
	 * 
	 * @param ilADTGroupDBBridge $a_properties
	 * @return self
	 */
	public function __construct(ilADTGroupDBBridge $a_properties)
	{
		$this->properties = $a_properties;
		$this->init();		
	}
		
	protected function init()
	{
		$this->tables_map = self::getTablesMap();
		
		// type to table lookup
		$this->tables_map_type = array();
		foreach($this->tables_map as $table => $types)
		{
			foreach($types as $type)
			{
				$this->tables_map_type[$type] = $table;
			}
		}
	}
	
	
	//
	// properties
	// 
	
	/**
	 * Set element id column name 
	 * 
	 * @param string $a_name
	 * @param string $a_type
	 */
	public function setElementIdColumn($a_name, $a_type)
	{
		$this->element_column = (string)$a_name;
		$this->element_column_type = (string)$a_type;
	}
	
	/**
	 * Get element id column name 
	 * 
	 * @return string 
	 */
	public function getElementIdColumn()
	{
		return $this->element_column;
	}
	
	
	//
	// table(s) handling
	// 
	
	/**
	 * mapping data types to sub-tables
	 * 
	 * @return array
	 */
	protected static function getTablesMap()
	{
		return array(
			"text" => array("Text", "Enum", "MultiEnum"),
			"int" => array("Integer"),
			"float" => array("Float"),
			"date" => array("Date"),
			"datetime" => array("DateTime"),
			"location" => array("Location")
		);
	}
	
	/**
	 * Get table name for ADT type
	 * @param string $a_type
	 * @return string
	 */
	protected function getTableForElementType($a_type)
	{
		if(isset($this->tables_map_type[$a_type]))
		{
			return $this->properties->getTable()."_".$this->tables_map_type[$a_type];
		}
	}		
	
	/**
	 * Map all group elements to sub tables
	 * 
	 * @return array
	 */
	protected function mapElementsToTables()
	{
		$res = array();
		
		foreach($this->properties->getElements() as $element_id => $element)
		{
			$table = $this->getTableForElementType($element->getADT()->getType());
			if($table)
			{
				$res[$table][] = $element_id;
			}
		}
		
		return $res;
	}
	
	
	// 
	// CRUD
	//

	/**
	 * process raw data for ADT import
	 * 
	 * @param string $a_sub_table
	 * @return array
	 */
	protected function processTableRowForElement($a_sub_table, $a_element_id, array $a_row)
	{		
		switch($a_sub_table)
		{
			case "location":				
				return array(
					$a_element_id."_lat" => $a_row["loc_lat"],
					$a_element_id."_long" => $a_row["loc_long"],
					$a_element_id."_zoom" => $a_row["loc_zoom"]
				);	
				break;

			default:
				if($a_row[self::SINGLE_COLUMN_NAME] !== null)
				{
					return array($a_element_id=>$a_row[self::SINGLE_COLUMN_NAME]);
				}
				break;
		}						
	}
	
	/**
	 * Read record
	 * 
	 * @param bool $a_return_additional_data
	 * @return bool|array
	 */
	public function read($a_return_additional_data = false)
	{		
		global $ilDB;
		
		// reset all group elements
		$this->properties->getADT()->reset();
		
		//  using preloaded data
		if(is_array(self::$preloaded))
		{
			$primary = $this->properties->getPrimary();
			foreach(self::$preloaded as $table => $data)
			{
				$sub_table = array_pop(explode("_", $table));
				
				foreach($data as $row)
				{
					// match by primary key
					foreach($primary as $primary_field => $primary_value)
					{
						if($row[$primary_field] != $primary_value[1])
						{
							continue(2);
						}
					}
					
					$element_id = $row[$this->getElementIdColumn()];
					if($this->properties->getADT()->hasElement($element_id))
					{
						$element_row =  $this->processTableRowForElement($sub_table, $element_id, $row);		
						if(is_array($element_row))
						{
							$this->properties->getElement($element_id)->readRecord($element_row);					
						}
					}
				}
			}
			return;
		}
		
		
		$has_data = false;
		$additional = array();
		
		// read minimum tables
		foreach($this->mapElementsToTables() as $table => $element_ids)
		{									
			$sql = "SELECT * FROM ".$table.
				" WHERE ".$this->properties->buildPrimaryWhere();
			$set = $ilDB->query($sql);
			if($ilDB->numRows($set))
			{		
				$sub_table = array_pop(explode("_", $table));
				
				while($row = $ilDB->fetchAssoc($set))
				{
					$element_id = $row[$this->getElementIdColumn()];	
					if(in_array($element_id, $element_ids))
					{
						$has_data = true;
						
						$element_row =  $this->processTableRowForElement($sub_table, $element_id, $row);						
						$this->properties->getElement($element_id)->readRecord($element_row);
								
						if($a_return_additional_data)
						{
							// removing primary and field id
							foreach(array_keys($this->properties->getPrimary()) as $key)
							{
								unset($row[$key]);
							}								
							unset($row[$this->getElementIdColumn()]);
							$additional[$element_id] = $row;
						}	
					}
					else
					{
						// :TODO: element no longer valid - delete?
					}
				}
			}
		}		
		
		if($a_return_additional_data)
		{
			return $additional;
		}
		return $has_data;
	}
	
	/**
	 * Create/insert record
	 * 
	 * @param array $a_additional_data 
	 */
	public function write(array $a_additional_data = null)
	{		
		global $ilDB;				
				
		// find existing entries
		$existing = array();
		foreach(array_keys($this->mapElementsToTables()) as $table)
		{	
			$sql = "SELECT ".$this->getElementIdColumn()." FROM ".$table.
				" WHERE ".$this->properties->buildPrimaryWhere();
			$set = $ilDB->query($sql);
			while($row = $ilDB->fetchAssoc($set))
			{		
				$id = $row[$this->getElementIdColumn()];
				
				// leave other records alone
				if($this->properties->getADT()->hasElement($id))
				{
					$existing[$table][$id] = $id;				
				}
			}
		}
		
				
		$tmp = array();
		
		// gather ADT values and distribute by sub-table
		foreach($this->properties->getElements() as $element_id => $element)
		{
			if(!$element->getADT()->isNull())
			{
				$table = $this->getTableForElementType($element->getADT()->getType());
				if($table)
				{						
					$fields = array();												
					$element->prepareUpdate($fields);										

					if(sizeof($fields) == 1)
					{
						$tmp[$table][$element_id][self::SINGLE_COLUMN_NAME] = $fields[$element_id];
					}
					else
					{
						foreach($fields as $key => $value)
						{				
							$key = substr($key, strlen($element_id)+1);
							if(substr($table, -8) == "location")
							{
								// long is reserved word
								$key = "loc_".$key;
							}
							$tmp[$table][$element_id][$key] = $value;
						}
					}
					
					if(isset($a_additional_data[$element_id]))
					{			
						$tmp[$table][$element_id] = array_merge($tmp[$table][$element_id], $a_additional_data[$element_id]);
					}		
				}
			}
		}
		
		// update/insert in sub tables
		if(sizeof($tmp))
		{			
			foreach($tmp as $table => $elements)
			{			
				foreach($elements as $element_id => $fields)
				{
					if(isset($existing[$table][$element_id]))
					{
						// update
						$primary = $this->properties->getPrimary();
						$primary[$this->getElementIdColumn()] = array($this->element_column_type, $element_id);	
						unset($existing[$table][$element_id]);
						$ilDB->update($table, $fields, $primary);
					}
					else
					{					
						// insert
						$fields[$this->getElementIdColumn()] = array($this->element_column_type, $element_id);															
						$fields = array_merge($this->properties->getPrimary(), $fields);
						$ilDB->insert($table, $fields);
					}
				}				
			}
		}	
		
		// remove all existing values that are now null
		if(sizeof($existing))
		{
			foreach($existing as $table => $element_ids)
			{
				if($element_ids)
				{				
					$ilDB->manipulate("DELETE FROM ".$table.
						" WHERE ".$this->properties->buildPrimaryWhere().
						" AND ".$ilDB->in($this->getElementIdColumn(), $element_ids, "", $this->element_column_type));
				}
			}
		}
	}		
	
	/**
	 * Delete record
	 */
	/*
	public function delete()
	{
		global $ilDB;			
		
		foreach(array_keys($this->tables_map) as $table)
		{
			$sql = "DELETE FROM ".$this->properties->getTable()."_".$table.
				" WHERE ".$this->properties->buildPrimaryWhere();
			$ilDB->manipulate($sql);
		}				
	}	
	*/
	
		
	//
	// helper methods (working via primary)
	// 
	
	/**
	 * Build where condition for (partial) primary 
	 *
	 * @param array $a_primary
	 * @return string
	 */
	protected static function buildPartialPrimaryWhere(array $a_primary)
	{
		global $ilDB;
		
		// using DB only, no object instances required
	
		$where = array();
		
		foreach($a_primary as $field => $def)
		{
			if(!is_array($def[1]))
			{		
				$where[] = $field."=".$ilDB->quote($def[1], $def[0]);
			}
			else
			{
				$where[] = $ilDB->in($field, $def[1], "", $def[0]);
			}
		}	
		
		if(sizeof($where))
		{
			return implode(" AND ", $where);
		}
	}
	
	/**
	 * Delete values by (partial) primary key
	 * 
	 * @param string $a_table
	 * @param array $a_primary
	 * @param string $a_type
	 */
	public static function deleteByPrimary($a_table, array $a_primary, $a_type = null)
	{
		global $ilDB;		
		
		// using DB only, no object instances required
		
		$where = self::buildPartialPrimaryWhere($a_primary);
		if(!$where)
		{
			return;
		}	
	
		// all tables
		if(!$a_type)
		{
			foreach(array_keys(self::getTablesMap()) as $table)
			{
				$sql = "DELETE FROM ".$a_table."_".$table.
					" WHERE ".$where;
				$ilDB->manipulate($sql);
			}	
		}
		// type-specific table
		else
		{
			$found = null;
			foreach(self::getTablesMap() as $table => $types)
			{
				if(in_array($a_type, $types))
				{
					$found = $table;
					break;
				}
			}			
			if($found)
			{
				$sql = "DELETE FROM ".$a_table."_".$found.
					" WHERE ".$where;
				$ilDB->manipulate($sql);
			}
		}
	}	
	
	/**
	 * Read values by (partial) primary key
	 * 
	 * @param string $a_table
	 * @param array $a_primary
	 */
	public static function preloadByPrimary($a_table, array $a_primary)
	{
		global $ilDB;
		
		$where = self::buildPartialPrimaryWhere($a_primary);
		if(!$where)
		{
			return false;
		}	
		
		self::$preloaded = array();
		
		foreach(array_keys(self::getTablesMap()) as $table)
		{
			$sql = "SELECT * FROM ".$a_table."_".$table.
				" WHERE ".$where;
			$set = $ilDB->query($sql);
			while($row = $ilDB->fetchAssoc($set))
			{
				self::$preloaded[$table][] = $row;
			}
		}	
	
		return true;
	}
	
	/**
	 * mapping sub-table to value field types
	 * 
	 * @return array
	 */
	protected static function getTableTypeMap()
	{
		return array(			
			"text" => "text",
			"int" => "integer",
			"float" => "float",
			"date" => "date",
			"datetime" => "timestamp"
		);
	}
			
	
	/**
	 * Clone values by (partial) primary key
	 * 
	 * @param string $a_table
	 * @param array $a_primary_def
	 * @param array $a_source_primary
	 * @param array $a_target_primary	 
	 * @param array $a_additional	 
	 * @return bool
	 */
	public static function cloneByPrimary($a_table, array $a_primary_def, array $a_source_primary, array $a_target_primary, array $a_additional = null)
	{
		global $ilDB;
		
		// using DB only, no object instances required
		
		$where = self::buildPartialPrimaryWhere($a_source_primary);
		if(!$where)
		{
			return false;
		}	
		
		$has_data = false;
		
		$type_map = self::getTableTypeMap();
		
		foreach(array_keys(self::getTablesMap()) as $table)
		{
			$sub_table = $a_table."_".$table;
			
			$sql = "SELECT * FROM ".$sub_table.
				" WHERE ".$where;
			$set = $ilDB->query($sql);
			if($ilDB->numRows($set))
			{
				$has_data = true;
				
				while($row = $ilDB->fetchAssoc($set))
				{
					$fields = array();
					
					// primary fields
					foreach($a_primary_def as $pfield => $ptype)
					{
						// make source to target primary
						if(array_key_exists($pfield, $a_target_primary))
						{
							$row[$pfield] = $a_target_primary[$pfield][1];
						}
						$fields[$pfield] = array($ptype, $row[$pfield]);
					}
									
					// value field(s)
					switch($table)
					{
						case "location":
							$fields["loc_lat"] = array("float", $row["loc_lat"]);
							$fields["loc_long"] = array("float", $row["loc_long"]);
							$fields["loc_zoom"] = array("integer", $row["loc_zoom"]);
							break;
														
						default:
							$fields[self::SINGLE_COLUMN_NAME] = array($type_map[$table], $row[self::SINGLE_COLUMN_NAME]);
							break;
					}
					
					// additional data
					if($a_additional)
					{
						foreach($a_additional as $afield => $atype)
						{
							$fields[$afield] = array($atype, $row[$afield]);
						}
					}
					
					$ilDB->insert($sub_table, $fields);
				}
			}
		}	
	
		return $has_data;
	}
	
	/**
	 * Read directly 
	 * 
	 * @param string $a_table
	 * @param array $a_primary
	 * @param string $a_type
	 */
	public static function readByPrimary($a_table, array $a_primary, $a_type = null)
	{
		global $ilDB;		
		
		// using DB only, no object instances required
		
		$where = self::buildPartialPrimaryWhere($a_primary);
		if(!$where)
		{
			return;
		}	
		
		$res = array();
	
		// all tables
		if(!$a_type)
		{
			foreach(array_keys(self::getTablesMap()) as $table)
			{
				$sql = "SELECT * FROM ".$a_table."_".$table.
					" WHERE ".$where;
				$set = $ilDB->query($sql);
				while($row = $ilDB->fetchAssoc($set))
				{
					$res[] = $row;
				}
			}	
		}
		// type-specific table
		else
		{
			$found = null;
			foreach(self::getTablesMap() as $table => $types)
			{
				if(in_array($a_type, $types))
				{
					$found = $table;
					break;
				}
			}			
			if($found)
			{
				$sql = "SELECT * FROM ".$a_table."_".$found.
					" WHERE ".$where;
				$set = $ilDB->query($sql);
				while($row = $ilDB->fetchAssoc($set))
				{
					$res[] = $row;
				}
			}
		}
		
		return $res;
	}
	
	/**
	 * Write directly 
	 * 
	 * @param string $a_table
	 * @param array $a_primary
	 * @param string $a_type
	 * @param mixed $a_value
	 */
	public static function writeByPrimary($a_table, array $a_primary, $a_type, $a_value)
	{
		global $ilDB;		
		
		// using DB only, no object instances required
		
		$where = self::buildPartialPrimaryWhere($a_primary);
		if(!$where)
		{
			return;
		}	
		
		// type-specific table	
		$found = null;
		foreach(self::getTablesMap() as $table => $types)
		{
			if(in_array($a_type, $types))
			{
				$found = $table;
				break;
			}
		}			
		if($found)
		{
			$type_map = self::getTableTypeMap();
			
			$sql = "UPDATE ".$a_table."_".$found.
				" SET ".self::SINGLE_COLUMN_NAME."=".$ilDB->quote($a_value, $type_map[$found]).
				" WHERE ".$where;
			$ilDB->manipulate($sql);			
		}		
	}
	
	/**
	 * Find entries
	 * 
	 * @param string $a_table
	 * @param string $a_type
	 * @param int $a_field_id
	 * @param string $a_condition
	 * @param string $a_additional_fields
	 * @return array
	 */
	public static function find($a_table, $a_type, $a_field_id, $a_condition, $a_additional_fields = null)	
	{
		global $ilDB;
		
		// type-specific table	
		$found = null;
		foreach(self::getTablesMap() as $table => $types)
		{
			if(in_array($a_type, $types))
			{
				$found = $table;
				break;
			}
		}			
		if($found)
		{				
			$res = array();
			
			$sql = "SELECT *".$a_additional_fields.
				" FROM ".$a_table."_".$found.
				" WHERE field_id = ".$ilDB->quote($a_field_id, "integer").
				" AND ".$a_condition;
			$set = $ilDB->query($sql);
			while($row = $ilDB->fetchAssoc($set))
			{
				$res[] = $row;
			}
			
			return $res;
		}				
	}
}

?>