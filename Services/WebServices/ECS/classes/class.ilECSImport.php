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
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesWebServicesECS 
*/
class ilECSImport
{
	protected $db = null;

	protected $server_id = 0;
	protected $obj_id = 0;
	protected $econtent_id = 0;
	protected $sub_id = 0;
	protected $mid = 0;
	protected $imported = false; 
	protected $ecs_id = 0;

	/**
	 * Constructor 
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct($a_server_id,$a_obj_id)
	{
	 	global $ilDB;

		$this->server_id = $a_server_id;
		$this->obj_id = $a_obj_id;
	 	$this->db = $ilDB;
	 	$this->read();
	}
	
	public static function lookupECSId($a_server_id, $a_mid, $a_econtent_id)
	{
		global $ilDB;
		
		$query = 'SELECT * from ecs_import '.
				'WHERE server_id = '.$ilDB->quote($a_server_id,'integer').' '.
				'AND mid = '.$ilDB->quote($a_mid,'integer').' '.
				'AND econtent_id = '.$ilDB->quote($a_econtent_id,'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->ecs_id;
		}
		return 0;
	}
	
	/**
	 * get all imported links
	 *
	 * @access public
	 * @static
	 *
	 */
	public static function _getAllImportedLinks($a_server_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM ecs_import ".
			'WHERE server_id = '.$ilDB->quote($a_server_id);
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$all[$row->econtent_id] = $row->obj_id;
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
	public static function _lookupObjIdsByMID($a_server_id,$a_mid)
	{
		global $ilDB;
		
		$query = "SELECT * FROM ecs_import ".
			"WHERE mid = ".$ilDB->quote($a_mid,'integer')." ".
			'AND server_id = '.$ilDB->quote($this->getServerId(),'integer');

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
		
		$query = "SELECT * FROM ecs_import WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->econtent_id;
		}
		return 0;
	}

	/**
	 * Lookup server id of imported content
	 * @global <type> $ilDB
	 * @param <type> $a_obj_id
	 * @return <type>
	 */
	public static function lookupServerId($a_obj_id)
	{
		global $ilDB;

		$query = 'SELECT * FROM ecs_import WHERE obj_id = '.$ilDB->quote($a_obj_id,'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->server_id;
		}
		return 0;
	}


