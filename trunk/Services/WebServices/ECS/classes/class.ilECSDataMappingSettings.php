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

include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSetting.php';
include_once './Services/WebServices/ECS/classes/class.ilECSDataMappingSetting.php';

/** 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesWebServicesECS
*/
class ilECSDataMappingSettings
{
	private static $instances = null;

	private $settings = null;
 	private $mappings = array();
 	
 	/**
	 * Singleton Constructor
	 *
	 * @access private
	 * 
	 */
	private function __construct($a_server_id)
	{
		$this->settings = ilECSSetting::getInstanceByServerId($a_server_id);
		$this->read();
	}
	
	/**
	 * Get Singleton instance
	 *
	 * @access public
	 * @static
	 * @deprecated
	 */
	public static function _getInstance()
	{
		$GLOBALS['ilLog']->write(__METHOD__.': Using deprecate call');
		$GLOBALS['ilLog']->logStack();

		return self::getInstanceByServerId(1);
	}

	/**
	 * Get singleton instance
	 * @param int $a_server_id
	 * @return ilECSDataMappingSettings
	 */
	public static function getInstanceByServerId($a_server_id)
	{
		if(isset(self::$instances[$a_server_id]))
		{
			return self::$instances[$a_server_id];
		}
		return self::$instances[$a_server_id] = new ilECSDataMappingSettings($a_server_id);
	}

	/**
	 * Delete server
	 * @global ilDB $ilDB
	 * @param int $a_server_id 
	 */
	public static function delete($a_server_id)
	{
		global $ilDB;

		$query = 'DELETE from ecs_data_mapping '.
			'WHERE sid = '.$ilDB->quote($a_server_id,'integer');
		$ilDB->manipulate($query);
	}

	/**
	 * Get actice ecs setting
	 * @return ilECSSetting
	 */
	public function getServer()
	{
		return $this->settings;
	}


	/**
	 * get mappings
	 *
	 * @access public
	 * 
	 */
	public function getMappings($a_mapping_type = 0)
	{
	 	if(!$a_mapping_type)
		{
			$a_mapping_type = ilECSDataMappingSetting::MAPPING_IMPORT_RCRS;
		}
		return $this->mappings[$a_mapping_type];
	}
	
	
	/**
	 * get mapping by key
	 *
	 * @access public
	 * @param int mapping type import, export, crs, rcrs
	 * @param string ECS data field name. E.g. 'lecturer'
	 * @return int AdvancedMetaData field id or 0 (no mapping)
	 * 
	 */
	public function getMappingByECSName($a_mapping_type,$a_key)
	{
	 	if(!$a_mapping_type)
		{
			$a_mapping_type = ilECSDataMappingSetting::MAPPING_IMPORT_RCRS;
		}

		return array_key_exists($a_key, (array) $this->mappings[$a_mapping_type]) ?
			$this->mappings[$a_mapping_type][$a_key] :
			0;
	}

	

	/**
	 * Read settings
	 *
	 * @access private
	 * 
	 */
	private function read()
	{
		global $ilDB;

		$this->mappings = array();

		$query = 'SELECT * FROM ecs_data_mapping '.
			'WHERE sid = '.$ilDB->quote($this->getServer()->getServerId(),'integer').' ';
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->mappings[$row->mapping_type][$row->ecs_field] = $row->advmd_id;
		}
	}
}
?>