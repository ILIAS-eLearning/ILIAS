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
include_once('Modules/Course/classes/Export/class.ilExportUserSettings.php');
include_once('Modules/Course/classes/class.ilCourseMembers.php');

define("IL_MEMBER_EXPORT_CSV_FIELD_SEPERATOR",',');
define("IL_MEMBER_EXPORT_CSV_STRING_DELIMITER",'"');


/** 
* Class for generation of member export files
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup Modules/Course 
*/
class ilMemberExport
{
	private $ref_id;
	private $obj_id;
	private $course;
	private $members;
	
	private $lng;
	
	private $settings;
	
	private $user_ids = array();
	private $user_course_data = array();
	private $user_profile_data = array();
	
	/**
	 * Constructor
	 *
	 * @access public
	 * 
	 */
	public function __construct($a_ref_id)
	{
		global $ilUser,$ilObjDataCache,$lng;
		
		$this->lng = $lng;
		
	 	$this->ref_id = $a_ref_id;
	 	$this->obj_id = $ilObjDataCache->lookupObjId($this->ref_id);
		 	
		$this->course = ilObjectFactory::getInstanceByRefId($this->ref_id,false);
		$this->members = new ilCourseMembers($this->course);
		
	 	$this->settings = new ilExportUserSettings($ilUser->getId());
	}
	
	/**
	 * Create Export File
	 *
	 * @access public
	 * 
	 */
	public function create()
	{
		$this->fetchUsers();
		
		// TODO: Switch different export types
	 	$this->createCSV();

	}
	
	/**
	 * toString method
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getCSVString()
	{
	 	return $this->csv->getCSVString();
	}
	
	/**
	 * Create CSV File
	 *
	 * @access public
	 * 
	 */
	public function createCSV()
	{
		include_once('Services/Utilities/classes/class.ilCSVWriter.php');
		$this->csv = new ilCSVWriter();

		// Add header line
		foreach($all_fields = $this->settings->getOrderedExportableFields() as $field)
		{
			switch($field)
			{
				case 'role':
					$this->csv->addColumn($this->lng->txt('crs_role_status'));
					break;
				default:
					$this->csv->addColumn($this->lng->txt($field));
					break;
			}
		}
		$this->csv->addRow();		
		// Add user data
		foreach($this->user_ids as $usr_id)
		{
			foreach($all_fields as $field)
			{
				switch($field)
				{
					case 'role':
						switch($this->user_course_data[$usr_id]['role'])
						{
							case IL_CRS_ADMIN:
								$this->csv->addColumn($this->lng->txt('crs_admin'));
								break;
								
							case IL_CRS_TUTOR:
								$this->csv->addColumn($this->lng->txt('crs_tutor'));
								break;

							case IL_CRS_MEMBER:
								$this->csv->addColumn($this->lng->txt('crs_member'));
								break;
								
							case 'subscriber':
								$this->csv->addColumn($this->lng->txt('crs_subscriber'));
								break;
							
							default:
								$this->csv->addColumn($this->lng->txt('crs_waiting_list'));
								break;
							
						}
						break;
						
					default:
						$this->csv->addColumn($this->user_profile_data[$usr_id][$field]);
						break;
						
				}
			}
			$this->csv->addRow();		
		}
	}
	
	/**
	 * Fetch all users that will be exported
	 *
	 * @access private
	 * 
	 */
	private function fetchUsers()
	{
		if($this->settings->enabled('admin'))
		{
			$this->user_ids = $tmp_ids = $this->members->getAdmins();
			$this->readCourseData($tmp_ids);
		}
		if($this->settings->enabled('tutor'))
		{
			$this->user_ids = array_merge($tmp_ids = $this->members->getTutors(),$this->user_ids);
			$this->readCourseData($tmp_ids);
		}
		if($this->settings->enabled('member'))
		{
			$this->user_ids = array_merge($tmp_ids = $this->members->getMembers(),$this->user_ids);
			$this->readCourseData($tmp_ids);
		}
		if($this->settings->enabled('subscribers'))
		{
			$this->user_ids = array_merge($tmp_ids = $this->members->getSubscribers(),$this->user_ids);
			$this->readCourseData($tmp_ids,'subscriber');
		}
		if($this->settings->enabled('waiting_list'))
		{
			include_once('Modules/Course/classes/class.ilCourseWaitingList.php');
			$waiting_list = new ilCourseWaitingList($this->obj_id);
			$this->user_ids = array_merge($waiting_list->getUserIds(),$this->user_ids);
			
		}
		// Sort by lastname
		$this->user_ids = ilUtil::_sortIds($this->user_ids,'usr_data','lastname','usr_id');
		
		// Finally read user profile data
		$this->user_profile_data = ilObjUser::_readUsersProfileData($this->user_ids);
	}
	
	/**
	 * Read All User related course data
	 *
	 * @access private
	 * 
	 */
	private function readCourseData($a_user_ids,$a_status = 'member')
	{
	 	foreach($a_user_ids as $user_id)
	 	{
	 		// Read course related data
	 		$this->user_course_data[$user_id] = $this->members->getUserData($user_id);
	 		switch($a_status)
	 		{
	 			case 'member':
	 				break;
	 			case 'subscriber':
	 				$this->user_course_data[$user_id]['role'] = 'subscriber';
	 				break; 
	 		}
	 	}
	}
	
}


?>