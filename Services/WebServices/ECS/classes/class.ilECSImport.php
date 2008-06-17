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
* Storage of ECS imported objects.
* This class stores the econent id and informations whether an object is imported or not. 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesWebServicesECS 
*/
class ilECSImport
{
	protected $db = null;

	protected $obj_id = 0;
	protected $econtent_id = 0;
	protected $mid = 0;
	protected $imported = false; 

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
	 * get all imported links
	 *
	 * @access public
	 * @static
	 *
	 */
	public static function _getAllImportedLinks()
	{
		global $ilDB;
		
		$query = "SELECT * FROM ecs_import ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$all[$row->econtent_id] = $row->obj_id;
		}
		return $all ? $all : array();
	}
	
	/**
	 * get all
	 *
	 * @access public
	 * @static
	 *
	 */
	public static function _getAll()
	{
		global $ilDB;
		
		$query = "SELECT * FROM ecs_import ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$all[$row->obj_id]['mid'] = $row->mid;
			$all[$row->obj_id]['econtent_id'] = $row->econtent_id;
			$all[$row->obj_id]['obj_id'] = $row->obj_id;
		}
		return $all ? $all : array();
	}
	
	/**
	 * lookup obj ids by mid 
	 *
	 * @access public
	 * @param int mid
	 * @return array obj ids
	 * @static
	 */
	public static function _lookupObjIdsByMID($a_mid)
	{
		global $ilDB;
		
		$query = "SELECT * FROM ecs_import ".
			"WHERE mid = ".$ilDB->quote($a_mid)." ";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$obj_ids[] = $row->obj_id;
		}
		return $obj_ids ? $obj_ids : array();
	}
	
	/**
	 * get econent_id
	 *
	 * @access public
	 * @static
	 *
	 * @param int obj_id
	 */
	public static function _lookupEContentId($a_obj_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM ecs_import WHERE obj_id = ".$ilDB->quote($a_obj_id)." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->econtent_id;
		}
		return 0;
	}
	
	/**
	 * lookup obj_id
	 *
	 * @access public
	 * 
	 */
	public function _lookupObjIds($a_econtent_id)
	{
	 	global $ilDB;
	 	
	 	$query = "SELECT obj_id FROM ecs_import WHERE econtent_id  = ".$ilDB->quote($a_econtent_id)." ";
	 	$res = $ilDB->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$obj_ids[] = $row->obj_id;
	 	}
	 	return $obj_ids ? $obj_ids : array();
	}
	
	/**
	 * loogup obj_id by econtent and mid
	 *
	 * @access public
	 * @param int econtent_id
	 * @param 
	 * 
	 */
	public function _lookupObjId($a_econtent_id,$a_mid)
	{
		global $ilDB;
		
		$query = "SELECT obj_id FROM ecs_import ".
			"WHERE econtent_id = ".$ilDB->quote($a_econtent_id)." ".
			"AND mid = ".$ilDB->quote($a_mid)." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->obj_id;
		}	
		return 0;
	}
	
	/**
	 * Lookup mid
	 *
	 * @access public
	 * 
	 */
	public function _lookupMID($a_obj_id)
	{
	 	global $ilDB;
	 	
	 	$query = "SELECT * FROM ecs_emport WHERE obj_id = ".$ilDB->quote($a_obj_id)." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->mid;
		}
		return 0;
	 	
	}
	
	/**
	 * Lookup mids by  
	 *
	 * @access public
	 * @static
	 *
	 * @param int econtent_id
	 */
	public static function _lookupMIDs($a_econtent_id)
	{
		global $ilDB;
		
		$query = "SELECT mid FROM ecs_import WHERE econtent_id = ".$ilDB->quote($a_econtent_id)." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$mids[] = $row->mid;
		}
		return $mids ? $mids : array();
	}
	
	/**
	 * Delete by obj_id
	 *
	 * @access public
	 * @static
	 *
	 * @param int obj_id
	 */
	public static function _deleteByObjId($a_obj_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM ecs_import ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id)." ";
		$ilDB->query($query);
		return true;
	}
	
	
	/**
	 * check if econtent is imported for a specific mid
	 *
	 * @access public
	 * @static
	 *
	 * @param int econtent id
	 * @param int mid
	 */
	public static function _isImported($a_econtent_id,$a_mid)
	{
		return ilECSImport::_lookupObjId($a_econtent_id,$a_mid);
	}
	
	/**
	 * Set exported
	 *
	 * @access public
	 * @param bool export status
	 * 
	 */
	public function setImported($a_status)
	{
	 	$this->imported = $a_status;
	}
	
	/**
	 * set mid
	 *
	 * @access public
	 * @param int mid
	 * 
	 */
	public function setMID($a_mid)
	{
	 	$this->mid = $a_mid;
	}
	
	/**
	 * get mid
	 *
	 * @access public
	 * 
	 */
	public function getMID()
	{
	 	return $this->mid;
	}
	
	/**
	 * set econtent id
	 *
	 * @access public
	 * @param int econtent id
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
		$query = "DELETE FROM ecs_import ".
			"WHERE obj_id = ".$this->db->quote($this->obj_id)." ";
		$this->db->query($query);
		
		$query = "INSERT INTO ecs_import ".
			"SET obj_id = ".$this->db->quote($this->obj_id).", ".
			"mid = ".$this->db->quote($this->mid).", ".
			"econtent_id = ".$this->db->quote($this->econtent_id)." ";
		$this->db->query($query);			
		
		return true;
	}
	
	/**
	 * Read 
	 * @access private
	 */
	private function read()
	{
	 	$query = "SELECT * FROM ecs_import WHERE ".
	 		"obj_id = ".$this->db->quote($this->obj_id)." ";
	 	$res = $this->db->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->econtent_id = $row->econtent_id;
			$this->mid = $row->mid;
	 	}
	}
}


?>