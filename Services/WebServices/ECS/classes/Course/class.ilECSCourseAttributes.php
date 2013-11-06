<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseAttribute.php';

/**
 * Storage of course attributes for assignment rules
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSCourseAttributes 
{
	private static $instances = null;

	private $server_id = 0;
	private $mid = 0;
	
	private $attributes = array();
	
	/**
	 * Constructor
	 */
	public function __construct($a_server_id,$a_mid)
	{
		$this->server_id = $a_server_id;
		$this->mid = $a_mid;
		
		$this->read();
	}
	
	/**
	 * Get instance
	 * @param type $a_server_id
	 * @param type $a_mid
	 * @return ilECSCourseAttributes
	 */
	public static function getInstance($a_server_id,$a_mid)
	{
		if(isset(self::$instances[$a_server_id.'_'.$a_mid]))
		{
			return self::$instances[$a_server_id.'_'.$a_mid];
		}
		return self::$instances[$a_server_id.'_'.$a_mid] = new ilECSCourseAttributes($a_server_id,$a_mid);
	}
	
	/**
	 * Get current attributes
	 * @return type
	 */
	public function getAttributes()
	{
		return (array) $this->attributes;
	}
	
	/**
	 * Get first defined attribute
	 * @return ilECSCourseAttribute
	 */
	public function getFirstAttribute()
	{
		foreach ($this->getAttributes() as $att)
		{
			return $att;
		}
		return NULL;
	}
	
	/**
	 * Get first attribute name
	 * @return type
	 */
	public function getFirstAttributeName()
	{
		if($this->getFirstAttribute() instanceof ilECSCourseAttribute)
		{
			return $this->getFirstAttribute()->getName();
		}
		return '';
	}
	
	/**
	 * Get attribute sequence
	 * @param type $a_last_attribute
	 * @return type
	 */
	public function getAttributeSequence($a_last_attribute)
	{
		if(!$a_last_attribute)
		{
			return array();
		}
		$sequence = array();
		foreach ($this->getAttributes() as $att)
		{
			$sequence[] = $att->getName();
			if($a_last_attribute == $att->getName())
			{
				break;
			}
		}
		return $sequence;
	}
	
	/**
	 * Get upper attributes in hierarchy
	 * @param type $a_name
	 */
	public function getUpperAttributes($a_name)
	{
		$reverse_attributes = array_reverse($this->getAttributes());
		
		$found = false;
		$upper = array();
		foreach ($reverse_attributes as $att)
		{
			if($att->getName() == $a_name)
			{
				$found = true;
				continue;
			}
			if($found)
			{
				$upper[] = $att->getName();
			}
		}
		return array_reverse($upper);
	}
	
	/**
	 * Get next attribute name in sequence
	 * @param string $a_name
	 */
	public function getNextAttributeName($a_name)
	{
		if(!$a_name)
		{
			return $this->getFirstAttributeName();
		}
		$found = false;
		foreach($this->getAttributes() as $att)
		{
			if($a_name == $att->getName())
			{
				$found = true;
				continue;
			}
			if($found)
			{
				return $att->getName();
			}
		}
		return '';
	}
	
	/**
	 * Get next attribute name in sequence
	 * @param string $a_name
	 */
	public function getPreviousAttributeName($a_name)
	{
		if(!$a_name)
		{
			return '';
		}
		$found = false;
		$reverse_attributes = array_reverse($this->getAttributes());
		foreach($reverse_attributes as $att)
		{
			if($a_name == $att->getName())
			{
				$found = true;
				continue;
			}
			if($found)
			{
				return $att->getName();
			}
		}
		return '';
	}

	/**
	 * Get active attribute values
	 */
	public function getAttributeValues()
	{
		$values = array();
		foreach ($this->getAttributes() as $att)
		{
			$values[] = $att->getName();
		}
		return $values;
	}
	
	/**
	 * Delete all mappings
	 */
	public function delete()
	{
		foreach($this->getAttributes() as $att)
		{
			$att->delete();
		}
		$this->attributes = array();
	}


	/**
	 * Read attributes
	 * @global type $ilDB
	 */
	protected function read()
	{
		global $ilDB;
		
		$this->attributes = array();
		
		$query = 'SELECT * FROM ecs_crs_mapping_atts '.
				'WHERE sid = '.$ilDB->quote($this->server_id,'integer').' '.
				'AND mid = '.$ilDB->quote($this->mid,'integer').' '.
				'ORDER BY id';
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->attributes[] = new ilECSCourseAttribute($row->id);
		}
	}
}
?>
