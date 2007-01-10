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

/** 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ModulesCourse
*/
class ilCourseAgreement
{
	private $db;
	private $user_id;
	private $obj_id;
	
	private $privacy;
	
	private $accepted = false;
	private $acceptance_time = 0;
	
	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct($a_usr_id,$a_obj_id)
	{
	 	global $ilDB;
	 	
	 	$this->db = $ilDB;
	 	$this->user_id = $a_usr_id;
	 	$this->obj_id = $a_obj_id;
	 	
	 	$this->privacy = ilPrivacySettings::_getInstance();
	 	
	 	if($this->privacy->confirmationRequired())
	 	{
		 	$this->read();
	 	}
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
	 		"WHERE usr_id =".$ilDB->quote($a_usr_id)." ";
		$ilDB->query($query);
		
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
	 		"WHERE obj_id =".$ilDB->quote($a_obj_id)." ";
		$ilDB->query($query);
		
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
	 	$ilDB->query($query);
	 	
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
	 		"WHERE obj_id =".$ilDB->quote($a_container_id)." ";
	 	$ilDB->query($query);
		
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
	 	$this->accepted = true;
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
	 	if(!$this->privacy->confirmationRequired())
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
	 * Read user entries
	 *
	 * @access private
	 */
	private function read()
	{
	 	$query = "SELECT * FROM member_agreement ".
	 		"WHERE usr_id = ".$this->db->quote($this->user_id)." ".
	 		"AND obj_id = ".$this->db->quote($this->obj_id)." ";
	 		
	 	$res = $this->db->query($query);
	 	$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
	 	$this->accepted = $row->accepted;
	 	$this->acceptance_time = $row->acceptance_time;
	}
}
?>