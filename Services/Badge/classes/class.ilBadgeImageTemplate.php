<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Badge Template
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 * @ingroup ServicesBadge
 */
class ilBadgeImageTemplate
{
	protected $id; // [int]	
	protected $title; // [string]
	protected $image; // [string]
	protected $types; // [array]
	
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
	
	public static function getInstances()
	{
		global $ilDB;
		
		$res = array();
		
		$types = array();
		$set = $ilDB->query("SELECT * FROM badge_image_templ_type");
		while($row = $ilDB->fetchAssoc($set))
		{
			$types[$row["tmpl_id"]][] = $row["type_id"];
		}
		
		$set = $ilDB->query("SELECT * FROM badge_image_template".
			" ORDER BY title");
		while($row = $ilDB->fetchAssoc($set))
		{
			$row["types"] = (array)$types[$row["id"]];
			
			$obj = new self();
			$obj->importDBRow($row);
			$res[] = $obj;
		}
				
		return $res;
	}
	
	public static function getInstancesByType($a_type_unique_id)
	{
		$res = array();
		
		foreach(self::getInstances() as $tmpl)
		{			
			if(!sizeof($tmpl->getTypes()) ||
				in_array($a_type_unique_id, $tmpl->getTypes()))
			{
				$res[] = $tmpl;
			}			
		}
		
		return $res;
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
	
	public function setTitle($a_value)
	{
		$this->title = trim($a_value);
	}
	
	public function getTitle()
	{
		return $this->title;
	}
	
	protected function setImage($a_value)
	{
		$this->image = trim($a_value);
	}
	
	public function getTypes()
	{
		return (array)$this->types;
	}
	
	public function setTypes(array $types = null)
	{		
		$this->types = is_array($types) 
			? array_unique($types)
			: null;
	}
	
	public function getImage()
	{
		return $this->image;
	}
	
	public function uploadImage(array $a_upload_meta)
	{		
		if($this->getId() &&
			$a_upload_meta["tmp_name"])
		{
 			$path = $this->getFilePath($this->getId());
			$tgt = $path."img".$this->getId();
			if(move_uploaded_file($a_upload_meta["tmp_name"], $tgt))
			{
				$this->setImage($a_upload_meta["name"]);
				$this->update();			
			}
		}
	}
	
	public function getImagePath()
	{
		if($this->getId())
		{
			return $this->getFilePath($this->getId())."img".$this->getId();
		}
	}
	
	/**
	 * Init file system storage
	 * 
	 * @param type $a_id
	 * @param type $a_subdir
	 * @return string 
	 */
	protected function getFilePath($a_id, $a_subdir = null)
	{		
		include_once "Services/Badge/classes/class.ilFSStorageBadgeImageTemplate.php";
		$storage = new ilFSStorageBadgeImageTemplate($a_id);
		$storage->create();
		
		$path = $storage->getAbsolutePath()."/";
		
		if($a_subdir)
		{
			$path .= $a_subdir."/";
			
			if(!is_dir($path))
			{
				mkdir($path);
			}
		}
				
		return $path;
	}
	
	
	//
	// crud
	//
	
	protected function read($a_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM badge_image_template".
			" WHERE id = ".$ilDB->quote($a_id, "integer"));
		if($ilDB->numRows($set))
		{
			$row = $ilDB->fetchAssoc($set);
			$row["types"] = $this->readTypes($a_id); 
			$this->importDBRow($row);						
		}		
	}
	
	protected function readTypes($a_id)
	{
		global $ilDB;
		
		$res = array();
		
		$set = $ilDB->query("SELECT * FROM badge_image_templ_type".
			" WHERE tmpl_id = ".$ilDB->quote($a_id, "integer"));
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row["type_id"];
		}
		
		if(!sizeof($res))
		{
			$res = null;
		}
		
		return $res;
	}
	
	protected function importDBRow(array $a_row)
	{
		$this->setId($a_row["id"]);		
		$this->setTitle($a_row["title"]);
		$this->setImage($a_row["image"]);								
		$this->setTypes($a_row["types"]);								
	}
	
	public function create()
	{
		global $ilDB;
		
		if($this->getId())
		{
			return $this->update();
		}
		
		$id = $ilDB->nextId("badge_image_template");
		$this->setId($id);
		
		$fields = $this->getPropertiesForStorage();			
		$fields["id"] = array("integer", $id);						
		
		$ilDB->insert("badge_image_template", $fields);
		
		$this->saveTypes();
	}
	
	public function update()
	{
		global $ilDB;
		
		if(!$this->getId())
		{
			return $this->create();
		}
		
		$fields = $this->getPropertiesForStorage();
		
		$ilDB->update("badge_image_template", $fields,
			array("id"=>array("integer", $this->getId()))
		);
		
		$this->saveTypes();
	}
	
	public function delete()
	{
		global $ilDB;
		
		if(!$this->getId())
		{
			return;
		}
		
		$path = $this->getFilePath($this->getId());
		ilUtil::delDir($path);
		
		$ilDB->manipulate("DELETE FROM badge_image_template".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer"));
	}
	
	protected function getPropertiesForStorage()
	{
		return array(			
			"title" => array("text", $this->getTitle()),
			"image" => array("text", $this->getImage())
		);		
	}
	
	protected function saveTypes()
	{
		global $ilDB;
		
		if($this->getId())
		{
			$ilDB->manipulate("DELETE FROM badge_image_templ_type".
				" WHERE tmpl_id = ".$ilDB->quote($this->getId(), "integer"));
			
			if($this->getTypes())
			{
				foreach($this->getTypes() as $type)
				{
					$fields = array(
						"tmpl_id" => array("integer", $this->getId()),
						"type_id" => array("text", $type)
					);				
					$ilDB->insert("badge_image_templ_type", $fields);
				}	
			}
		}
	}
}
