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
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup Modules/Course 
*/
class ilExportUserSettings
{
	private $db;
	private $user_id;
	private $obj_id;
	private $settings = array();
	
	/**
	 * Constructor
	 *
	 * @access public
	 * 
	 */
	public function __construct($a_user_id,$a_obj_id)
	{
	 	global $ilDB;
	 	
	 	$this->user_id = $a_user_id;
	 	$this->obj_id = $a_obj_id;
	 	$this->db = $ilDB;
	 	
	 	$this->read();
	}
	
	/**
	 * Delete user related data
	 *
	 * @access public
	 * 
	 */
	public static function _delete($a_usr_id)
	{
	 	global $ilDB;
	 	
	 	$query = "DELETE FROM member_export_settings WHERE user_id = ".$ilDB->quote($a_usr_id);
	 	$ilDB->query($query);
		return true;
	}
	
	/**
	 * Set Settings
	 *
	 * @access public
	 * @param array Array of Settings
	 * 
	 */
	public function set($a_data)
	{
	 	$this->settings = $a_data;
	}
	
	/**
	 * Check if a specific option is enabled
	 *
	 * @access public
	 * @param strin option
	 * 
	 */
	public function enabled($a_option)
	{
	 	if(array_key_exists($a_option,$this->settings) and $this->settings[$a_option])
	 	{
	 		return true;
	 	}
	 	return false;
	}
	
	/**
	 * Get ordered enabled fields
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getOrderedExportableFields()
	{
		include_once('Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
		include_once('Services/PrivacySecurity/classes/class.ilExportFieldsInfo.php');
		include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
		include_once('Services/User/classes/class.ilUserDefinedFields.php');

		$field_info = ilExportFieldsInfo::_getInstance();

	 	$fields[] = 'role';
	 	// Append agreement info
	 	$privacy = ilPrivacySettings::_getInstance();
	 	if($privacy->confirmationRequired())
	 	{
	 		$fields[] = 'agreement';
	 	}

	 	foreach($field_info->getExportableFields() as $field)
	 	{
	 		if($this->enabled($field))
	 		{
		 		$fields[] = $field; 
	 		}
	 	}
	 	
	 	$udf = ilUserDefinedFields::_getInstance();
	 	foreach($udf->getCourseExportableFields() as $field_id => $udf_data)
	 	{
	 		$fields[] = 'udf_'.$field_id;
	 	}
	 	
	 	// Add course specific fields
		foreach(ilCourseDefinedFieldDefinition::_getFields($this->obj_id) as $field_obj)
		{
			if($this->enabled('cdf_'.$field_obj->getId()))
			{
				$fields[] = 'cdf_'.$field_obj->getId();
			}
		}	 	
	 	return $fields ? $fields : array();
	}
	
	/**
	 * Store settings in DB
	 *
	 * @access public
	 * 
	 */
	public function store()
	{
	 	$query = "DELETE FROM member_export_user_settings WHERE user_id = ".$this->db->quote($this->user_id);
	 	$this->db->query($query);
		
		$query = "INSERT INTO member_export_user_settings SET user_id = ".$this->db->quote($this->user_id).", ".
			"settings = '".addslashes(serialize($this->settings))."' ";
		$this->db->query($query);
		$this->read();
	}
	
	/**
	 * Read store settings
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function read()
	{
	 	$query = "SELECT * FROM member_export_user_settings WHERE user_id = ".$this->db->quote($this->user_id);
	 	$res = $this->db->query($query);
		
	 	if($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->settings = unserialize(stripslashes($row->settings));
	 	}
		return true;
	}
}

?>