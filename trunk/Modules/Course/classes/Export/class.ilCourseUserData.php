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
* @ingroup ModulesCourse
*/
class ilCourseUserData
{
	private $db;
	private $user_id;
	private $field_id;
	private $value;
	
	
	/**
	 * Contructor
	 *
	 * @access public
	 * @param int user id
	 * @param int field id
	 * 
	 */
	public function __construct($a_user_id,$a_field_id = 0)
	{
	 	global $ilDB;
	 	
	 	$this->db = $ilDB;
	 	$this->user_id = $a_user_id;
	 	$this->field_id = $a_field_id;
	 	
	 	if($this->field_id)
	 	{
	 		$this->read();
	 	}
	}
	
	/**
	 * Get values by obj_id (for all users)
	 *
	 * @access public
	 * @static
	 *
	 * @param int obj_id
	 */
	public static function _getValuesByObjId($a_obj_id)
	{
		global $ilDB;
		
		include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
		$field_ids = ilCourseDefinedFieldDefinition::_getFieldIds($a_obj_id);
		if(!count($field_ids))
		{
			return array();
		}
				
		$where = "WHERE ".$ilDB->in('field_id',$field_ids,false,'integer');
		$query = "SELECT * FROM crs_user_data ".
			$where;
		
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$user_data[$row->usr_id][$row->field_id] = $row->value;
		}
		
		return $user_data ? $user_data : array();
	}
	
	/**
	 * Check required fields 
	 *
	 * @access public
	 * @static
	 *
	 * @param int user id
	 * @param int object id
	 * 
	 * @return bool all fields filled
	 */
	public static function _checkRequired($a_usr_id,$a_obj_id)
	{
		global $ilDB;

		include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
		$required = ilCourseDefinedFieldDefinition::_getRequiredFieldIds($a_obj_id);
		if(!count($required))
		{
			return true;
		}
		
		//$and = ("AND field_id IN (".implode(",",ilUtil::quoteArray($required)).")");
		$and = "AND ".$ilDB->in('field_id',$required,false,'integer');
		
		$query = "SELECT COUNT(*) num_entries FROM crs_user_data ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ".
			"AND value != '' AND value IS NOT NULL ".
			$and." ".
			" ";
		$res = $ilDB->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		
		return $row->num_entries == count($required);
	}
	
	/**
	 * Delete all entries of an user
	 *
	 * @access public
	 * @static
	 * @param int user_id
	 * 
	 */
	public static function _deleteByUser($a_user_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM crs_user_data ".
			"WHERE usr_id = ".$ilDB->quote($a_user_id ,'integer');
		$res = $ilDB->manipulate($query);
				 	
	}
	
	/**
	 * Delete by field
	 *
	 * @access public
	 * @param
	 * 
	 */
	public static function _deleteByField($a_field_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM crs_user_data ".
			"WHERE field_id = ".$ilDB->quote($a_field_id ,'integer');
		$res = $ilDB->manipulate($query);
	}
	
	public function setValue($a_value)
	{
	 	$this->value = $a_value;
	}
	public function getValue()
	{
		return $this->value;
	}
	
	/**
	 * update value
	 *
	 * @access public
	 * 
	 */
	public function update()
	{
		$this->delete();
		$this->create();	
	}
	
	/**
	 * insert entry
	 *
	 * @access public
	 * 
	 */
	public function delete()
	{
	 	global $ilDB;
	 	
	 	$query = "DELETE FROM crs_user_data ".
	 		"WHERE usr_id = ".$this->db->quote($this->user_id ,'integer')." ".
	 		"AND field_id = ".$this->db->quote($this->field_id ,'integer');
	 	$res = $ilDB->manipulate($query);
	}
	
	/**
	 * Add entry
	 *
	 * @access public
	 * 
	 */
	public function create()
	{
	 	global $ilDB;
	 	
	 	$query = "INSERT INTO crs_user_data (value,usr_id,field_id) ".
	 		"VALUES( ".
	 		$this->db->quote($this->getValue() ,'text').", ".
	 		$this->db->quote($this->user_id ,'integer').", ".
	 		$this->db->quote($this->field_id ,'integer')." ".
	 		")";
			
	 	$res = $ilDB->manipulate($query);
	}

	/**
	 * Read value
	 *
	 * @access private
	 */
	private function read()
	{
	 	global $ilDB;
	 	
	 	$query = "SELECT * FROM crs_user_data ".
	 		"WHERE usr_id = ".$this->db->quote($this->user_id ,'integer')." ".
	 		"AND field_id = ".$this->db->quote($this->field_id ,'integer');
	 	$res = $this->db->query($query);
	 	$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

		$this->setValue($row->value);			
			
	}
}


?>