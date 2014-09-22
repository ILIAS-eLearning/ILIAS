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
* Singleton class that stores all privacy settings
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup Services/PrivacySecurity
*/

class ilPrivacySettings
{
    private static $instance = null;
		private $db;
		private $settings;
	
		private $export_course;
		private $export_group;
		private $export_confirm_course;
		private $export_confirm_group;
		private $fora_statistics;
		private $anonymous_fora;
		private $rbac_log;
		private $rbac_log_age;
		private $show_grp_access_times;
		private $show_crs_access_times;
		private $ref_id;
		private $sahs_protocol_data;
		private $export_scorm;

    /**
	 * Private constructor: use _getInstance()
	 *
	 * @access private
	 * @param
	 *
	 */
	private function __construct()
	{
		global $ilSetting,$ilDB;

		$this->db = $ilDB;
		$this->settings = $ilSetting;

	 	$this->read();
	}

	/**
	 * Get instance of ilPrivacySettings
	 *
	 * @access public
	 * @return \ilPrivacySettings
	 */
	public static function _getInstance()
	{
		if(is_object(self::$instance))
		{
			return self::$instance;
		}
	 	return self::$instance = new ilPrivacySettings();
	}

	public function getPrivacySettingsRefId()
	{
		return $this->ref_id;
	}

	public function enabledCourseExport()
	{
		return $this->export_course;
	}
	
	public function enabledGroupExport()
	{
		return $this->export_group;
	}
	
	/**
	 * Check if a user has the permission to access approved user profile fields, course related user data and custom user data
	 * @todo rename
	 * @param object $a_ref_id
	 * @return 
	 */
	public function checkExportAccess($a_ref_id,$a_user_id = 0)
	{
		global $ilUser,$ilAccess,$rbacsystem;
		
		$user_id = $a_user_id ? $a_user_id : $ilUser->getId();
		
		if(ilObject::_lookupType($a_ref_id, true) == 'crs')
		{
			return $this->enabledCourseExport() and $ilAccess->checkAccessOfUser($user_id,'write','',$a_ref_id) and $rbacsystem->checkAccessOfUser($user_id,'export_member_data',$this->getPrivacySettingsRefId());
		}
		else
		{
			return $this->enabledGroupExport() and $ilAccess->checkAccessOfUser($user_id,'write','',$a_ref_id) and $rbacsystem->checkAccessOfUser($user_id,'export_member_data',$this->getPrivacySettingsRefId());
		}		
	}

	public function enableCourseExport($a_status)
	{
		$this->export_course = (bool) $a_status;
	}

	public function enableGroupExport($a_status)
	{
		$this->export_group = (bool) $a_status;
	}

	/**
	*	write access to property fora statitics
	* @param bool $a_status	value to set property
	*/	
	public function enableForaStatistics ($a_status) 
	{
		$this->fora_statistics = (bool) $a_status;
	}
	
	/**
	*	read access to property enable fora statistics
	* @return bool true if enabled, false otherwise
	*/
	public function enabledForaStatistics () 
	{
			return $this->fora_statistics;
	}

	/**
	* write access to property anonymous fora
	* @param bool $a_status	value to set property
	*/	
	public function enableAnonymousFora ($a_status)
	{
		$this->anonymous_fora = (bool) $a_status;
	}
	
	/**
	* read access to property enable anonymous fora
	* @return bool true if enabled, false otherwise
	*/
	public function enabledAnonymousFora ()
	{
		return $this->anonymous_fora;
	}

	/**
	* write access to property rbac_log
	* @param bool $a_status	value to set property
	*/
	public function enableRbacLog ($a_status)
	{
		$this->rbac_log = (bool) $a_status;
	}

	/**
	* read access to property enable rbac log
	* @return bool true if enabled, false otherwise
	*/
	public function enabledRbacLog ()
	{
		return $this->rbac_log;
	}

	/**
	* write access to property rbac log age
	*  @param int $a_age	value to set property
	*/
	public function setRbacLogAge ($a_age)
	{
		$this->rbac_log_age = (int) $a_age;
	}

	/**
	* read access to property rbac log age
	* @return int
	*/
	public function getRbacLogAge ()
	{
		return $this->rbac_log_age;
	}
	
	public function confirmationRequired($a_type)
	{
		switch($a_type)
		{
			case 'crs':
				return $this->courseConfirmationRequired();
				
			case 'grp':
				return $this->groupConfirmationRequired();
		}
		return false;
	}

	public function courseConfirmationRequired()
	{
		return $this->export_confirm_course;
	}

	public function groupConfirmationRequired()
	{
		return $this->export_confirm_group;
	}

	public function setCourseConfirmationRequired($a_status)
	{
		$this->export_confirm_course = (bool) $a_status;
	}
	
