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
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesWebServicesECS
*/
class ilECSParticipantSettings
{
	private static $instances = null;

	private $export = array();
	private $import = array();
	private $export_type = array();
	
	/**
	 * Constructor (Singleton)
	 *
	 * @access private
	 * 
	 */
	private function __construct($a_server_id)
	{
	 	$this->server_id = $a_server_id;
		$this->read();
	}
	
	/**
	 * get instance
	 *
	 * @access public
	 * @static
	 *
	 */
	public static function _getInstance()
	{
		$GLOBALS['ilLog']->write(__METHOD__.': Using deprecated call');
		$GLOBALS['ilLog']->logStack();
		return self::getInstanceByServerId(15);
	}

	/**
	 * Get instance by server id
	 * @param int $a_server_id
	 * @return ilECSParticipantSettings
	 */
	public static function getInstanceByServerId($a_server_id)
	{
		if(isset(self::$instances[$a_server_id]))
		{
			return self::$instances[$a_server_id];
		}
		return self::$instances[$a_server_id] = new ilECSParticipantSettings($a_server_id);
	}
	
	/**
	 * Get all available mids
	 * @global  $ilDB
	 * @param type $a_server_id
	 * @return type
	 */
	public static function getAvailabeMids($a_server_id)
	{
		global $ilDB;
		
		$query = 'SELECT mid FROM ecs_part_settings '.
				'WHERE sid = '.$ilDB->quote($a_server_id,'integer');
		$res = $ilDB->query($query);
		
		$mids = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$mids[] = $row->mid;
		}
		return $mids;
	}
	

	/**
	 * Get participants which are enabled and export is allowed
	 */
	public static function getExportableParticipants($a_type)
	{
		global $ilDB;

		$query = 'SELECT sid,mid,export_types FROM ecs_part_settings ep '.
			'JOIN ecs_server es ON ep.sid = es.server_id '.
			'WHERE export = '.$ilDB->quote(1,'integer').' '.
			'AND active = '.$ilDB->quote(1,'integer').' '.
			'ORDER BY cname,es.title';
		
		$res = $ilDB->query($query);
		$mids = array();
		$counter = 0;
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if(in_array($a_type, (array) unserialize($row->export_types)))
			{
				$mids[$counter]['sid'] = $row->sid;
				$mids[$counter]['mid'] = $row->mid;
				$counter++;
			}
		}
		return $mids;
	}

	/**
	 * Get server ids which allow an export
	 * @global <type> $ilDB
	 * @return <type>
	 */
	public static function getExportServers()
	{
		global $ilDB;

		$query = 'SELECT DISTINCT(sid) FROM ecs_part_settings  ep '.
			'JOIN ecs_server es ON ep.sid = es.server_id '.
			'WHERE export = '.$ilDB->quote(1,'integer').' '.
			'AND active = '.$ilDB->quote(1,'integer').' ';
		$res = $ilDB->query($query);
		$sids = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$sids[] = $row->sid;
		}
		return $sids;
	}

	/**
	 * Delete by server
	 * @global  $ilDB
	 * @param int $a_server_id 
	 */
	public static function deleteByServer($a_server_id)
	{
		global $ilDB;

		$query = 'DELETE from ecs_part_settings '.
			'WHERE sid = '.$ilDB->quote($a_server_id,'integer');
		$ilDB->manipulate($query);
	}
	
	/**
	 * Lookup mid of current cms participant
	 * @global  $ilDB
	 * @param int $a_server_id
	 */
	public static function loookupCmsMid($a_server_id)
	{
		global $ilDB;
		
		include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSetting.php';
		
		$query = 'SELECT mid FROM ecs_part_settings '.
				'WHERE sid = '.$ilDB->quote($a_server_id,'integer').' '.
				'AND import_type = '.$ilDB->quote(ilECSParticipantSetting::IMPORT_CMS);
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->mid;
		}
		return 0;
	}

	/**
	 * Get server id
	 * @return int
	 */
	public function getServerId()
	{
		return $this->server_id;
	}


	/**
	 * Read stored entry
	 * @return <type>
	 */
	public function read()
	{
		global $ilDB;

		$query = 'SELECT * FROM ecs_part_settings '.
			'WHERE sid = '.$ilDB->quote($this->getServerId(),'integer').' ';
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->export[$row->mid] = $row->export;
			$this->import[$row->mid] = $row->import;
			$this->import_type[$row->mid] = $row->import_type;
			$this->export_types[$row->mid] = (array) unserialize($row->export_types);
			$this->import_types[$row->mid] = (array) unserialize($row->import_types);
		}
		return true;
	}

	/**
	 * Check if import is allowed for scecific mid
	 * @param array $a_mids
	 * @return <type>
	 */
	public function isImportAllowed(array $a_mids)
	{
		foreach($a_mids as $mid)
		{
			if($this->import[$mid])
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * get number of participants that are enabled
	 *
	 * @access public
	 * @deprecated
	 */
	public function getEnabledParticipants()
	{
		$ret = array();
		foreach($this->export as $mid => $enabled)
		{
			if($enabled)
			{
				$ret[] = $mid;
			}
		}
		return $ret;
	 	#return $this->enabled ? $this->enabled : array();
	}
	
	/**
	 * is partivcipant enabled
	 *
	 * @access public
	 * @param int mid
	 * @deprecated
	 * 
	 */
	public function isEnabled($a_mid)
	{
	 	return $this->export[$a_mid] ? true : false;
	}
	
	/**
	 * set enabled participants by community
	 *
	 * @access public
	 * @param int community id
	 * @param array participant ids
	 */
	public function setEnabledParticipants($a_parts)
	{
	 	$this->enabled = (array) $a_parts;
	}
}
?>