	/**
	 * lookup obj_id
	 *
	 * @access public
	 * 
	 */
	public function _lookupObjIds($a_server_id,$a_econtent_id)
	{
	 	global $ilDB;
	 	
	 	$query = "SELECT obj_id FROM ecs_import WHERE econtent_id  = ".$ilDB->quote($a_econtent_id,'integer')." ".
			'AND server_id = '.$ilDB->quote($a_server_id,'integer');
	 	$res = $ilDB->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$obj_ids[] = $row->obj_id;
	 	}
	 	return $obj_ids ? $obj_ids : array();
	}
	
	/**
	 * loogup obj_id by econtent and mid and server_id
	 *
	 * @access public
	 * @param int econtent_id
	 * @param 
	 * 
	 */
	public static function _lookupObjId($a_server_id,$a_econtent_id,$a_mid, $a_sub_id = NULL)
	{
		global $ilDB;
		
		$query = "SELECT obj_id FROM ecs_import ".
			"WHERE econtent_id = ".$ilDB->quote($a_econtent_id,'integer')." ".
			"AND mid = ".$ilDB->quote($a_mid,'integer')." ".
			'AND server_id = '.$ilDB->quote($a_server_id,'integer').' ';
		
		if($a_sub_id)
		{
			$query .= 'AND sub_id = '.$ilDB->quote($a_sub_id,'integer');
		}
		else
		{
			$query .= 'AND sub_id IS NULL';
		}
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
	public function _lookupMID($a_server_id,$a_obj_id)
	{
	 	global $ilDB;
	 	
	 	$query = "SELECT * FROM ecs_emport WHERE obj_id = ".$ilDB->quote($a_obj_id)." ".
			'AND server_id = '.$ilDB->quote($a_server_id,'integer');
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
	public static function _lookupMIDs($a_server_id,$a_econtent_id)
	{
		global $ilDB;
		
		$query = "SELECT mid FROM ecs_import WHERE econtent_id = ".$ilDB->quote($a_econtent_id,'integer')." ".
			'AND server_id = '.$ilDB->quote($a_server_id,'integer');
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
			"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ";
		$res = $ilDB->manipulate($query);
		return true;
	}

	/**
	 * Delete by server id
	 * @global ilDB $ilDB
	 * @param int $a_server_id
	 */
	public static function deleteByServer($a_server_id)
	{
		global $ilDB;

		$query = 'DELETE FROM ecs_import '.
			'WHERE server_id = '.$ilDB->quote($a_server_id,'integer');
		$ilDB->manipulate($query);
	}
	
	/**
	 * Delete ressources
	 * @global  $ilDB
	 * @param type $a_server_id
	 * @param type $a_mid
	 * @param type $a_cms_ids
	 */
	public static function deleteRessources($a_server_id, $a_mid, $a_cms_ids)
	{
		global $ilDB;
		
		$query = 'DELETE FROM ecs_import '.
				'WHERE server_id = '.$ilDB->quote($a_server_id,'integer'). ' '.
				'AND mid = '.$ilDB->quote($a_mid,'integer').' '.
				'AND '.$ilDB->in('econtent_id',(array) $a_cms_ids,false,'integer');
		$ilDB->manipulate($query);
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
	public static function _isImported($a_server_id,$a_econtent_id,$a_mid, $a_sub_id = 0)
	{
		return ilECSImport::_lookupObjId($a_server_id,$a_econtent_id,$a_mid, $a_sub_id);
	}
	
	public function setServerId($a_server_id)
	{
		$this->server_id = $a_server_id;
	}

	public function getServerId()
	{
		return $this->server_id;
	}
	
	/**
	 * Set imported
	 *
	 * @access public
	 * @param bool export status
	 * 
	 */
	public function setImported($a_status)
	{
	 	$this->imported = $a_status;
	}
	
	public function setSubId($a_id)
	{
		$this->sub_id = $a_id;
	}
	
	public function getSubId()
	{
		return $this->sub_id ? $this->sub_id : NULL;
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
		global $ilDB;
		
		$query = "DELETE FROM ecs_import ".
			"WHERE obj_id = ".$this->db->quote($this->obj_id,'integer')." ".
			'AND server_id = '.$ilDB->quote($this->getServerId(),'integer');
		$res = $ilDB->manipulate($query);
		
		$query = "INSERT INTO ecs_import (obj_id,mid,econtent_id,sub_id,server_id,ecs_id) ".
			"VALUES ( ".
			$this->db->quote($this->obj_id,'integer').", ".
			$this->db->quote($this->mid,'integer').", ".
			$this->db->quote($this->econtent_id,'integer').", ".
			$this->db->quote($this->getSubId(),'text'). ', '.
			$this->db->quote($this->getServerId(),'integer').', '.
			$this->db->quote($this->getECSId(),'integer').' '.
			")";
		
		$res = $ilDB->manipulate($query);
		
		return true;
	}
	
	/**
	 * Read 
	 * @access private
	 */
	private function read()
	{
	 	global $ilDB;
	 	
	 	$query = "SELECT * FROM ecs_import WHERE ".
	 		"obj_id = ".$this->db->quote($this->obj_id,'integer')." ".
			'AND server_id = '.$ilDB->quote($this->getServerId(),'integer');
	 	$res = $this->db->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->econtent_id = $row->econtent_id;
			$this->mid = $row->mid;
			$this->sub_id = $row->sub_id;
			$this->ecs_id = $row->ecs_id;
	 	}
	}
	
	public static function resetServerId($a_server_id)
	{
		global $ilDB;

		$query = 'UPDATE ecs_import SET server_id = '.$ilDB->quote(0,'integer').
			' WHERE server_id = '.$ilDB->quote($a_server_id,'integer');
		$ilDB->manipulate($query);
		return true;
	}
	
	public function getECSId()
	{
		return $this->ecs_id;
	}
	
	public function setECSId($a_id)
	{
		$this->ecs_id = $a_id;
	}
}
?>