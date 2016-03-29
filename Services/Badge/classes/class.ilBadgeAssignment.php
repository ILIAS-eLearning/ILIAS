<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBadgeAssignment
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ServicesBadge
 */
class ilBadgeAssignment
{
	protected $badge_id; // [int]
	protected $user_id; // [int]
	protected $tstamp; // [timestamp]
	protected $awarded_by; // [int]
	protected $pos; // [int]
	protected $stored; // [bool]
	
	public function __construct($a_badge_id = null, $a_user_id = null)
	{
		if($a_badge_id &&
			$a_user_id)
		{
			$this->setBadgeId($a_badge_id);
			$this->setUserId($a_user_id);
			
			$this->read($a_badge_id, $a_user_id);
		}		
	}
	
	public static function getInstancesByUserId($a_user_id)
	{
		global $ilDB;
		
		$res = array();
		
		$set = $ilDB->query("SELECT * FROM badge_user_badge".
			" WHERE user_id = ".$ilDB->quote($a_user_id, "integer"));
		while($row = $ilDB->fetchAssoc($set))
		{
			$obj = new self();
			$obj->importDBRow($row);
			$res[] = $obj;			
		}
		
		return $res;
	}
	
	public static function getInstancesByBadgeId($a_badge_id)
	{
		global $ilDB;
		
		$res = array();
		
		$set = $ilDB->query("SELECT * FROM badge_user_badge".
			" WHERE badge_id = ".$ilDB->quote($a_badge_id, "integer"));
		while($row = $ilDB->fetchAssoc($set))
		{
			$obj = new self();
			$obj->importDBRow($row);
			$res[] = $obj;			
		}
		
		return $res;
	}
	
	public static function getInstancesByParentId($a_parent_obj_id)
	{
		global $ilDB;
		
		$res = array();
		
		$badge_ids = array();
		foreach(ilBadge::getInstancesByParentId($a_parent_obj_id) as $badge)
		{
			$badge_ids[] = $badge->getId();
		}
		if(sizeof($badge_ids))
		{
			$set = $ilDB->query("SELECT * FROM badge_user_badge".
			" WHERE ".$ilDB->in("badge_id", $badge_ids, "", "integer"));
			while($row = $ilDB->fetchAssoc($set))
			{
				$obj = new self();
				$obj->importDBRow($row);
				$res[] = $obj;			
			}		
		}
		
		return $res;
	}
	
	public static function getAssignedUsers($a_badge_id)
	{
		$res = array();
		
		foreach(self::getInstancesByBadgeId($a_badge_id) as $ass)
		{
			$res[] = $ass->getUserId();
		}
		
		return $res;
	}
	
	public static function exists($a_badge_id, $a_user_id)
	{
		$obj = new self($a_badge_id, $a_user_id);
		return $obj->stored;
	}			
	
	
	//
	// setter/getter
	// 
	
	protected function setBadgeId($a_value)
	{
		$this->badge_id = (int)$a_value;
	}
	
	public function getBadgeId()
	{
		return $this->badge_id;
	}
	
	protected function setUserId($a_value)
	{
		$this->user_id = (int)$a_value;
	}
	
	public function getUserId()
	{
		return $this->user_id;
	}
	
	protected function setTimestamp($a_value)
	{
		$this->tstamp = (int)$a_value;
	}
	
	public function getTimestamp()
	{
		return $this->tstamp;
	}
	
	public function setAwardedBy($a_id)
	{
		$this->awarded_by = (int)$a_id;
	}
	
	public function getAwardedBy()
	{
		return $this->awarded_by;
	}
	
	public function setPosition($a_value)
	{
		if($a_value !== null)
		{
			$a_value = (int)$a_value;
		}
		$this->pos = $a_value;
	}
	
	public function getPosition()
	{
		return $this->pos;
	}
	
	
	//
	// crud
	// 
	
	protected function importDBRow(array $a_row)
	{
		$this->stored = true;
		$this->setBadgeId($a_row["badge_id"]);
		$this->setUserId($a_row["user_id"]);
		$this->setTimestamp($a_row["tstamp"]);
		$this->setAwardedBy($a_row["awarded_by"]);
		$this->setPosition($a_row["pos"]);		
	}
	
