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
include_once('Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
include_once('Modules/Course/classes/class.ilCourseAgreement.php');
include_once('Modules/Course/classes/class.ilCourseParticipants.php');
include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
include_once('Services/User/classes/class.ilUserDefinedData.php');

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
	private $user_course_fields = array();
	private $user_profile_data = array();
	private $privacy;
	
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
		$this->members = ilCourseParticipants::_getInstanceByObjId($this->obj_id);
		$this->agreement = ilCourseAgreement::_readByObjId($this->obj_id);
	 	$this->settings = new ilExportUserSettings($ilUser->getId(),$this->obj_id);
	 	$this->privacy = ilPrivacySettings::_getInstance();
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
				case 'agreement':
					$this->csv->addColumn($this->lng->txt('ps_agreement_accepted'));
					break;
				default:
					if(substr($field,0,4) == 'udf_')
					{
						$field_id = explode('_',$field);
						include_once('Services/User/classes/class.ilUserDefinedFields.php');
						$udf = ilUserDefinedFields::_getInstance();
						$def = $udf->getDefinition($field_id[1]);
						$this->csv->addColumn($def['field_name']);						
					}
					elseif(substr($field,0,4) == 'cdf_')
					{
						$field_id = explode('_',$field);
						$this->csv->addColumn(ilCourseDefinedFieldDefinition::_lookupName($field_id[1]));
					}
					else
					{
						$this->csv->addColumn($this->lng->txt($field));
					}
					break;
			}
		}
		$this->csv->addRow();		
		// Add user data
		foreach($this->user_ids as $usr_id)
		{
			$udf_data = new ilUserDefinedData($usr_id);
			
			foreach($all_fields as $field)
			{
				// Handle course defined fields
				if($this->addUserDefinedField($udf_data,$field))
				{
					continue;
				}
				
				if($this->addCourseField($usr_id,$field))
				{
					continue;
				}
				
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
					
					case 'agreement':
						if(isset($this->agreement[$usr_id]))
						{
							if($this->agreement[$usr_id]['accepted'])
							{
								$this->csv->addColumn(ilFormat::formatUnixTime($this->agreement[$usr_id]['acceptance_time'],true));
							}
							else
							{
								$this->csv->addColumn($this->lng->txt('ps_not_accepted'));
							}
						}
						else
						{
							$this->csv->addColumn($this->lng->txt('ps_not_accepted'));
						}
						break;
						
					// These fields are always enabled
					case 'login':
					case 'firstname':
					case 'lastname':
						$this->csv->addColumn($this->user_profile_data[$usr_id][$field]);
						break;
											
					default:
						// Check aggreement
						if(!$this->privacy->confirmationRequired() or $this->agreement[$usr_id]['accepted'])
						{
							$this->csv->addColumn($this->user_profile_data[$usr_id][$field]);
						}
						else
						{
							$this->csv->addColumn('');
						}
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
		$this->readCourseSpecificFieldsData();
		
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
	 		if($this->members->isAdmin($user_id))
	 		{
	 			$this->user_course_data[$user_id]['role'] = IL_CRS_ADMIN;
	 		}
	 		elseif($this->members->isTutor($user_id))
	 		{
	 			$this->user_course_data[$user_id]['role'] = IL_CRS_TUTOR;
	 		}
	 		elseif($this->members->isMember($user_id))
	 		{
	 			$this->user_course_data[$user_id]['role'] = IL_CRS_MEMBER;
	 		}
	 		else
	 		{
 				$this->user_course_data[$user_id]['role'] = 'subscriber';
	 		}
	 	}
	}
	
	/**
	 * Read course specific fields data
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function readCourseSpecificFieldsData()
	{
		include_once('Modules/Course/classes/Export/class.ilCourseUserData.php');
	 	$this->user_course_fields = ilCourseUserData::_getValuesByObjId($this->obj_id);
	}
	
	/**
	 * fill course specific fields
	 *
	 * @access private
	 * @param int usr_id
	 * @param string field
	 * @return bool
	 * 
	 */
	private function addCourseField($a_usr_id,$a_field)
	{
	 	if(substr($a_field,0,4) != 'cdf_')
	 	{
	 		return false;
	 	}
	 	if(!$this->privacy->confirmationRequired() or $this->agreement[$a_usr_id]['accepted'])
	 	{
	 		$field_info = explode('_',$a_field);
	 		$field_id = $field_info[1];
	 		$value = $this->user_course_fields[$a_usr_id][$field_id];
	 		$this->csv->addColumn($value);
	 		return true;
	 	}
	 	$this->csv->addColumn('');
	 	return true;
	 	
	}
	
	/**
	 * Add user defined fields
	 *
	 * @access private
	 * @param object user defined data object
	 * @param int field
	 * 
	 */
	private function addUserDefinedField($udf_data,$a_field)
	{
	 	if(substr($a_field,0,4) != 'udf_')
	 	{
	 		return false;
	 	}
	 	if(!$this->privacy->confirmationRequired() or $this->agreement[$udf_data->getUserId()]['accepted'])
	 	{
	 		$field_info = explode('_',$a_field);
	 		$field_id = $field_info[1];
	 		$value = $udf_data->get($field_id);
	 		$this->csv->addColumn($value);
	 		return true;
	 	}
	 	$this->csv->addColumn('');
	}
}


?>