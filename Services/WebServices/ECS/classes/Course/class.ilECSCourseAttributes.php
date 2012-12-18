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
