<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Storage of course attributes for assignment rules
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSCourseAttribute
{
	private $id = 0;
	private $server_id = 0;
	private $mid = 0;
	private $name = '';

	/**
	 * Constructor
	 * @param int $attribute_id
	 */
	public function __construct($a_id = 0)
	{
		$this->id = $a_id;
		
		$this->read();
	}
	
	/**
	 * Get id
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}
	
	public function setServerId($a_server_id)
	{
		$this->server_id = $a_server_id;
	}
	
	public function getServerId()
	{
		return $this->server_id;
	}
	
	public function setMid($a_mid)
	{
		$this->mid = $a_mid;
	}
	
	public function getMid()
	{
		return $this->mid;
	}


	public function setName($a_name)
	{
		$this->name = $a_name;
	}
	
	/**
	 * Get name
	 * @return type
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Delete attribute
	 * @global type $ilDB
	 * @return boolean
	 */
	public function delete()
	{
		global $ilDB;
		
		$query = "DELETE FROM ecs_crs_mapping_atts ".
				'WHERE id = '.$ilDB->quote($this->getId(),'integer');
		$ilDB->manipulate($query);
		return true;
	}

	/**
	 * Save a new entry
	 * @global type $ilDB
	 * @return boolean
	 */
	public function save()
	{
		global $ilDB;
		
		$this->id = $ilDB->nextId('ecs_crs_mapping_atts');
		
		$query = 'INSERT INTO ecs_crs_mapping_atts (id,sid,mid,name) '.
				'VALUES ( '.
				$ilDB->quote($this->getId(),'integer').', '.
				$ilDB->quote($this->getServerId(),'integer').', '.
				$ilDB->quote($this->getMid(),'integer').', '.
				$ilDB->quote($this->getName(),'text').' '.
				') ';
		$ilDB->manipulate($query);
		return true;
	}


	
	/**
	 * read active attributes
	 */
	protected function read()
	{
		global $ilDB;
		
		if(!$this->getId())
		{
			return true;
		}
		
		
		$query = 'SELECT * FROM ecs_crs_mapping_atts '.
				'WHERE id = '.$ilDB->quote($this->getId(),'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setName($row->name);
		}
		return true;
	}
}
?>
