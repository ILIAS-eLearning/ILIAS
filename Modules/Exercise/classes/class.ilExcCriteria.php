<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilExcCriteria
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesExercise
 */
abstract class ilExcCriteria
{
	protected $id; // [int]
	protected $parent; // [int]
	protected $title; // [string]
	protected $desc; // [string]
	protected $pos; // [int]
	
	protected function __construct()
	{	
		
	}
	
	public static function getInstanceById($a_id)			
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT *".
			" FROM exc_crit".
			" WHERE id = ".$ilDB->quote($a_id, "integer"));
		if($ilDB->numRows($set))
		{
			$row = $ilDB->fetchAssoc($set);
			$obj = self::getInstanceByType($row["type"]);
			$obj->importFromDB($row);
			return $obj;
		}
	}		
	
	public static function getInstancesByParentId($a_parent_id)
	{
		global $ilDB;
		
		$res = array();
		
		$set = $ilDB->query("SELECT *".
			" FROM exc_crit".
			" WHERE parent = ".$ilDB->quote($a_parent_id, "integer").
			" ORDER BY pos");
		while($row = $ilDB->fetchAssoc($set))
		{
			$obj = self::getInstanceByType($row["type"]);
			$obj->importFromDB($row);
			$res[$obj->getId()] = $obj;
		}	
		
		return $res;
	}
	
	
	//
	// type(s)
	// 
	
	public static function getTypesMap()
	{
		global $lng;
		
		return array(
			"bool" => $lng->txt("exc_criteria_type_bool")
			,"rating" => $lng->txt("exc_criteria_type_rating")
			,"text" => $lng->txt("exc_criteria_type_text")
			,"file" => $lng->txt("exc_criteria_type_file")			
		);
	}
	
	public function getTranslatedType()
	{
		$map = $this->getTypesMap();
		return $map[$this->getType()];
	}
	
	public static function getInstanceByType($a_type)			
	{
		$class = "ilExcCriteria".ucfirst($a_type);
		include_once "Modules/Exercise/classes/class.".$class.".php";
		return new $class;
	}
	
	
	//
	// properties
	// 
	
	public function getId()
	{
		return $this->id;
	}
	
	protected function setId($a_id)
	{
		$this->id = (int)$a_id;
	}
	
	abstract protected function getType();
	
	public function setParent($a_value)
	{
		$this->parent = ($a_value !== null)
			? (int)$a_value
			: null;
	}
	
	public function getParent()
	{
		return $this->parent;
	}
	
	public function setTitle($a_value)
	{
		$this->title = ($a_value !== null)
			? trim($a_value)
			: null;
	}
	
	public function getTitle()
	{
		return $this->title;
	}
	
	public function setDescription($a_value)
	{
		$this->desc = ($a_value !== null)
			? trim($a_value)
			: null;
	}
	
	public function getDescription()
	{
		return $this->desc;
	}

	public function setPosition($a_value)
	{
		$this->pos = (int)$a_value;
	}
	
	public function getPosition()
	{
		return $this->pos;
	}
	
	
	//
	// CRUD
	//
	
	protected function importFromDB(array $a_row)
	{		
		$this->setId($a_row["id"]);
		$this->setParent($a_row["parent"]);
		$this->setTitle($a_row["title"]);
		$this->setDescription($a_row["descr"]);
		$this->setPosition($a_row["pos"]);
	}
	
	protected function getDBProperties()
	{
		return array(
			"type" => array("text", $this->getType())
			,"title" => array("text", $this->getTitle())
			,"descr" => array("text", $this->getDescription())
			,"pos" => array("integer", $this->getPosition())
		);		
	}
	protected function getLastPosition()
	{
		global $ilDB;
		
		if(!$this->getParent())
		{
			return;
		}
		
		$set = $ilDB->query("SELECT MAX(pos) pos".
			" FROM exc_crit".
			" WHERE parent = ".$ilDB->quote($this->getParent(), "integer"));
		$row = $ilDB->fetchAssoc($set);		
		return (int)$row["pos"];
	}
	
	public function save()
	{
		global $ilDB;
		
		if($this->id)
		{
			return $this->update();
		}
		
		$this->id = $ilDB->nextId("exc_crit");
		
		$fields = $this->getDBProperties();		
		
		$fields["id"] = array("integer", $this->id);
		$fields["type"] = array("text", $this->getType());
		$fields["parent"] = array("integer", $this->getParent());
		$fields["pos"] = array("integer", $this->getLastPosition()+10);
		
		$ilDB->insert("exc_crit", $fields);
	}
	
	public function update()
	{
		global $ilDB;
		
		if(!$this->id)
		{
			return $this->save();
		}
		
		$primary = array("id"=>array("integer", $this->id));		
		$ilDB->update("exc_crit", $this->getDBProperties(), $primary);
	}
	
	public function delete()
	{
		global $ilDB;
		
		if(!$this->id)
		{
			return;
		}
				
		$ilDB->manipulate("DELETE FROM exc_crit".
			" WHERE id = ".$ilDB->quote($this->id, "integer"));
	}
	
	public function deleteByParent()
	{
		global $ilDB;
		
		if(!$this->getParent())
		{
			return;
		}
		
		$ilDB->manipulate("DELETE FROM exc_crit".
			" WHERE parent = ".$ilDB->quote($this->getParent(), "integer"));	
	}
	
	
}

