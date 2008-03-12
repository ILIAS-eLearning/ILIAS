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
* @defgroup 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup Services/PrivacySecurity
*/
class ilExportFieldsInfo
{
	private static $instance = null;
	
	private $settings;
	private $db;
	private $lng;
	
	private $possible_fields = array();
	
	/**
	 * Private Singleton Constructor. Use getInstance
	 *
	 * @access private
	 * 
	 */
	private function __construct()
	{
	 	global $ilDB,$ilSetting,$lng;
	 	
	 	$this->db = $ilDB;
	 	$this->lng = $lng;
	 	$this->settings = $ilSetting;
	 	
	 	$this->read();
	}
	
	/**
	 * Get Singleton Instance
	 *
	 * @access public
	 * 
	 */
	public static function _getInstance()
	{
	 	if(is_object(self::$instance))
	 	{
	 		return self::$instance;
	 	}
	 	return self::$instance = new ilExportFieldsInfo();
	}
	
	/**
	 * Check if field is exportable
	 *
	 * @access public
	 * @param string field name
	 * @return bool
	 * 
	 */
	public function isExportable($a_field_name)
	{
		return array_key_exists($a_field_name,$this->possible_fields);
	}
	
	/**
	 * Get informations (exportable) about user data profile fields
	 *
	 * @access public
	 * 
	 */
	public function getFieldsInfo()
	{
	 	return $this->possible_fields;
	}
	
	/**
	 * Get Exportable Fields
	 *
	 * @access public
	 */
	public function getExportableFields()
	{
	 	foreach($this->possible_fields as $field => $exportable)
	 	{
	 		if($exportable)
	 		{
	 			$fields[] = $field;
	 		}
	 	}
	 	return $fields ? $fields : array();
	}
	
	/**
	 * Get exportable fields as info string 
	 *
	 * @access public
	 * @return string info page string
	 */
	public function exportableFieldsToInfoString()
	{
		$fields = array();
		foreach($this->getExportableFields() as $field)
		{
			$fields[] = $this->lng->txt($field);
		}
		return implode('<br />',$fields);
	}
	
	/**
	 * Read info about exportable fields
	 *
	 * @access private
	 * 
	 */
	private function read()
	{
		$this->possible_fields = array(
			'login'		=> 1,
			'gender' => 1,
			'lastname' => 1,
			'firstname' => 1, 
			'title' => 0,
			'institution' => 0,
			'department' => 0,
			'street' => 0,
			'zipcode' => 0,
			'city' => 0,
			'country' => 0,
			'phone_home' => 0,
			'phone_mobile' => 0,
			'phone_office' => 0,
			'fax' => 0,
			'email' => 0,
			'matriculation' => 0);

		$settings_all = $this->settings->getAll();
		foreach($settings_all as $key => $value)
		{
			if(stristr($key,'usr_settings_course_export_') and $value)
			{
				$field = substr($key,27);
				if(array_key_exists($field,$this->possible_fields))
				{
					$this->possible_fields[$field] = 1;
				}
			}
		}
		return true;
	}
}
?>