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
include_once('Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');


/** 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ModulesCourse
*/
class ilMemberAgreement
{
	private $db;
	private $user_id;
	private $obj_id;
	private $type;
	
	private $privacy;
	
	private $accepted = false;
	private $acceptance_time = 0;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param int usr_id
	 * @param int obj_id
	 */
	public function __construct($a_usr_id,$a_obj_id)
	{
	 	global $ilDB;
	 	
	 	$this->db = $ilDB;
	 	$this->user_id = $a_usr_id;
	 	$this->obj_id = $a_obj_id;
		$this->type = ilObject::_lookupType($this->obj_id);
	 	
	 	$this->privacy = ilPrivacySettings::_getInstance();
	 	
	 	if($this->privacy->confirmationRequired($this->type) or ilCourseDefinedFieldDefinition::_hasFields($this->obj_id))
	 	{
		 	$this->read();
	 	}
	}
	
	/**
	 * Read user data by object id
	 *
	 * @access public
	 * @static
	 *
	 * @param int obj_id
	 */
	public static function _readByObjId($a_obj_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM member_agreement ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer');
			
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$user_data[$row->usr_id]['accepted'] = $row->accepted;
			$user_data[$row->usr_id]['acceptance_time'] = $row->acceptance_time;
		}
		return $user_data ? $user_data : array();					
	}
	
	/**
	 * Check if there is any user agreement
	 *
	 * @access public
	 * @static
	 *
	 * @param int obj_id
	 */
	public static function _hasAgreementsByObjId($a_obj_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM member_agreement ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ".
			"AND accepted = 1";
		
		$res = $ilDB->query($query);
		return $res->numRows() ? true : false;
	}
	
	/**
	 * Check if there is any user agreement
	 *
	 * @access public
	 * @static
	 *
	 * @param int obj_id
	 */
	public static function _hasAgreements()
	{
		global $ilDB;
		
		$query = "SELECT * FROM member_agreement ".
			"WHERE accepted = 1";
		
		$res = $ilDB->query($query);
		return $res->numRows() ? true : false;
	}

	/**
	 * Check if user has accepted agreement
	 *
	 * @access public
	 * @static
	 *
	 * @param
	 */
	public static function _hasAccepted($a_usr_id,$a_obj_id)
	{
		global $ilDB;
		
		$query = "SELECT accepted FROM member_agreement ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ".
			"AND obj_id = ".$ilDB->quote($a_obj_id ,'integer');
		$res = $ilDB->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		
		return $row->accepted == 1 ? true : false;
	}
	
	/**
	 * Lookup users who have accepted the agreement
	 * @param int $a_obj_id
	 * @return 
	 */
	public static function lookupAcceptedAgreements($a_obj_id)
	{
		global $ilDB;
		
		$query = "SELECT usr_id FROM member_agreement ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer').' '.
			"AND accepted = 1 ";
			
		$res = $ilDB->query($query);			
		$user_ids = array();
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$user_ids[] = $row['usr_id'];
		}
		return $user_ids;
	}
	
	
	
	/**
	 * Delete all entries by user
	 *
	 * @access public static
	 * 
	 * @param int user id
	 * 
	 */
	public static function _deleteByUser($a_usr_id)
	{
	 	global $ilDB;
	 	
	 	$query = "DELETE FROM member_agreement ".
	 		"WHERE usr_id =".$ilDB->quote($a_usr_id ,'integer')." ";
		$res = $ilDB->manipulate($query);
		
		return true;
	}
	
	/**
	 * Delete all entries by obj_id
	 *
	 * @access public static
	 * @param int obj_id
	 * 
	 */
	public static function _deleteByObjId($a_obj_id)
	{
	 	global $ilDB;
	 	
	 	$query = "DELETE FROM member_agreement ".
	 		"WHERE obj_id =".$ilDB->quote($a_obj_id ,'integer')." ";
		$res = $ilDB->manipulate($query);
		
		return true;
	 	
	}
	
	/**
	 * Reset all. Set all aggrement to 0.
	 * This is called after global settings have been modified.
	 *
	 * @access public
	 * @param
	 * 
	 */
	public static function _reset()
	{
	 	global $ilDB;
	 	
	 	$query = "UPDATE member_agreement SET accepted = 0 ";
	 	$res = $ilDB->manipulate($query);
	 	
	 	return true;
	}
	
	/**
	 * Reset all agreements for a specific container
	 *
	 * @access public static
	 * @param int obj_id of container
	 * 
	 */
	public static function _resetContainer($a_container_id)
	{
	 	global $ilDB;
	 	
	 	$query = "UPDATE member_agreement ".
	 		"SET accepted = 0 ".
	 		"WHERE obj_id = ".$ilDB->quote($a_container_id ,'integer')." ";
	 	$res = $ilDB->manipulate($query);
		
		return true;
	}
	/**
	 * set accepted
	 *
	 * @access public
	 * @param bool status
	 * 
	 */
	public function setAccepted($a_status)
	{
	 	$this->accepted = $a_status;
	}
	
	/**
	 * set acceptance time
	 *
	 * @access public
	 * @param int unix time of acceptance
	 * 
	 */
	public function setAcceptanceTime($a_timest)
	{
	 	$this->acceptance_time = $a_timest;
	}
	/**
	 * Checks whether the agreement is accepted
	 * This function return always true if no acceptance is required by global setting
	 *
	 * @access public
	 * @return bool
	 */
	public function agreementRequired()
	{
	 	if(!$this->privacy->confirmationRequired($this->type) and !ilCourseDefinedFieldDefinition::_hasFields($this->obj_id))
	 	{
	 		return false;
	 	}
	 	return $this->accepted ? false : true;
	}
	
	/**
	 * Is accepted
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function isAccepted()
	{
	 	return (bool) $this->accepted;
	}
	
	/**
	 * get Acceptance time
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getAcceptanceTime()
	{
	 	return $this->acceptance_time;
	}
	
	/**
	 * save Acceptance settings
	 *
	 * @access public
	 * 
	 */
	public function save()
	{
		global $ilDB;
		
		$this->delete();
		
	 	$query = "INSERT INTO member_agreement (usr_id,obj_id,accepted,acceptance_time) ".
	 		"VALUES( ".
	 		$this->db->quote($this->user_id ,'integer').", ".
	 		$this->db->quote($this->obj_id ,'integer').", ".
	 		$this->db->quote((int) $this->isAccepted() ,'integer').", ".
	 		$this->db->quote($this->getAcceptanceTime() ,'integer')." ".
	 		")";
	 	$ilDB->manipulate($query);
		return true;	
	}
	
	/**
	 * Delete entry
	 *
	 * @access public
	 * 
	 */
	public function delete()
	{
	 	global $ilDB;
	 	
	 	$query = "DELETE FROM member_agreement ".
	 		"WHERE usr_id = ".$this->db->quote($this->user_id ,'integer')." ".
	 		"AND obj_id = ".$this->db->quote($this->obj_id ,'integer');
	 	$res = $ilDB->manipulate($query);
		return true;			
	}
	
	/**
	 * Read user entries
	 *
	 * @access private
	 */
	public function read()
	{
	 	$query = "SELECT * FROM member_agreement ".
	 		"WHERE usr_id = ".$this->db->quote($this->user_id ,'integer')." ".
	 		"AND obj_id = ".$this->db->quote($this->obj_id ,'integer')." ";
	 		
	 	$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
		 	$this->accepted = $row->accepted;
		 	$this->acceptance_time = $row->acceptance_time;
		}
	}
}
?>