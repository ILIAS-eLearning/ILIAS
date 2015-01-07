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
include_once('Services/Membership/classes/class.ilMemberAgreement.php');
include_once('Modules/Course/classes/class.ilCourseParticipants.php');
include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
include_once('Services/User/classes/class.ilUserDefinedData.php');
include_once('Services/User/classes/class.ilUserFormSettings.php');

define("IL_MEMBER_EXPORT_CSV_FIELD_SEPERATOR",',');
define("IL_MEMBER_EXPORT_CSV_STRING_DELIMITER",'"');


/** 
* Class for generation of member export files
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup Modules/Course 
*/
class ilMemberExport
{
	const EXPORT_CSV = 1;
	const EXPORT_EXCEL = 2;
	
	
	private $ref_id;
	private $obj_id;
	private $type;
	private $members;
	
	private $lng;
	
	private $settings;
	
	private $export_type = null;
	private $filename = null;
	
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
	public function __construct($a_ref_id, $a_type = self::EXPORT_CSV)
	{
		global $ilObjDataCache,$lng;
		
		$this->lng = $lng;
		
		$this->export_type = $a_type;
		
	 	$this->ref_id = $a_ref_id;
	 	$this->obj_id = $ilObjDataCache->lookupObjId($this->ref_id);
		$this->type = ilObject::_lookupType($this->obj_id);
		
		$this->initMembers();
		 	
		$this->agreement = ilMemberAgreement::_readByObjId($this->obj_id);
	 	$this->settings = new ilUserFormSettings('memexp');
	 	$this->privacy = ilPrivacySettings::_getInstance();
	}
	
	/**
	 * set filename
	 * @param object $a_file
	 * @return 
	 */
	public function setFilename($a_file)
	{
		$this->filename = $a_file;
	}
	
	/**
	 * get filename
	 * @return 
	 */
	public function getFilename()
	{
		return $this->filename;
	}
	
	/**
	 * get ref_id 
	 * @return 
	 */
	public function getRefId()
	{
		return $this->ref_id;
	}
	
	/**
	 * get obj type
	 * @return 
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * get current export type
	 * @return 
	 */
	public function getExportType()
	{
		return $this->export_type;
	}
	
