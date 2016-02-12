<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBadge
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ServicesBadge
 */
class ilBadge
{
	protected $id; // [int]
	protected $parent_id; // [int]
	protected $type_id; // [string]
	protected $active; // [bool]
	protected $title; // [string]
	protected $desc; // [string]
	protected $config; // [array]
	
	/**
	 * Constructor
	 * 
	 * @param int $a_id
	 * @return self
	 */
	public function __construct($a_id = null)
	{
		if($a_id)
		{
			$this->read($a_id);
		}
	}
	
	public static function getInstancesByParentId($a_parent_id)
	{
		global $ilDB;
		
		$res = array();
		
		$set = $ilDB->query("SELECT * FROM badge_badge".
			" WHERE parent_id = ".$ilDB->quote($a_parent_id));
		while($row = $ilDB->fetchAssoc($set))
		{
			$obj = new self();
			$obj->importDBRow($row);
			$res[] = $obj;
		}
				
		return $res;
	}
	
	public function getTypeInstance()
	{
		if($this->getTypeId())
		{
			include_once "./Services/Badge/classes/class.ilBadgeHandler.php";
			$handler = ilBadgeHandler::getInstance();
			return $handler->getTypeInstanceByUniqueId($this->getTypeId());		
		}
	}
	
	
	//
	// setter/getter
	//
	
	protected function setId($a_id)
	{
		$this->id = (int)$a_id;
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function setParentId($a_id)
	{
		$this->parent_id = (int)$a_id;
	}
	
	public function getParentId()
	{
		return $this->parent_id;
	}
	
	public function setTypeId($a_id)
	{
		$this->type_id = trim($a_id);
	}
	
	public function getTypeId()
	{
		return $this->type_id;
	}
	
	public function setActive($a_value)
	{
		$this->active = (bool)$a_value;
	}
	
	public function isActive()
	{
		return $this->active;
	}
	
	public function setTitle($a_value)
	{
		$this->title = trim($a_value);
	}
	
	public function getTitle()
	{
		return $this->title;
	}
	
	public function setDescription($a_value)
	{
		$this->desc = trim($a_value);
	}
	
	public function getDescription()
	{
		return $this->desc;
	}
	
	public function setConfiguration(array $a_value = null)
	{
		if(is_array($a_value) &&
			!sizeof($a_value))
		{
			$a_value = null;
		}
		$this->config = $a_value;
	}
	
	public function getConfiguration()
	{
		return $this->config;
	}
	
	
	//
	// crud
	//
	
	protected function read($a_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM badge_badge".
			" WHERE id = ".$ilDB->quote($a_id, "integer"));
		if($ilDB->numRows($set))
		{
			$row = $ilDB->fetchAssoc($set);
			$this->importDBRow($row);			
		}		
	}
	
	protected function importDBRow(array $a_row)
	{
		$this->setId($a_row["id"]);
		$this->setParentId($a_row["parent_id"]);
		$this->setTypeId($a_row["type_id"]);
		$this->setActive($a_row["active"]);
		$this->setTitle($a_row["title"]);
		$this->setDescription($a_row["descr"]);
		$this->setConfiguration($a_row["conf"]
				? unserialize($a_row["conf"])
				: null);				
	}
	
	public function create()
	{
		global $ilDB;
		
		if($this->getId())
		{
			return $this->update();
		}
		
		$id = $ilDB->nextId("badge_badge");
		$this->setId($id);
		
		$fields = $this->getPropertiesForStorage();
			
		$fields["id"] = array("integer", $id);						
		$fields["parent_id"] = array("integer", $this->getParentId());
		$fields["type_id"] = array("text", $this->getTypeId());
		
		$ilDB->insert("badge_badge", $fields);
	}
	
	public function update()
	{
		global $ilDB;
		
		if(!$this->getId())
		{
			return $this->create();
		}
		
		$fields = $this->getPropertiesForStorage();
		
		$ilDB->update("badge_badge", $fields,
			array("id"=>array("integer", $this->getId()))
		);
	}
	
	public function delete()
	{
		global $ilDB;
		
		if(!$this->getId())
		{
			return;
		}
		
		$ilDB->manipulate("DELETE FROM badge_badge".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer"));
	}
	
	protected function getPropertiesForStorage()
	{
		return array(
			"active" => array("integer", $this->isActive()),
			"title" => array("text", $this->getTitle()),
			"descr" => array("text", $this->getDescription()),
			"conf" => array("text", $this->getConfiguration()
				? serialize($this->getConfiguration())
				: null)
		);		
	}
}