	public function setGroupConfirmationRequired($a_status)
	{
		$this->export_confirm_group = (bool) $a_status;
	}

	/**
	 * Show group last access times
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function showGroupAccessTimes($a_status)
	{
	 	$this->show_grp_access_times = $a_status;
	}
	
	/**
	 * check if group access time are visible
	 *
	 * @access public
	 * 
	 */
	public function enabledGroupAccessTimes()
	{
	 	return (bool) $this->show_grp_access_times;
	}
	
	/**
	 * show course access times
	 *
	 * @access public
	 * @param bool status
	 * @return
	 */
	public function showCourseAccessTimes($a_status)
	{
		$this->show_crs_access_times = $a_status;
	}
	
	/**
	 * check if access time are enabled in courses
	 *
	 * @access public
	 * @return
	 */
	public function enabledCourseAccessTimes()
	{
		return (bool) $this->show_crs_access_times;
	}
	
	/**
	 * Check if access time are enabled for a specific type
	 * @param type $a_obj_type
	 * @return type
	 */
	public function enabledAccessTimesByType($a_obj_type)
	{
		switch($a_obj_type)
		{
			case 'crs':
				return $this->enabledCourseAccessTimes();
				
			case 'grp':
				return $this->enabledGroupAccessTimes();
		}
		
	}

	/**
	 * Save settings
	 *
	 *
	 */
	public function save()
	{
	 	$this->settings->set('ps_export_confirm',(bool) $this->courseConfirmationRequired());
	 	$this->settings->set('ps_export_confirm_group',(bool) $this->groupConfirmationRequired());
	 	$this->settings->set('ps_export_course',(bool) $this->enabledCourseExport());
	 	$this->settings->set('ps_export_group',(bool) $this->enabledGroupExport());
	 	$this->settings->set('enable_fora_statistics',(bool) $this->enabledForaStatistics());
		$this->settings->set('enable_anonymous_fora',(bool) $this->enabledAnonymousFora());
	 	$this->settings->set('ps_access_times',(bool) $this->enabledGroupAccessTimes());
	 	$this->settings->set('ps_crs_access_times',(bool) $this->enabledCourseAccessTimes());
	 	$this->settings->set('rbac_log',(bool) $this->enabledRbacLog());
	 	$this->settings->set('rbac_log_age',(int) $this->getRbacLogAge());
		$this->settings->set('enable_sahs_pd',(int) $this->enabledSahsProtocolData());
		$this->settings->set('ps_export_scorm',(bool) $this->enabledExportSCORM());
	}
	/**
	 * read settings
	 *
	 * @access private
	 * @param
	 *
	 */
	private function read()
	{
		global $ilDB;
		
	    $query = "SELECT object_reference.ref_id FROM object_reference,tree,object_data ".
				"WHERE tree.parent = ".$ilDB->quote(SYSTEM_FOLDER_ID,'integer')." ".
				"AND object_data.type = 'ps' ".
				"AND object_reference.ref_id = tree.child ".
				"AND object_reference.obj_id = object_data.obj_id";
		$res = $this->db->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$this->ref_id = $row["ref_id"];

		$this->export_course = (bool) $this->settings->get('ps_export_course',false);
		$this->export_group = (bool) $this->settings->get('ps_export_group',false);
		$this->export_confirm_course = (bool) $this->settings->get('ps_export_confirm',false);
		$this->export_confirm_group = (bool) $this->settings->get('ps_export_confirm_group',false);
		$this->fora_statistics = (bool) $this->settings->get('enable_fora_statistics',false);
		$this->anonymous_fora = (bool) $this->settings->get('enable_anonymous_fora',false);
		$this->show_grp_access_times = (bool) $this->settings->get('ps_access_times',false);
		$this->show_crs_access_times = (bool) $this->settings->get('ps_crs_access_times',false);
		$this->rbac_log = (bool) $this->settings->get('rbac_log',false);
		$this->rbac_log_age = (int) $this->settings->get('rbac_log_age',6);
		$this->sahs_protocol_data = (int) $this->settings->get('enable_sahs_pd', 0);
		$this->export_scorm = (bool) $this->settings->get('ps_export_scorm',false);
	}

	/**
	 * validate settings
	 *
	 * @return 0, if everything is ok, an error code otherwise
	 */
	public function validate() 
	{
	    return 0;
	}

	public function enabledSahsProtocolData()
	{
		return (int) $this->sahs_protocol_data;
	}
	public function enableSahsProtocolData($status)
	{
		$this->sahs_protocol_data = (int) $status;
	}

	// show and export protocol data with name
	public function enabledExportSCORM()
	{
		return $this->export_scorm;
	}
	public function enableExportSCORM($a_status)
	{
		$this->export_scorm = (bool) $a_status;
	}


}
?>
