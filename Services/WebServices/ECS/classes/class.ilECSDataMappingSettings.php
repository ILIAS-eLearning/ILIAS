<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/** 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesWebServicesECS
*/

class ilECSDataMappingSettings
{
	private static $instance = null;

 	private $mappings = array();
 	
 	/**
	 * Singleton Constructor
	 *
	 * @access private
	 * 
	 */
	private function __construct()
	{
	 	$this->initStorage();
	 	$this->read();
	}
	
	/**
	 * Get Singleton instance
	 *
	 * @access public
	 * @static
	 *
	 */
	public static function _getInstance()
	{
		if(self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new ilECSDataMappingSettings();
	}
	
	/**
	 * get mappings
	 *
	 * @access public
	 * 
	 */
	public function getMappings()
	{
	 	return $this->mappings ? $this->mappings : array();
	}
	
	/**
	 * set mappings
	 *
	 * @access public
	 * @param array e.g array('lecturer' => 0,'room' => 17). Which means 'lecturer' is ignored, 'room' is mapped against AdvancedFieldDefinition 17. 
	 * 
	 */
	public function setMappings($a_mappings)
	{
	 	if(!is_array($a_mappings))
	 	{
	 		return false;
	 	}
	 	$this->mappings = array();
	 	foreach($a_mappings as $key => $field_id)
	 	{
	 		$this->mappings[$key] = (int) $field_id;
	 	}
	 	return true;
	}
	
	/**
	 * get mapping by key
	 *
	 * @access public
	 * @param string ECS data field name. E.g. 'lecturer'
	 * @return int AdvancedMetaData field id or 0 (no mapping)
	 * 
	 */
	public function getMappingByECSName($a_key)
	{
	 	return isset($this->mappings[$a_key]) ? $this->mappings[$a_key] : 0;
	}

	/**
	 * Save mappings
	 *
	 * @access public
	 * 
	 */
	public function save()
	{
		$this->storage->set('mappings',addslashes(serialize($this->mappings)));
	}
	
	/**
	 * init data storage
	 *
	 * @access private
	 */
	private function initStorage()
	{
		include_once('./Services/Administration/classes/class.ilSetting.php');
	 	$this->storage = new ilSetting('ecs_mappings');
	}

	/**
	 * Read settings
	 *
	 * @access private
	 * 
	 */
	private function read()
	{
		$mappings = $this->storage->get('mappings');
		if($mappings)
		{
			$this->mappings = unserialize(stripslashes($mappings));
		}
	}

}


?>