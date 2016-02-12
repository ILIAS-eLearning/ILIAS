<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBadgeHandler
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ServicesBadge
 */
class ilBadgeHandler
{
	protected $settings; // [ilSetting]
	
	protected static $instance; // [ilBadgeHandler]
	
	/**
	 * Constructor
	 * 
	 * @return self
	 */
	protected function __construct()
	{
		$this->settings = new ilSetting("bdga");
	}
	
	/**
	 * Constructor
	 * 
	 * @return self
	 */
	public static function getInstance()
	{
		if(!self::$instance)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	
	//
	// setter/getter
	//	
	
	public function isActive()
	{
		return $this->settings->get("active", false);		
	}
	
	public function setActive($a_value)
	{
		$this->settings->set("active", (bool)$a_value);		
	}
	
	public function isObiActive()
	{
		return $this->settings->get("obi_active", false);		
	}
	
	public function setObiActive($a_value)
	{
		$this->settings->set("obi_active", (bool)$a_value);		
	}
	
	public function getObiOrganistation()
	{
		return $this->settings->get("obi_organisation", null);		
	}
	
	public function setObiOrganisation($a_value)
	{
		$this->settings->set("obi_organisation", trim($a_value));		
	}
	
	public function getObiContact()
	{
		return $this->settings->get("obi_contact", null);		
	}
	
	public function setObiContact($a_value)
	{
		$this->settings->set("obi_contact", trim($a_value));		
	}
	
	public function getObiSalt()
	{
		return $this->settings->get("obi_salt", null);		
	}
	
	public function setObiSalt($a_value)
	{
		$this->settings->set("obi_salt", trim($a_value));		
	}
	
	public function getComponents()
	{
		$components = $this->settings->get("components", null);
		if($components)
		{
			return unserialize($components);
		}
		return array();
	}
	
	public function setComponents(array $a_components = null)
	{
		if(is_array($a_components) &&
			!sizeof($a_components))
		{
			$a_components = null;
		}
		$this->settings->set("components", $a_components !== null
			? serialize(array_unique($a_components))
			: null);		
	}
			
	
	//
	// component handling
	//	
	
	protected function getComponent($a_id)
	{
		global $ilDB;
		
		// see ilCtrl
		$set = $ilDB->query("SELECT * FROM il_component".
			" WHERE id = ".$ilDB->quote($a_id, "text"));
		$rec = $ilDB->fetchAssoc($set);
		if($rec["type"])
		{
			return $rec;
		}
	}
	
	/**
	 * Get provider instance
	 * 
	 * @param string $a_component_id
	 * @return ilBadgeProvider
	 */
	public function getProviderInstance($a_component_id)
	{
		$comp = $this->getComponent($a_component_id);		
		if($comp)
		{
			$class = "il".$comp["name"]."BadgeProvider";
			$file = $comp["type"]."/".$comp["name"]."/classes/class.".$class.".php";
			if(file_exists($file))
			{
				include_once $file;		
				$obj = new $class;
				if($obj instanceof ilBadgeProvider)
				{
					return $obj;
				}
			}
		}				
	}
	
	public function getComponentCaption($a_component_id)
	{
		$comp = $this->getComponent($a_component_id);
		if($comp)
		{
			return $comp["type"]."/".$comp["name"];
		}
	}			
	
	//
	// types
	// 
	
	public function getUniqueTypeId($a_component_id, ilBadgeType $a_badge)
	{
		return $a_component_id."/".$a_badge->getId();
	}
	
	/**
	 * Get type instance by unique id (component, type)
	 * @param string $a_id
	 * @return ilBadgeType
	 */
	public function getTypeInstanceByUniqueId($a_id)
	{
		$parts = explode("/", $a_id);
		$comp_id = $parts[0];
		$type_id = $parts[1];	
		$provider = $this->getProviderInstance($comp_id);	
		if($provider)
		{						
			foreach($provider->getBadgeTypes() as $type)
			{
				if($type->getId() == $type_id)
				{
					return $type;
				}
			}			
		}
	}
	
	public function getInactiveTypes()
	{
		$types = $this->settings->get("inactive_types", null);
		if($types)
		{
			return unserialize($types);
		}
		return array();
	}
	
	public function setInactiveTypes(array $a_types = null)
	{
		if(is_array($a_types) &&
			!sizeof($a_types))
		{
			$a_types = null;
		}
		$this->settings->set("inactive_types", $a_types !== null
			? serialize(array_unique($a_types))
			: null);
	}
	
	public function getAvailableTypesForObjType($a_object_type)
	{
		$res = array();
		
		$inactive = $this->getInactiveTypes();
		foreach($this->getComponents() as $component_id)
		{
			$provider = $this->getProviderInstance($component_id);
			if($provider)
			{
				foreach($provider->getBadgeTypes() as $type)
				{
					$id = $this->getUniqueTypeId($component_id, $type);
					if(!in_array($id, $inactive) &&
						in_array($a_object_type, $type->getValidObjectTypes()))
					{
						$res[$id] = $type;
					}
				}
			}
		}				
		
		return $res;
	}
	
	
	//
	// service/module definition
	// 		
	
	/**
	 * Import component definition
	 * 
	 * @param string $a_component_id
	 */
	public static function updateFromXML($a_component_id)
	{
		$handler = self::getInstance();
		$components = $handler->getComponents();
		$components[] = $a_component_id;	
		$handler->setComponents($components);
	}
	
	/**
	 * Remove component definition
	 * 
	 * @param string $a_component_id
	 */
	public static function clearFromXML($a_component_id)
	{	
		$handler = self::getInstance();
		$components = $handler->getComponents();
		foreach($components as $idx => $component)
		{
			if($component == $a_component_id)
			{
				unset($components[$idx]);
			}
		}
		$handler->setComponents($components);
	}
	
}

