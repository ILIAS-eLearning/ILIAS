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
* Storage of ECS exported objects.
* This class stores the econent id and informations whether an object is exported or not. 
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
* 
* 
* @ingroup ServicesWebServicesECS 
*/
class ilECSExport
{
	protected $db = null;

	protected $obj_id = 0;
	protected $econtent_id = 0;
	protected $exported = false; 

	/**
	 * Constructor 
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct($a_obj_id)
	{
	 	global $ilDB;
	 	
	 	$this->obj_id = $a_obj_id;
	 	
	 	$this->db = $ilDB;
	 	$this->read();
	}
	
	/**
	 * get all exported econtent ids
	 *
	 * @access public
	 * @static
	 *
	 */
	public static function _getAllEContentIds()
	{
		global $ilDB;
		
		$query = "SELECT econtent_id FROM ecs_export ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$econtent_ids[$row->econtent_id] = $row->econtent_id; 
		}
		return $econtent_ids ? $econtent_ids : array();
	}
	
	/**
	 * get exported ids
	 *
	 * @access public
	 * @return
	 * @static
	 */
	public static function _getExportedIDs()
	{
		global $ilDB;
		$query = "SELECT obj_id FROM ecs_export ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$obj_ids[] = $row->obj_id;
		}
		return $obj_ids ? $obj_ids : array();
	}
	
	/**
	 * Delete econtent ids
	 *
	 * @access public
	 * @static
	 *
	 * @param array array of econtent ids
	 */
	public static function _deleteEContentIds($a_ids)
	{
		global $ilDB;
		
		if(!is_array($a_ids) or !count($a_ids))
		{
			return true;
		}
		#$query = "DELETE FROM ecs_export WHERE econtent_id IN (".implode(',',ilUtil::quoteArray($a_ids)).')';
		$query = "DELETE FROM ecs_export WHERE ".$ilDB->in('econtent_id',$a_ids,false,'integer');
		$res = $ilDB->manipulate($query);
		return true;
	}
	
	/**
	 * is remote object
	 *
	 * @access public
	 * @static
	 *
	 * @param int econtent_id
	 */
	public static function _isRemote($a_econtent_id)
	{
		global $ilDB;
		
		$query = "SELECT obj_id FROM ecs_export ".
			"WHERE econtent_id = ".$ilDB->quote($a_econtent_id,'integer')." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return false;
		}
		return true;
	}
	
	/**
	 * Set exported
	 *
	 * @access public
	 * @param bool export status
	 * 
	 */
	public function setExported($a_status)
	{
	 	$this->exported = $a_status;
	}
	
	/**
	 * check if an object is exported or not
	 *
	 * @access public
	 * 
	 */
	public function isExported()
	{
	 	return (bool) $this->exported;
	}

	/**
	 * set econtent id
	 *
	 * @access public
	 * @param int econtent id (received from ECS::addResource)
	 * 
	 */
	public function setEContentId($a_id)
	{
	 	$this->econtent_id = $a_id;
	}
	
	/**
	 * get econtent id
	 *
	 * @access public
	 * @return int econtent id 
	 * 
	 */
	public function getEContentId()
	{
	 	return $this->econtent_id;
	}
	
	/**
	 * Save
	 *
	 * @access public
	 */
	public function save()
	{
		global $ilDB;

		$query = "DELETE FROM ecs_export ".
			"WHERE obj_id = ".$this->db->quote($this->obj_id,'integer')." ";
		$res = $ilDB->manipulate($query);
	
		if($this->isExported())
		{
			$query = "INSERT INTO ecs_export (obj_id,econtent_id) ".
				"VALUES ( ".
				$this->db->quote($this->obj_id,'integer').", ".
				$this->db->quote($this->getEContentId(),'integer')." ".
				")";
			$res = $ilDB->manipulate($query);
		}
		
		return true;
	}
	
	/**
	 * Read 
	 * @access private
	 */
	private function read()
	{
	 	global $ilDB;
	 	
	 	$query = "SELECT * FROM ecs_export WHERE ".
	 		"obj_id = ".$this->db->quote($this->obj_id,'integer')." ";
	 	$res = $this->db->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->econtent_id = $row->econtent_id;
	 		$this->exported = true;
	 	}
	}
}
?>