	/**
	 * Get obj id
	 * @return 
	 */
	public function getObjId()
	{
		return $this->obj_id;
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
		
		// DONE: Switch different export types
		switch($this->getExportType())
		{
			case self::EXPORT_CSV:
			 	$this->createCSV();
				break;
				
			case self::EXPORT_EXCEL:
				$this->createExcel();
				break;
		}
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
	 * 
	 * @return 
	 */
	public function createExcel()
	{
		include_once "./Services/Excel/classes/class.ilExcelUtils.php";
		include_once "./Services/Excel/classes/class.ilExcelWriterAdapter.php";
		$adapter = new ilExcelWriterAdapter($this->getFilename(), false);
		$workbook = $adapter->getWorkbook();
		$this->worksheet = $workbook->addWorksheet();
		$this->write();
		$workbook->close();
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
		
		$this->write();
	}
	
	
	
	/**
	 * Write on column
	 * @param object $a_value
	 * @param object $a_row
	 * @param object $a_col
	 * @return 
	 */
	protected function addCol($a_value,$a_row,$a_col)
	{
		switch($this->getExportType())
		{
			case self::EXPORT_CSV:
				$this->csv->addColumn($a_value);
				break;
				
			case self::EXPORT_EXCEL:
				$this->worksheet->write($a_row,$a_col,$a_value);
				break;
		}
	}
	
	/**
	 * Add row
	 * @return 
	 */
	protected function addRow()
	{
		switch($this->getExportType())
		{
			case self::EXPORT_CSV:
				$this->csv->addRow();
				break;
			
			case self::EXPORT_EXCEL:
				break;
		}
	}
	
	/**
	 * Get ordered enabled fields
	 *
	 * @access public
	 * @param
	 * 
	 */
	protected function getOrderedExportableFields()
	{
		include_once('Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
		include_once('Services/PrivacySecurity/classes/class.ilExportFieldsInfo.php');
		include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
		include_once('Services/User/classes/class.ilUserDefinedFields.php');

		$field_info = ilExportFieldsInfo::_getInstanceByType(ilObject::_lookupType($this->obj_id));
		$field_info->sortExportFields();
	 	$fields[] = 'role';
	 	// Append agreement info
	 	$privacy = ilPrivacySettings::_getInstance();
	 	if($privacy->courseConfirmationRequired() or ilCourseDefinedFieldDefinition::_hasFields($this->obj_id))
	 	{
	 		$fields[] = 'agreement';
	 	}

	 	foreach($field_info->getExportableFields() as $field)
	 	{
	 		if($this->settings->enabled($field))
	 		{
		 		$fields[] = $field; 
	 		}
	 	}
	 	
	 	$udf = ilUserDefinedFields::_getInstance();
	 	foreach($udf->getCourseExportableFields() as $field_id => $udf_data)
	 	{
			if($this->settings->enabled('udf_'.$field_id))
			{
				$fields[] = 'udf_'.$field_id;
			}
	 	}
	 	
	 	// Add course specific fields
		foreach(ilCourseDefinedFieldDefinition::_getFields($this->obj_id) as $field_obj)
		{
			if($this->settings->enabled('cdf_'.$field_obj->getId()))
			{
				$fields[] = 'cdf_'.$field_obj->getId();
			}
		}	 	
	 	return $fields ? $fields : array();
	}
	
	/**
	 * Write data
	 * @return 
	 */
	protected function write()
	{
		// Add header line
		$row = 0;
		$col = 0;
		foreach($all_fields = $this->getOrderedExportableFields() as $field)
		{
			switch($field)
			{
				case 'role':
					#$this->csv->addColumn($this->lng->txt($this->getType().'_role_status'));
					$this->addCol($this->lng->txt($this->getType().'_role_status'), $row, $col++);
					break;
				case 'agreement':
					#$this->csv->addColumn($this->lng->txt('ps_agreement_accepted'));
					$this->addCol($this->lng->txt('ps_agreement_accepted'), $row, $col++);
					break;
				case 'consultation_hour':
					$this->lng->loadLanguageModule('dateplaner');
					$this->addCol($this->lng->txt('cal_ch_field_ch'), $row, $col++);
					break;
				
				default:
					if(substr($field,0,4) == 'udf_')
					{
						$field_id = explode('_',$field);
						include_once('Services/User/classes/class.ilUserDefinedFields.php');
						$udf = ilUserDefinedFields::_getInstance();
						$def = $udf->getDefinition($field_id[1]);
						#$this->csv->addColumn($def['field_name']);						
						$this->addCol($def['field_name'], $row, $col++);
					}
					elseif(substr($field,0,4) == 'cdf_')
					{
						$field_id = explode('_',$field);
						#$this->csv->addColumn(ilCourseDefinedFieldDefinition::_lookupName($field_id[1]));
						$this->addCol(ilCourseDefinedFieldDefinition::_lookupName($field_id[1]),$row,$col++);
					}elseif($field == "username")//User Name Presentation Guideline; username should be named login
					{
						$this->addCol($this->lng->txt("login"), $row, $col++);
					}
					else
					{
						#$this->csv->addColumn($this->lng->txt($field));
						$this->addCol($this->lng->txt($field), $row, $col++);
					}
					break;
			}
		}
		#$this->csv->addRow();
		$this->addRow();
		// Add user data
		foreach($this->user_ids as $usr_id)
		{
			$row++;
			$col = 0;
			
			$udf_data = new ilUserDefinedData($usr_id);
			foreach($all_fields as $field)
			{
				// Handle course defined fields
				if($this->addUserDefinedField($udf_data,$field,$row,$col))
				{
					$col++;
					continue;
				}
				
				if($this->addCourseField($usr_id,$field,$row,$col))
				{
					$col++;
					continue;
				}
				
				switch($field)
				{
					case 'role':
						switch($this->user_course_data[$usr_id]['role'])
						{
							case IL_CRS_ADMIN:
								#$this->csv->addColumn($this->lng->txt('crs_admin'));
								$this->addCol($this->lng->txt('crs_admin'), $row, $col++);
								break;
								
							case IL_CRS_TUTOR:
								#$this->csv->addColumn($this->lng->txt('crs_tutor'));
								$this->addCol($this->lng->txt('crs_tutor'), $row, $col++);
								break;

							case IL_CRS_MEMBER:
								#$this->csv->addColumn($this->lng->txt('crs_member'));
								$this->addCol($this->lng->txt('crs_member'), $row, $col++);
								break;
								
							case IL_GRP_ADMIN:
								#$this->csv->addColumn($this->lng->txt('il_grp_admin'));
								$this->addCol($this->lng->txt('il_grp_admin'), $row, $col++);
								break;
								
							case IL_GRP_MEMBER:
								#$this->csv->addColumn($this->lng->txt('il_grp_member'));
								$this->addCol($this->lng->txt('il_grp_member'), $row, $col++);
								break;
								
							case 'subscriber':
								#$this->csv->addColumn($this->lng->txt($this->getType().'_subscriber'));
								$this->addCol($this->lng->txt($this->getType().'_subscriber'), $row, $col++);
								break;
							
							default:
								#$this->csv->addColumn($this->lng->txt('crs_waiting_list'));
								$this->addCol($this->lng->txt('crs_waiting_list'), $row, $col++);
								break;
							
						}
						break;
					
					case 'agreement':
						if(isset($this->agreement[$usr_id]))
						{
							if($this->agreement[$usr_id]['accepted'])
							{
								#$this->csv->addColumn(ilFormat::formatUnixTime($this->agreement[$usr_id]['acceptance_time'],true));
								$this->addCol(ilFormat::formatUnixTime($this->agreement[$usr_id]['acceptance_time'],true),$row,$col++);
							}
							else
							{
								#$this->csv->addColumn($this->lng->txt('ps_not_accepted'));
								$this->addCol($this->lng->txt('ps_not_accepted'),$row,$col++);
							}
						}
						else
						{
							#$this->csv->addColumn($this->lng->txt('ps_not_accepted'));
							$this->addCol($this->lng->txt('ps_not_accepted'),$row,$col++);
						}
						break;
						
					// These fields are always enabled
					case 'username':
						#$this->csv->addColumn($this->user_profile_data[$usr_id]['login']);
						$this->addCol($this->user_profile_data[$usr_id]['login'],$row,$col++);
						break;
						
					case 'firstname':
					case 'lastname':
						#$this->csv->addColumn($this->user_profile_data[$usr_id][$field]);
						$this->addCol($this->user_profile_data[$usr_id][$field],$row,$col++);
						break;
					
					case 'consultation_hour':
						include_once './Services/Booking/classes/class.ilBookingEntry.php';
						$bookings = ilBookingEntry::lookupManagedBookingsForObject($this->obj_id, $GLOBALS['ilUser']->getId());
						
						$uts = array();
						foreach((array) $bookings[$usr_id] as $ut)
						{
							ilDatePresentation::setUseRelativeDates(false);
							$tmp = ilDatePresentation::formatPeriod(
									new ilDateTime($ut['dt'],IL_CAL_UNIX),
									new ilDateTime($ut['dtend'],IL_CAL_UNIX)
							);
							if(strlen($ut['explanation']))
							{
								$tmp .= ' '.$ut['explanation'];
							}
							$uts[] = $tmp;
						}
						$uts_str = implode(',',$uts);
						$this->addCol($uts_str, $row, $col++);
						break;
											
					default:
						// Check aggreement
						if((!$this->privacy->courseConfirmationRequired() and !ilCourseDefinedFieldDefinition::_getFields($this->obj_id))
							or $this->agreement[$usr_id]['accepted'])
						{
							#$this->csv->addColumn($this->user_profile_data[$usr_id][$field]);
							$this->addCol($this->user_profile_data[$usr_id][$field],$row,$col++);
						}
						else
						{
							#$this->csv->addColumn('');
							$this->addCol('', $row, $col++);
						}
						break;
						
				}
			}
			#$this->csv->addRow();
			$this->addRow();		
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
	 			$this->user_course_data[$user_id]['role'] = $this->getType() == 'crs' ? IL_CRS_ADMIN : IL_GRP_ADMIN;
	 		}
	 		elseif($this->members->isTutor($user_id))
	 		{
	 			$this->user_course_data[$user_id]['role'] = IL_CRS_TUTOR;
	 		}
	 		elseif($this->members->isMember($user_id))
	 		{
	 			$this->user_course_data[$user_id]['role'] = $this->getType() == 'crs' ? IL_CRS_MEMBER : IL_GRP_MEMBER;
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
	private function addCourseField($a_usr_id,$a_field,$row,$col)
	{
	 	if(substr($a_field,0,4) != 'cdf_')
	 	{
	 		return false;
	 	}
	 	if((!$this->privacy->courseConfirmationRequired() and ilCourseDefinedFieldDefinition::_getFields($this->obj_id)) 
	 		or $this->agreement[$a_usr_id]['accepted'])
	 	{
	 		$field_info = explode('_',$a_field);
	 		$field_id = $field_info[1];
	 		$value = $this->user_course_fields[$a_usr_id][$field_id];
	 		#$this->csv->addColumn($value);
			$this->addCol($value, $row, $col);
	 		return true;
	 	}
	 	#$this->csv->addColumn('');
		$this->addCol('', $row, $col);
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
	private function addUserDefinedField($udf_data,$a_field,$row,$col)
	{
	 	if(substr($a_field,0,4) != 'udf_')
	 	{
	 		return false;
	 	}
	 	if(!$this->privacy->courseConfirmationRequired() or $this->agreement[$udf_data->getUserId()]['accepted'])
	 	{
	 		$field_info = explode('_',$a_field);
	 		$field_id = $field_info[1];
	 		$value = $udf_data->get('f_'.$field_id);
	 		#$this->csv->addColumn($value);
			$this->addCol($value, $row, $col);
	 		return true;
	 	}
	 	#$this->csv->addColumn('');
		$this->addCol('', $row, $col);
	}
	
	/**
	 * Init member object
	 * @return 
	 */
	protected function initMembers()
	{
		if($this->getType() == 'crs')
		{
			$this->members = ilCourseParticipants::_getInstanceByObjId($this->getObjId());
		}
		if($this->getType() == 'grp')
		{
			$this->members = ilGroupParticipants::_getInstanceByObjId($this->getObjId());
		}
		return true;
	}
}


?>