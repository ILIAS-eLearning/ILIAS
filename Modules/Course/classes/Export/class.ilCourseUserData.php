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
* @ingroup ModulesCourse
*/
class ilCourseUserData
{
	private $ilDB;
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
	public function __contruct($a_user_id,$a_field_id = 0)
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
			"WHERE usr_id = ".$ilDB->quote($a_user_id);
		$ilDB->query($query);
				 	
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
			"WHERE field_id = ".$ilDB->quote($a_field_id);
		$ilDB->query($query);
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
	 	$query = "DELETE FROM crs_user_data ".
	 		"WHERE usr_id = ".$this->db->quote($this->user_id)." ".
	 		"AND field_id = ".$this->db->quote($this->field_id);
	 	$this->db->query($query);
	}
	
	/**
	 * Add entry
	 *
	 * @access public
	 * 
	 */
	public function create()
	{
	 	$query = "INSERT INTO crs_user_data SET ".
	 		"value = ".$this->db->quote($this->getValue()).", ".
	 		"usr_id = ".$this->db->quote($this->user_id)." ".
	 		"field_id = ".$this->db->quote($this->field_id)." ";
	 	$this->db->query($query);
	}

	/**
	 * Read value
	 *
	 * @access private
	 */
	private function read()
	{
	 	$query = "SELECT * FROM crs_user_data ".
	 		"WHERE usr_id = ".$this->db->quote($this->user_id)." ".
	 		"AND field_id = ".$this->db->quote($this->field_id);
	 	$res = $this->db->query($query);
	 	$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

		$this->setValue($row->value);			
			
	}
}


?>