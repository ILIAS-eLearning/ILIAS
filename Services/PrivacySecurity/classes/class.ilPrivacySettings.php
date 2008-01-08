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
* @author Stefan Meyer <smeyer@databay.de>
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
		private $export_confirm;
		private $fora_statistics;
		private $access_times;
		private $ref_id;

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
	 *
	 */
	public function _getInstance()
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

	public function enabledExport()
	{
		return $this->export_course;
	}
	public function enableExport($a_status)
	{
		$this->export_course = (bool) $a_status;
	}


	/**
	*	write access to property fora statitics
	* @param bool $a_status	value to set property
	*/	
	public function enableForaStatistics ($a_status) {
		$this->fora_statistics = (bool) $a_status;
	}
	
	/**
	*	read access to property enable fora statistics
	* @return bool true if enabled, false otherwise
	*/
	 public function enabledForaStatistics () {
			return $this->fora_statistics;
		}



	public function confirmationRequired()
	{
		return $this->export_confirm;
	}

	public function setConfirmationRequired($a_status)
	{
		$this->export_confirm = (bool) $a_status;
	}
	
	/**
	 * Show group last access times
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function showAccessTimes($a_status)
	{
	 	$this->show_access_times = $a_status;
	}
	
	/**
	 * check if group access time are visible
	 *
	 * @access public
	 * 
	 */
	public function enabledAccessTimes()
	{
	 	return (bool) $this->show_access_times;
	}

	/**
	 * Save settings
	 *
	 *
	 */
	public function save()
	{
	 	$this->settings->set('ps_export_confirm',(bool) $this->confirmationRequired());
	 	$this->settings->set('ps_export_course',(bool) $this->enabledExport());
	 	$this->settings->set('enable_fora_statistics',(bool) $this->enabledForaStatistics());
	 	$this->settings->set('ps_access_times',(bool) $this->enabledAccessTimes());
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
				"WHERE tree.parent = ".$ilDB->quote(SYSTEM_FOLDER_ID)." ".
				"AND object_data.type = 'ps' ".
				"AND object_reference.ref_id = tree.child ".
				"AND object_reference.obj_id = object_data.obj_id";
		$res = $this->db->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$this->ref_id = $row["ref_id"];

		$this->export_course = (bool) $this->settings->get('ps_export_course',false);
		$this->export_confirm = (bool) $this->settings->get('ps_export_confirm',false);
		$this->fora_statistics = (bool) $this->settings->get('enable_fora_statistics',false);
		$this->show_access_times = (bool) $this->settings->get('ps_access_times',false);

	}

	/**
	 * validate settings
	 *
	 * @return 0, if everything is ok, an error code otherwise
	 */
	public function validate() {
	    return 0;
	}


}
?>