	protected function read($a_badge_id, $a_user_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM badge_user_badge".
			" WHERE badge_id = ".$ilDB->quote($a_badge_id, "integer").
			" AND user_id = ".$ilDB->quote($a_user_id, "integer"));
		$row = $ilDB->fetchAssoc($set);
		if($row["user_id"])
		{
			$this->importDBRow($row);				
		}
	}
	
	protected function getPropertiesForStorage()
	{
		return array(
			"tstamp" => array("integer", (bool)$this->stored ? $this->getTimestamp() : time()),
			"awarded_by" => array("integer", $this->getAwardedBy()),
			"pos" => array("integer", $this->getPosition())
		);		
	}
	
	public function store()
	{
		global $ilDB;
		
		if(!$this->getBadgeId() ||
			!$this->getUserId())
		{
			return;
		}
		
		$keys = array(
			"badge_id" => array("integer", $this->getBadgeId()),
			"user_id" => array("integer", $this->getUserId())
		);
		$fields = $this->getPropertiesForStorage();
		
		if(!(bool)$this->stored)
		{												
			$ilDB->insert("badge_user_badge", $fields + $keys);
		}
		else
		{
			$ilDB->update("badge_user_badge", $fields, $keys);
		}
	}
	
	public function delete()
	{
		global $ilDB;
		
		if(!$this->getBadgeId() ||
			!$this->getUserId())
		{
			return;
		}
		
		$ilDB->manipulate("DELETE FROM badge_user_badge".
			" WHERE badge_id = ".$ilDB->quote($this->getBadgeId(), "integer").
			" AND user_id = ".$ilDB->quote($this->getUserId(), "integer"));
	}
	
	public static function deleteByUserId($a_user_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM badge_user_badge".
			" WHERE user_id = ".$ilDB->quote($a_user_id, "integer"));
	}
	
	public static function deleteByBadgeId($a_badge_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM badge_user_badge".
			" WHERE badge_id = ".$ilDB->quote($a_badge_id, "integer"));
	}
	
	// :TODO: to be discussed
	public static function deleteByParentId($a_parent_obj_id)
	{
		global $ilDB;
	
		$badge_ids = array();
		foreach(ilBadge::getInstancesByParentId($a_parent_obj_id) as $badge)
		{
			$badge_ids[] = $badge->getId();
		}
		if(sizeof($badge_ids))
		{
			$ilDB->manipulate("DELETE FROM badge_user_badge".
			" WHERE ".$ilDB->in("badge_id", $badge_ids, "", "integer"));
		}
	}
	
	
	//
	// PUBLISHING
	// 
	
	protected function prepareJson($a_url)
	{						
		$verify = new stdClass();
		$verify->type = "hosted";
		$verify->url = $a_url;
		
		$recipient = new stdClass();
		$recipient->type = "email";
		$recipient->hashed = true;
		$recipient->salt = ilBadgeHandler::getInstance()->getObiSalt();
		
		// https://github.com/mozilla/openbadges-backpack/wiki/How-to-hash-&-salt-in-various-languages.
		$user = new ilObjUser($this->getUserId());
		$recipient->identity = 'sha256$'.hash('sha256', $user->getEmail().$recipient->salt);
		
		// spec: should be locally unique
		$unique_id = md5($this->getBadgeId()."-".$this->getUserId());		
		
		$json = new stdClass();
		$json->{"@context"} = "https://w3id.org/openbadges/v1";
		$json->type = "Assertion";
		$json->id = $a_url;
		$json->uid = $unique_id;
		$json->recipient = $recipient;
			
		include_once "Services/Badge/classes/class.ilBadge.php";
		$badge = new ilBadge($this->getBadgeId());
		$badge_url = $badge->getStaticUrl();
		
		// :TODO: created baked image
		 
		$json->image = str_replace(
			"class.json",
			"image.".array_pop(explode(".", $badge->getImage())),
			$badge_url
		);
		
		$json->issuedOn = $this->getTimestamp();
		$json->badge =  $badge_url;		
		$json->verify = $verify;	
		
		return $json;
	}
	
	public function getStaticUrl()
	{							
		$path = ilBadgeHandler::getInstance()->getInstancePath($this);
		
		$url = ILIAS_HTTP_PATH.substr($path, 1);
		
		if(!file_exists($path))
		{			
			$json = json_encode($this->prepareJson($url));
			file_put_contents($path, $json);
		}
		
		return $url;
	}
}