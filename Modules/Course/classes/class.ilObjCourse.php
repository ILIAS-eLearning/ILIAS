<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* Class ilObjCourse
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
*/

require_once "./classes/class.ilContainer.php";

define('IL_CRS_ACTIVATION_OFFLINE',0);
define('IL_CRS_ACTIVATION_UNLIMITED',1);
define('IL_CRS_ACTIVATION_LIMITED',2);

define('IL_CRS_SUBSCRIPTION_DEACTIVATED',0);
define('IL_CRS_SUBSCRIPTION_UNLIMITED',1);
define('IL_CRS_SUBSCRIPTION_LIMITED',2);

define('IL_CRS_SUBSCRIPTION_CONFIRMATION',2);
define('IL_CRS_SUBSCRIPTION_DIRECT',3);
define('IL_CRS_SUBSCRIPTION_PASSWORD',4);

define('IL_CRS_VIEW_STANDARD',0);
define('IL_CRS_VIEW_OBJECTIVE',1);
define('IL_CRS_VIEW_TIMING',2);
define('IL_CRS_VIEW_ARCHIVE',3);

define('IL_CRS_ARCHIVE_DOWNLOAD',3);
define('IL_CRS_ARCHIVE_NONE',0);

define('IL_CRS_SORT_MANUAL',1);
define('IL_CRS_SORT_TITLE',2);
define('IL_CRS_SORT_ACTIVATION',3);

class ilObjCourse extends ilContainer
{
	var $members_obj;
	var $archives_obj;
	var $items_obj;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjCourse($a_id = 0,$a_call_by_reference = true)
	{
		#define("ILIAS_MODULE","course");
		#define("KEEP_IMAGE_PATH",1);

		$this->SUBSCRIPTION_DEACTIVATED = 1;
		$this->SUBSCRIPTION_CONFIRMATION = 2;
		$this->SUBSCRIPTION_DIRECT = 3;
		$this->SUBSCRIPTION_PASSWORD = 4;
		$this->SUBSCRIPTION_AUTOSUBSCRIPTION = 5;
		$this->SORT_MANUAL = 1;
		$this->SORT_TITLE = 2;
		$this->SORT_ACTIVATION = 3;
		$this->ARCHIVE_DISABLED = 1;
		$this->ARCHIVE_READ = 2;
		$this->ARCHIVE_DOWNLOAD = 3;
		$this->ABO_ENABLED = 1;
		$this->ABO_DISABLED = 0;
		$this->SHOW_MEMBERS_ENABLED = 1;
		$this->SHOW_MEMBERS_DISABLED = 0;

		$this->type = "crs";
		$this->ilObject($a_id,$a_call_by_reference);

		if($a_id)
		{
			#$this->__initMetaObject();
			$this->initCourseMemberObject();
		}
		else
		{

		}

	}

	function getImportantInformation()
	{
		return $this->important;
	}
	function setImportantInformation($a_info)
	{
		$this->important = $a_info;
	}
	function getSyllabus()
	{
		return $this->syllabus;
	}
	function setSyllabus($a_syllabus)
	{
		$this->syllabus = $a_syllabus;
	}
	function getContactName()
	{
		return $this->contact_name;
	}
	function setContactName($a_cn)
	{
		$this->contact_name = $a_cn;
	}
	function getContactConsultation()
	{
		return $this->contact_consultation;
	}
	function setContactConsultation($a_value)
	{
		$this->contact_consultation = $a_value;
	}
	function getContactPhone()
	{
		return $this->contact_phone;
	}
	function setContactPhone($a_value)
	{
		$this->contact_phone = $a_value;
	}
	function getContactEmail()
	{
		return $this->contact_email;
	}
	function setContactEmail($a_value)
	{
		$this->contact_email = $a_value;
	}
	function getContactResponsibility()
	{
		return $this->contact_responsibility;
	}
	function setContactResponsibility($a_value)
	{
		$this->contact_responsibility = $a_value;
	}

	function getActivationType()
	{
		return (int) $this->activation_type;
	}
	function setActivationType($a_type)
	{
		$this->activation_type = $a_type;
	}
	function getActivationUnlimitedStatus()
	{
		return $this->activation_type == IL_CRS_ACTIVATION_UNLIMITED;
		
	} 
	function getActivationStart()
	{
		return $this->activation_start ? $this->activation_start : time();
	}
	function setActivationStart($a_value)
	{
		$this->activation_start = $a_value;
	}
	function getActivationEnd()
	{
		return $this->activation_end ? $this->activation_end : mktime(0,0,0,12,12,date("Y",time())+2);
	}
	function setActivationEnd($a_value)
	{
		$this->activation_end = $a_value;
	}
	function getOfflineStatus()
	{
		return $this->activation_type == IL_CRS_ACTIVATION_OFFLINE;
	}


	function getSubscriptionLimitationType()
	{
		return $this->subscription_limitation_type;
	}
	function setSubscriptionLimitationType($a_type)
	{
		$this->subscription_limitation_type = $a_type;
	}
	function getSubscriptionUnlimitedStatus()
	{
		return $this->subscription_limitation_type == IL_CRS_SUBSCRIPTION_UNLIMITED;
	} 
	function getSubscriptionStart()
	{
		return $this->subscription_start ? $this->subscription_start : time();
	}
	function setSubscriptionStart($a_value)
	{
		$this->subscription_start = $a_value;
	}
	function getSubscriptionEnd()
	{
		return $this->subscription_end ? $this->subscription_end : mktime(0,0,0,12,12,date("Y",time())+2);
	}
	function setSubscriptionEnd($a_value)
	{
		$this->subscription_end = $a_value;
	}
	function getSubscriptionType()
	{
		return $this->subscription_type ? $this->subscription_type : IL_CRS_SUBSCRIPTION_DIRECT;
		#return $this->subscription_type ? $this->subscription_type : $this->SUBSCRIPTION_DEACTIVATED;
	}
	function setSubscriptionType($a_value)
	{
		$this->subscription_type = $a_value;
	}
	function getSubscriptionPassword()
	{
		return $this->subscription_password;
	}
	function setSubscriptionPassword($a_value)
	{
		$this->subscription_password = $a_value;
	}
	function enabledObjectiveView()
	{
		return $this->view_mode == IL_CRS_VIEW_OBJECTIVE;
	}

	function enabledWaitingList()
	{
		return (bool) $this->waiting_list;
	}

	function enableWaitingList($a_status)
	{
		$this->waiting_list = (bool) $a_status;
	}

	function inSubscriptionTime()
	{
		if($this->getSubscriptionUnlimitedStatus())
		{
			return true;
		}
		if(time() > $this->getSubscriptionStart() and time() < $this->getSubscriptionEnd())
		{
			return true;
		}
		return false;
	}

	function getSubscriptionMaxMembers()
	{
		return $this->subscription_max_members;
	}
	function setSubscriptionMaxMembers($a_value)
	{
		$this->subscription_max_members = $a_value;
	}
	
	/**
	 * Check if subscription notification is enabled
	 *
	 * @access public
	 * @static
	 *
	 * @param int course_id
	 */
	public static function _isSubscriptionNotificationEnabled($a_course_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM crs_settings ".
			"WHERE obj_id = ".$ilDB->quote($a_course_id)." ".
			"AND subscription_notify = 1";
		$res = $ilDB->query($query);
		return $res->numRows() ? true : false;
	}
	
	function getSubscriptionNotify()
	{
		return $this->subscription_notify ? true : false;
	}
	function setSubscriptionNotify($a_value)
	{
		$this->subscription_notify = $a_value ? true : false;
	}

	function setViewMode($a_mode)
	{
		$this->view_mode = $a_mode;
	}
	function getViewMode()
	{
		return $this->view_mode;
	}

	function _lookupViewMode($a_id)
	{
		global $ilDB;

		$query = "SELECT view_mode FROM crs_settings WHERE obj_id = ".$ilDB->quote($a_id)." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->view_mode;
		}
		return false;
	}


	function getOrderType()
	{
		return $this->order_type ? $this->order_type : IL_CRS_SORT_TITLE;
	}
	function setOrderType($a_value)
	{
		$this->order_type = $a_value;
	}
	function getArchiveStart()
	{
		return $this->archive_start ? $this->archive_start : time();
	}
	function setArchiveStart($a_value)
	{
		$this->archive_start = $a_value;
	}
	function getArchiveEnd()
	{
		return $this->archive_end ? $this->archive_end : mktime(0,0,0,12,12,date("Y",time())+2);
	}
	function setArchiveEnd($a_value)
	{
		$this->archive_end = $a_value;
	}
	function getArchiveType()
	{
		return $this->archive_type ? IL_CRS_ARCHIVE_DOWNLOAD : IL_CRS_ARCHIVE_NONE;
	}
	function setArchiveType($a_value)
	{
		$this->archive_type = $a_value;
	}
	function setAboStatus($a_status)
	{
		$this->abo = $a_status;
	}
	function getAboStatus()
	{
		return $this->abo;
	}
	function setShowMembers($a_status)
	{
		$this->show_members = $a_status;
	}
	function getShowMembers()
	{
		return $this->show_members;
	}

	function getMessage()
	{
		return $this->message;
	}
	function setMessage($a_message)
	{
		$this->message = $a_message;
	}
	function appendMessage($a_message)
	{
		if($this->getMessage())
		{
			$this->message .= "<br /> ";
		}
		$this->message .= $a_message;
	}

	function getMembers()
	{
		return $this->members_obj->getMembers();
	}


	function isActivated($a_check_archive = false)
	{
		if($a_check_archive)
		{
			if($this->isArchived())
			{
				return true;
			}
		}
		if($this->getOfflineStatus())
		{
			return false;
		}
		if($this->getActivationUnlimitedStatus())
		{
			return true;
		}
		if(time() < $this->getActivationStart() or
		   time() > $this->getActivationEnd())
		{
			return false;
		}
		return true;
	}

	/**
	 * Static version of isActive() to avoid instantiation of course object
	 *
	 * @param int id of user
	 * @return boolean
	 */
	function _isActivated($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM crs_settings ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id)." ";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$type = $row->activation_type;
			$start = $row->activation_start;
			$end = $row->activation_end;
		}
		switch($type)
		{
			case IL_CRS_ACTIVATION_OFFLINE:
				return false;

			case IL_CRS_ACTIVATION_UNLIMITED:
				return true;

			case IL_CRS_ACTIVATION_LIMITED:
				if(time() < $start or
				   time() > $end)
				{
					return false;
				}
				return true;
				
			default:
				return false;
		}
	}

	function _registrationEnabled($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM crs_settings ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id)." ";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$type = $row->subscription_limitation_type;
			$reg_start = $row->subscription_start;
			$reg_end = $row->subscription_end;
		}

		switch($type)
		{
			case IL_CRS_SUBSCRIPTION_UNLIMITED:
				return true;

			case IL_CRS_SUBSCRIPTION_DEACTIVATED:
				return false;

			case IL_CRS_SUBSCRIPTION_LIMITED:
				if(time() > $reg_start and
				   time() < $reg_end)
				{
					return true;
				}
			default:
				return false;
		}
		return false;
	}

	function isArchived()
	{
		if($this->getViewMode() != IL_CRS_VIEW_ARCHIVE)
		{
			return false;
		}
		if(time() < $this->getArchiveStart() or time() > $this->getArchiveEnd())
		{
			return false;
		}
		return true;
	}

	function allowAbo()
	{
		return $this->ABO == $this->ABO_ENABLED;
	}

	function read($a_force_db = false)
	{
		parent::read($a_force_db);

		$this->__readSettings();
	}
	function create($a_upload = false)
	{
		parent::create($a_upload);

		if(!$a_upload)
		{
			$this->createMetaData();
		}
		$this->__createDefaultSettings();
	}
	
	/**
	* Set Latitude.
	*
	* @param	string	$a_latitude	Latitude
	*/
	function setLatitude($a_latitude)
	{
		$this->latitude = $a_latitude;
	}

	/**
	* Get Latitude.
	*
	* @return	string	Latitude
	*/
	function getLatitude()
	{
		return $this->latitude;
	}

	/**
	* Set Longitude.
	*
	* @param	string	$a_longitude	Longitude
	*/
	function setLongitude($a_longitude)
	{
		$this->longitude = $a_longitude;
	}

	/**
	* Get Longitude.
	*
	* @return	string	Longitude
	*/
	function getLongitude()
	{
		return $this->longitude;
	}

	/**
	* Set LocationZoom.
	*
	* @param	int	$a_locationzoom	LocationZoom
	*/
	function setLocationZoom($a_locationzoom)
	{
		$this->locationzoom = $a_locationzoom;
	}

	/**
	* Get LocationZoom.
	*
	* @return	int	LocationZoom
	*/
	function getLocationZoom()
	{
		return $this->locationzoom;
	}

	/**
	* Set Enable Course Map.
	*
	* @param	boolean	$a_enablemap	Enable Course Map
	*/
	function setEnableCourseMap($a_enablemap)
	{
		$this->enablemap = $a_enablemap;
	}

	/**
	* Get Enable Course Map.
	*
	* @return	boolean	Enable Course Map
	*/
	function getEnableCourseMap()
	{
		return $this->enablemap;
	}
	
	/**
	 * Clone course (no member data)
	 *
	 * @access public
	 * @param int target ref_id
	 * @param int copy id
	 * 
	 */
	public function cloneObject($a_target_id,$a_copy_id = 0)
	{
		global $ilDB,$ilUser;
		
	 	$new_obj = parent::cloneObject($a_target_id,$a_copy_id);
	 	
	 	$this->cloneAutoGeneratedRoles($new_obj);
	 	$this->cloneMetaData($new_obj);
	 	
	 	// Assign admin
		$new_obj->initCourseMemberObject();
		$new_obj->members_obj->add($ilUser->getId(),IL_CRS_ADMIN);
		
		// Copy settings
		$this->cloneSettings($new_obj);
		
		// Course Defined Fields
		include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
		ilCourseDefinedFieldDefinition::_clone($this->getId(),$new_obj->getId());
		
		// Clone course files
		include_once('Modules/Course/classes/class.ilCourseFile.php');
		ilCourseFile::_cloneFiles($this->getId(),$new_obj->getId());
		
		// Copy learning progress settings
		include_once('Services/Tracking/classes/class.ilLPObjSettings.php');
		$obj_settings = new ilLPObjSettings($this->getId());
		$obj_settings->cloneSettings($new_obj->getId());
		unset($obj_settings);
		
		return $new_obj;
	}
	
	/**
	 * Clone object dependencies (start objects, preconditions)
	 *
	 * @access public
	 * @param int target ref id of new course
	 * @param int copy id
	 * 
	 */
	public function cloneDependencies($a_target_id,$a_copy_id)
	{
		global $ilObjDataCache;
		
	 	// Clone course start objects
	 	include_once('Modules/Course/classes/class.ilCourseStart.php');
	 	$start = new ilCourseStart($this->getRefId(),$this->getId());
	 	$start->cloneDependencies($a_target_id,$a_copy_id);
	 	
	 	// Clone course item settings
		$this->initCourseItemObject();
		$this->items_obj->cloneDependencies($a_target_id,$a_copy_id);
		
		// Clone course learning objectives
		include_once('Modules/Course/classes/class.ilCourseObjective.php');
		$crs_objective = new ilCourseObjective($this);
		$crs_objective->ilClone($a_target_id,$a_copy_id);
		
		include_once('Services/Tracking/classes/class.ilLPCollections.php');
		$lp_collection = new ilLPCollections($this->getId());
		$lp_collection->cloneCollections($a_target_id,$a_copy_id);		
	 	
		// Clone events including assigned materials
		include_once('Modules/Course/classes/Event/class.ilEvent.php');
		ilEvent::_cloneEvent($this->getId(),$ilObjDataCache->lookupObjId($a_target_id),$a_copy_id);

	 	return true;
	}
	
	/**
	 * Clone automatic genrated roles (permissions and template permissions)
	 *
	 * @access public
	 * @param object new course object
	 * 
	 */
	public function cloneAutoGeneratedRoles($new_obj)
	{
		global $ilLog,$rbacadmin,$rbacreview;
		
		$admin = $this->getDefaultAdminRole();
		$new_admin = $new_obj->getDefaultAdminRole();
	 	$source_rolf = $rbacreview->getRoleFolderIdOfObject($this->getRefId());
	 	$target_rolf = $rbacreview->getRoleFolderIdOfObject($new_obj->getRefId());
		
		if(!$admin || !$new_admin || !$source_rolf || !$target_rolf)
		{
			$ilLog->write(__METHOD__.' : Error cloning auto generated role: il_crs_admin');
		}
		$rbacadmin->copyRolePermissions($admin,$source_rolf,$target_rolf,$new_admin,true);
		$ilLog->write(__METHOD__.' : Finished copying of role crs_admin.');
		
		$tutor = $this->getDefaultTutorRole();
		$new_tutor = $new_obj->getDefaultTutorRole();
		if(!$tutor || !$new_tutor)
		{
			$ilLog->write(__METHOD__.' : Error cloning auto generated role: il_crs_tutor');
		}
		$rbacadmin->copyRolePermissions($tutor,$source_rolf,$target_rolf,$new_tutor,true);
		$ilLog->write(__METHOD__.' : Finished copying of role crs_tutor.');
		
		$member = $this->getDefaultMemberRole();
		$new_member = $new_obj->getDefaultMemberRole();
		if(!$member || !$new_member)
		{
			$ilLog->write(__METHOD__.' : Error cloning auto generated role: il_crs_member');
		}
		$rbacadmin->copyRolePermissions($member,$source_rolf,$target_rolf,$new_member,true);
		$ilLog->write(__METHOD__.' : Finished copying of role crs_member.');
		
		return true;
	}
	

	function validate()
	{
		$this->initCourseMemberObject();

		$this->setMessage('');

		#if(($this->getSubscriptionLimitationType() != IL_CRS_SUBSCRIPTION_DEACTIVATED) and
		#   $this->getSubscriptionType() == )
		#{
		#	$this->appendMessage($this->lng->txt('crs_select_registration_type'));
		#}

		if(($this->getActivationType() == IL_CRS_ACTIVATION_LIMITED) and
		   $this->getActivationEnd() < $this->getActivationStart())
		{
			$this->appendMessage($this->lng->txt("activation_times_not_valid"));
		}
		if(($this->getSubscriptionLimitationType() == IL_CRS_SUBSCRIPTION_LIMITED) and
		   $this->getSubscriptionStart() > $this->getSubscriptionEnd())
		{
			$this->appendMessage($this->lng->txt("subscription_times_not_valid"));
		}
		#if((!$this->getActivationUnlimitedStatus() and
		#	!$this->getSubscriptionUnlimitedStatus()) and
		#	($this->getSubscriptionStart() > $this->getActivationEnd() or
		#	 $this->getSubscriptionStart() < $this->getActivationStart() or
		#	 $this->getSubscriptionEnd() > $this->getActivationEnd() or
		#	 $this->getSubscriptionEnd() <  $this->getActivationStart()))
		#   
		#{
		#	$this->appendMessage($this->lng->txt("subscription_time_not_within_activation"));
		#}
		if($this->getSubscriptionType() == IL_CRS_SUBSCRIPTION_PASSWORD and !$this->getSubscriptionPassword())
		{
			$this->appendMessage($this->lng->txt("crs_password_required"));
		}
		if($this->getSubscriptionMaxMembers() and !is_numeric($this->getSubscriptionMaxMembers()))
		{
			$this->appendMessage($this->lng->txt("max_members_not_numeric"));
		}
		if(($this->getViewMode() == IL_CRS_VIEW_ARCHIVE) and
		   $this->getArchiveStart() > $this->getArchiveEnd())
		{
			$this->appendMessage($this->lng->txt("archive_times_not_valid"));
		}
		return $this->getMessage() ? false : true;
	}

	function validateInfoSettings()
	{
		global $ilErr;

		if($this->getContactEmail() and 
		   !(ilUtil::is_email($this->getContactEmail()) or 
			 ilObjUser::getUserIdByLogin($this->getContactEmail())))
		{
			$ilErr->appendMessage($this->lng->txt('contact_email_not_valid'));
			return false;
		}
		return true;
	}

	function hasContactData()
	{
		return strlen($this->getContactName()) or
			strlen($this->getContactResponsibility()) or
			strlen($this->getContactEmail()) or
			strlen($this->getContactPhone()) or
			strlen($this->getContactConsultation());
	}
			

	/**
	* delete course and all related data	
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{
		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}


		// delete meta data
		$this->deleteMetaData();

		// put here course specific stuff

		$this->__deleteSettings();

		$this->initCourseItemObject();
		$this->items_obj->deleteAllEntries();

		include_once('Modules/Course/classes/class.ilCourseParticipants.php');
		ilCourseParticipants::_deleteAllEntries($this->getId());

		$this->initCourseArchiveObject();
		$this->archives_obj->deleteAll();

		include_once './Modules/Course/classes/class.ilCourseObjective.php';
		ilCourseObjective::_deleteAll($this->getId());

		include_once './Modules/Course/classes/class.ilObjCourseGrouping.php';
		ilObjCourseGrouping::_deleteAll($this->getId());

		include_once './Modules/Course/classes/Event/class.ilEvent.php';
		ilEvent::_deleteAll($this->getId());

		include_once './Modules/Course/classes/class.ilCourseFile.php';
		ilCourseFile::_deleteByCourse($this->getId());
		
		include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
		ilCourseDefinedFieldDefinition::_deleteByContainer($this->getId());
		
		return true;
	}



	/**
	* update complete object
	*/
	function update()
	{
		$this->updateMetaData();
		$this->updateSettings();
		parent::update();
	}

	function updateSettings()
	{
		global $ilDB;

		// Due to a bug 3.5.alpha maybe no settings exist. => create default settings

		$query = "SELECT * FROM crs_settings WHERE obj_id = ".$ilDB->quote($this->getId())." ";
		$res = $ilDB->query($query);

		if(!$res->numRows())
		{
			$this->__createDefaultSettings();
		}

		$query = "UPDATE crs_settings SET ".
			"syllabus = ".$ilDB->quote($this->getSyllabus()).", ".
			"contact_name = ".$ilDB->quote($this->getContactName()).", ".
			"contact_responsibility = ".$ilDB->quote($this->getContactResponsibility()).", ".
			"contact_phone = ".$ilDB->quote($this->getContactPhone()).", ".
			"contact_email = ".$ilDB->quote($this->getContactEmail()).", ".
			"contact_consultation = ".$ilDB->quote($this->getContactConsultation()).", ".
			"activation_type = ".$ilDB->quote($this->getActivationType()).", ".
			#"activation_unlimited = '".(int) $this->getActivationUnlimitedStatus()."', ".
			"activation_start = ".$ilDB->quote($this->getActivationStart()).", ".
			"activation_end = ".$ilDB->quote($this->getActivationEnd()).", ".
			#"activation_offline = '".(int) $this->getOfflineStatus()."', ".
			"subscription_limitation_type = ".$ilDB->quote($this->getSubscriptionLimitationType()).", ".
			#"subscription_unlimited = '".(int) $this->getSubscriptionUnlimitedStatus()."', ".
			"subscription_start = ".$ilDB->quote($this->getSubscriptionStart()).", ".
			"subscription_end = ".$ilDB->quote($this->getSubscriptionEnd()).", ".
			"subscription_type = ".$ilDB->quote($this->getSubscriptionType()).", ".
			"subscription_password = ".$ilDB->quote($this->getSubscriptionPassword()).", ".
			"subscription_max_members = ".$ilDB->quote($this->getSubscriptionMaxMembers()).", ".
			"subscription_notify = ".$ilDB->quote($this->getSubscriptionNotify()).", ".
			"view_mode = ".$ilDB->quote($this->getViewMode()).", ".
			"sortorder = ".$ilDB->quote($this->getOrderType()).", ".
			"archive_start = ".$ilDB->quote($this->getArchiveStart()).", ".
			"archive_end = ".$ilDB->quote($this->getArchiveEnd()).", ".
			"archive_type = ".$ilDB->quote($this->getArchiveType()).", ".
			"abo = ".$ilDB->quote($this->getAboStatus()).", ".
			#"objective_view = '".(int) $this->enabledObjectiveView()."', ".
			"waiting_list = ".$ilDB->quote($this->enabledWaitingList()).", ".
			"important = ".$ilDB->quote($this->getImportantInformation()).", ".
			"show_members = ".$ilDB->quote($this->getShowMembers()).", ".
			"latitude = ".$ilDB->quote($this->getLatitude()).", ".
			"longitude = ".$ilDB->quote($this->getLongitude()).", ".
			"location_zoom = ".$ilDB->quote($this->getLocationZoom()).", ".
			"enable_course_map = ".$ilDB->quote($this->getEnableCourseMap())." ".
			"WHERE obj_id = ".$ilDB->quote($this->getId())."";

		$res = $ilDB->query($query);
	}
	
	/**
	 * Clone entries in settings table
	 *
	 * @access public
	 * @param object new course object
	 * 
	 */
	public function cloneSettings($new_obj)
	{
		$new_obj->setSyllabus($this->getSyllabus());
		$new_obj->setContactName($this->getContactName());
		$new_obj->setContactResponsibility($this->getContactResponsibility());
		$new_obj->setContactPhone($this->getContactPhone());
		$new_obj->setContactEmail($this->getContactEmail());
		$new_obj->setContactConsultation($this->getContactConsultation());
		$new_obj->setActivationType($this->getActivationType());
		$new_obj->setActivationStart($this->getActivationStart());
		$new_obj->setActivationEnd($this->getActivationEnd());
		$new_obj->setSubscriptionLimitationType($this->getSubscriptionLimitationType());
		$new_obj->setSubscriptionStart($this->getSubscriptionStart());
		$new_obj->setSubscriptionEnd($this->getSubscriptionEnd());
		$new_obj->setSubscriptionType($this->getSubscriptionType());
		$new_obj->setSubscriptionPassword($this->getSubscriptionPassword());
		$new_obj->setSubscriptionMaxMembers($this->getSubscriptionMaxMembers());
		$new_obj->setSubscriptionNotify($this->getSubscriptionNotify());
		$new_obj->setViewMode($this->getViewMode());
		$new_obj->setOrderType($this->getOrderType());
		$new_obj->setArchiveStart($this->getArchiveStart());
		$new_obj->setArchiveEnd($this->getArchiveEnd());
		$new_obj->setArchiveType($this->getArchiveType());
		$new_obj->setAboStatus($this->getAboStatus());
		$new_obj->enableWaitingList($this->enabledWaitingList());
		$new_obj->setImportantInformation($this->getImportantInformation());
		$new_obj->setShowMembers($this->getShowMembers());
		$new_obj->update();
	}

	function __createDefaultSettings()
	{
		global $ilDB;

		$query = "INSERT INTO crs_settings SET ".
			"obj_id = ".$ilDB->quote($this->getId()).", ".
			"syllabus = ".$ilDB->quote($this->getSyllabus()).", ".
			"contact_name = ".$ilDB->quote($this->getContactName()).", ".
			"contact_responsibility = ".$ilDB->quote($this->getContactResponsibility()).", ".
			"contact_phone = ".$ilDB->quote($this->getContactPhone()).", ".
			"contact_email = ".$ilDB->quote($this->getContactEmail()).", ".
			"contact_consultation = ".$ilDB->quote($this->getContactConsultation()).", ".
			"activation_type = ".$ilDB->quote(IL_CRS_ACTIVATION_UNLIMITED).", ".
			#"activation_unlimited = '1', ".
			"activation_start = ".$ilDB->quote($this->getActivationStart()).", ".
			"activation_end = ".$ilDB->quote($this->getActivationEnd()).", ".
			#"activation_offline = '1', ".
			"subscription_limitation_type = ".$ilDB->quote(IL_CRS_SUBSCRIPTION_DEACTIVATED).", ".
			#"subscription_unlimited = '1', ".
			"subscription_start = ".$ilDB->quote($this->getSubscriptionStart()).", ".
			"subscription_end = ".$ilDB->quote($this->getSubscriptionEnd()).", ".
			"subscription_type = ".$ilDB->quote(IL_CRS_SUBSCRIPTION_DIRECT).", ".
			"subscription_password = ".$ilDB->quote($this->getSubscriptionPassword()).", ".
			"subscription_max_members = ".$ilDB->quote($this->getSubscriptionMaxMembers()).", ".
			"subscription_notify = '1', ".
			"view_mode = '0', ".
			"sortorder = ".$ilDB->quote(IL_CRS_SORT_TITLE).", ".
			"archive_start = ".$ilDB->quote($this->getArchiveStart()).", ".
			"archive_end = ".$ilDB->quote($this->getArchiveEnd()).", ".
			"archive_type = ".$ilDB->quote(IL_CRS_ARCHIVE_NONE).", ".
			"abo = ".$ilDB->quote($this->ABO_ENABLED).", ".
			"latitude = ".$ilDB->quote($this->getLatitude()).", ".
			"longitude = ".$ilDB->quote($this->getLongitude()).", ".
			"location_zoom = ".$ilDB->quote($this->getLocationZoom()).", ".
			"enable_course_map = ".$ilDB->quote($this->getEnableCourseMap()).", ".
			#"objective_view = '0', ".
			"waiting_list = '1', ".
			"show_members = '1'";

		$res = $ilDB->query($query);
	}
	

	function __readSettings()
	{
		global $ilDB;

		$query = "SELECT * FROM crs_settings WHERE obj_id = ".$ilDB->quote($this->getId())."";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setSyllabus($row->syllabus);
			$this->setContactName($row->contact_name);
			$this->setContactResponsibility($row->contact_responsibility);
			$this->setContactPhone($row->contact_phone);
			$this->setContactEmail($row->contact_email);
			$this->setContactConsultation($row->contact_consultation);
			$this->setActivationType($row->activation_type);
			#$this->setActivationUnlimitedStatus($row->activation_unlimited);
			$this->setActivationStart($row->activation_start);
			$this->setActivationEnd($row->activation_end);
			#$this->setOfflineStatus($row->activation_offline);
			$this->setSubscriptionLimitationType($row->subscription_limitation_type);
			#$this->setSubscriptionUnlimitedStatus($row->subscription_unlimited);
			$this->setSubscriptionStart($row->subscription_start);
			$this->setSubscriptionEnd($row->subscription_end);
			$this->setSubscriptionType($row->subscription_type);
			$this->setSubscriptionPassword($row->subscription_password);
			$this->setSubscriptionMaxMembers($row->subscription_max_members);
			$this->setSubscriptionNotify($row->subscription_notify);
			$this->setViewMode($row->view_mode);
			$this->setOrderType($row->sortorder);
			$this->setArchiveStart($row->archive_start);
			$this->setArchiveEnd($row->archive_end);
			$this->setArchiveType($row->archive_type);
			$this->setAboStatus($row->abo);
			$this->enableWaitingList($row->waiting_list);
			$this->setImportantInformation($row->important);
			$this->setShowMembers($row->show_members);
			$this->setLatitude($row->latitude);
			$this->setLongitude($row->longitude);
			$this->setLocationZoom($row->location_zoom);
			$this->setEnableCourseMap($row->enable_course_map);
		}
		return true;
	}

	function initWaitingList()
	{
		include_once "./Modules/Course/classes/class.ilCourseWaitingList.php";

		if(!is_object($this->waiting_list_obj))
		{
			$this->waiting_list_obj =& new ilCourseWaitingList($this->getId());
		}
		return true;
	}
		
		
	function initCourseMemberObject()
	{
		include_once "./Modules/Course/classes/class.ilCourseParticipants.php";
		$this->members_obj = ilCourseParticipants::_getInstanceByObjId($this->getId());
		return true;
	}

	function initCourseItemObject($a_child_id = 0)
	{
		include_once "./Modules/Course/classes/class.ilCourseItems.php";
		
		if(!is_object($this->items_obj))
		{
			$this->items_obj =& new ilCourseItems($this,$a_child_id);
		}
		return true;
	}

	function initCourseArchiveObject()
	{
		include_once "./Modules/Course/classes/class.ilCourseArchives.php";

		if(!is_object($this->archives_obj))
		{
			$this->archives_obj =& new ilCourseArchives($this);
		}
		return true;
	}
		


	// RBAC METHODS
	function initDefaultRoles()
	{
		global $rbacadmin,$rbacreview,$ilDB;

		$rolf_obj = $this->createRoleFolder();

		// CREATE ADMIN ROLE
		$role_obj = $rolf_obj->createRole("il_crs_admin_".$this->getRefId(),"Admin of course obj_no.".$this->getId());
		$admin_id = $role_obj->getId();
		
		// SET PERMISSION TEMPLATE OF NEW LOCAL ADMIN ROLE
		$query = "SELECT obj_id FROM object_data ".
			" WHERE type='rolt' AND title='il_crs_admin'";

		$res = $this->ilias->db->getRow($query, DB_FETCHMODE_OBJECT);
		$rbacadmin->copyRoleTemplatePermissions($res->obj_id,ROLE_FOLDER_ID,$rolf_obj->getRefId(),$role_obj->getId());

		// SET OBJECT PERMISSIONS OF COURSE OBJECT
		$ops = $rbacreview->getOperationsOfRole($role_obj->getId(),"crs",$rolf_obj->getRefId());
		$rbacadmin->grantPermission($role_obj->getId(),$ops,$this->getRefId());

		// SET OBJECT PERMISSIONS OF ROLE FOLDER OBJECT
		//$ops = $rbacreview->getOperationsOfRole($role_obj->getId(),"rolf",$rolf_obj->getRefId());
		//$rbacadmin->grantPermission($role_obj->getId(),$ops,$rolf_obj->getRefId());

		// CREATE TUTOR ROLE
		// CREATE ROLE AND ASSIGN ROLE TO ROLEFOLDER...
		$role_obj = $rolf_obj->createRole("il_crs_tutor_".$this->getRefId(),"Tutors of course obj_no.".$this->getId());
		$member_id = $role_obj->getId();

		// SET PERMISSION TEMPLATE OF NEW LOCAL ROLE
		$query = "SELECT obj_id FROM object_data ".
			" WHERE type='rolt' AND title='il_crs_tutor'";
		$res = $this->ilias->db->getRow($query, DB_FETCHMODE_OBJECT);
		$rbacadmin->copyRoleTemplatePermissions($res->obj_id,ROLE_FOLDER_ID,$rolf_obj->getRefId(),$role_obj->getId());

		// SET OBJECT PERMISSIONS OF COURSE OBJECT
		$ops = $rbacreview->getOperationsOfRole($role_obj->getId(),"crs",$rolf_obj->getRefId());
		$rbacadmin->grantPermission($role_obj->getId(),$ops,$this->getRefId());

		// SET OBJECT PERMISSIONS OF ROLE FOLDER OBJECT
		//$ops = $rbacreview->getOperationsOfRole($role_obj->getId(),"rolf",$rolf_obj->getRefId());
		//$rbacadmin->grantPermission($role_obj->getId(),$ops,$rolf_obj->getRefId());

		// CREATE MEMBER ROLE
		// CREATE ROLE AND ASSIGN ROLE TO ROLEFOLDER...
		$role_obj = $rolf_obj->createRole("il_crs_member_".$this->getRefId(),"Member of course obj_no.".$this->getId());
		$member_id = $role_obj->getId();

		// SET PERMISSION TEMPLATE OF NEW LOCAL ROLE
		$query = "SELECT obj_id FROM object_data ".
			" WHERE type='rolt' AND title='il_crs_member'";
		$res = $this->ilias->db->getRow($query, DB_FETCHMODE_OBJECT);
		$rbacadmin->copyRoleTemplatePermissions($res->obj_id,ROLE_FOLDER_ID,$rolf_obj->getRefId(),$role_obj->getId());
		
		// SET OBJECT PERMISSIONS OF COURSE OBJECT
		$ops = $rbacreview->getOperationsOfRole($role_obj->getId(),"crs",$rolf_obj->getRefId());
		$rbacadmin->grantPermission($role_obj->getId(),$ops,$this->getRefId());

		// SET OBJECT PERMISSIONS OF ROLE FOLDER OBJECT
		//$ops = $rbacreview->getOperationsOfRole($role_obj->getId(),"rolf",$rolf_obj->getRefId());
		//$rbacadmin->grantPermission($role_obj->getId(),$ops,$rolf_obj->getRefId());

		unset($role_obj);
		unset($rolf_obj);

		// Break inheritance, create local roles and initialize permission
		// settings depending on course status.
		$this->__setCourseStatus();

		return true;
	}

	/**
	* set course status
	*
	* Grants permissions on the course object for all parent roles.  
	* Each permission is granted by computing the intersection of the role 
	* template il_crs_non_member and the permission template of 
	* the parent role.
	*
	* Creates linked roles in the local role folder object for all 
	* parent roles and initializes their permission templates.
	* Each permission template is initialized by computing the intersection 
	* of the role template il_crs_non_member and the permission
	* template of the parent role.
	*
	* @access	private
	*/
	function __setCourseStatus()
	{
		global $rbacadmin, $rbacreview, $rbacsystem;

		//get Rolefolder of course
		$rolf_data = $rbacreview->getRoleFolderOfObject($this->getRefId());

		//define all relevant roles for which rights are needed to be changed
		$arr_parentRoles = $rbacreview->getParentRoleIds($this->getRefId());
		$arr_relevantParentRoleIds = array_diff(array_keys($arr_parentRoles),$this->getDefaultCourseRoles());

		$template_id = $this->__getCrsNonMemberTemplateId();

		//get defined operations from template
		if (is_null($template_id))
		{
			$template_ops = array();
		} else {
			$template_ops = $rbacreview->getOperationsOfRole($template_id, 'crs', ROLE_FOLDER_ID);
		}

		foreach ($arr_relevantParentRoleIds as $parentRole)
		{
			if ($rbacreview->isProtected($arr_parentRoles[$parentRole]['parent'],$parentRole))
			{
				continue;
			}
				
			$granted_permissions = array();

			// Delete the linked role for the parent role
			// (just in case if it already exists).
			$rbacadmin->deleteLocalRole($parentRole,$rolf_data["child"]);

			// Grant permissions on the course object for 
			// the parent role. In the foreach loop we
			// compute the intersection of the role     
			// template il_crs_non_member and the 
			// permission template of the parent role.
			$current_ops = $rbacreview->getRoleOperationsOnObject($parentRole, $this->getRefId());
			$rbacadmin->revokePermission($this->getRefId(), $parentRole);
			foreach ($template_ops as $template_op) 
			{
				if (in_array($template_op,$current_ops)) 
				{
					array_push($granted_permissions,$template_op);
				}
			}
			if (!empty($granted_permissions))
			{
				$rbacadmin->grantPermission($parentRole, $granted_permissions, $this->getRefId());
			}

			// Create a linked role for the parent role and
			// initialize it with the intersection of 
			// il_crs_non_member and the permission
			// template of the parent role
			if (! is_null($template_id))
			{
				$rbacadmin->copyRolePermissionIntersection(
					$template_id, ROLE_FOLDER_ID, 
					$parentRole, $arr_parentRoles[$parentRole]['parent'], 
					$rolf_data["child"], $parentRole
				);
			}
			$rbacadmin->assignRoleToFolder($parentRole,$rolf_data["child"],"false");
		}//END foreach
	}

	/**
	* get course non-member template
	* @access	private
	* @param	return obj_id of roletemplate containing permissionsettings for 
	*           non-member roles of a course.
	*/
	function __getCrsNonMemberTemplateId()
	{
		global $ilDB;
		
		$q = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='il_crs_non_member'";
		$res = $this->ilias->db->query($q);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

		return $row["obj_id"];
	}


	/**
	* get default course roles, returns the defaultlike create roles 
	* il_crs_tutor, il_crs_admin and il_crs_member
	* @access	public
	* @param 	returns the obj_ids of course specific roles in an associative
    *           array.
	*			key=descripiton of the role (i.e. "il_crs_tutor", "il_crs_admin", "il_crs_member".
	*			value=obj_id of the role
	*/
	public function getDefaultCourseRoles($a_crs_id = "")
	{
		global $rbacadmin, $rbacreview;

		if (strlen($a_crs_id) > 0)
		{
			$crs_id = $a_crs_id;
		}
		else
		{
			$crs_id = $this->getRefId();
		}

		$rolf 	   = $rbacreview->getRoleFolderOfObject($crs_id);
		$role_arr  = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"]);

		foreach ($role_arr as $role_id)
		{
			$role_Obj =& $this->ilias->obj_factory->getInstanceByObjId($role_id);

			$crs_Member ="il_crs_member_".$crs_id;
			$crs_Admin  ="il_crs_admin_".$crs_id;
			$crs_Tutor  ="il_crs_tutor_".$crs_id;

			if (strcmp($role_Obj->getTitle(), $crs_Member) == 0 )
			{
				$arr_crsDefaultRoles["crs_member_role"] = $role_Obj->getId();
			}

			if (strcmp($role_Obj->getTitle(), $crs_Admin) == 0)
			{
				$arr_crsDefaultRoles["crs_admin_role"] = $role_Obj->getId();
			}

			if (strcmp($role_Obj->getTitle(), $crs_Tutor) == 0)
			{
				$arr_crsDefaultRoles["crs_tutor_role"] = $role_Obj->getId();
			}
		}

		return $arr_crsDefaultRoles;
	}

	function __getLocalRoles()
	{
		global $rbacreview;

		// GET role_objects of predefined roles
		
		$rolf = $rbacreview->getRoleFolderOfObject($this->getRefId());

		return $rbacreview->getRolesOfRoleFolder($rolf["ref_id"],false);
	}

	function __deleteSettings()
	{
		global $ilDB;
		
		$query = "DELETE FROM crs_settings ".
			"WHERE obj_id = ".$ilDB->quote($this->getId())." ";

		$this->ilias->db->query($query);

		return true;
	}	

	function getDefaultMemberRole()
	{
		$local_roles = $this->__getLocalRoles();

		foreach($local_roles as $role_id)
		{
			if($tmp_role =& ilObjectFactory::getInstanceByObjId($role_id,false))
			{
				if(!strcmp($tmp_role->getTitle(),"il_crs_member_".$this->getRefId()))
				{
					return $role_id;
				}
			}
		}
		return false;
	}
	function getDefaultTutorRole()
	{
		$local_roles = $this->__getLocalRoles();

		foreach($local_roles as $role_id)
		{
			if($tmp_role =& ilObjectFactory::getInstanceByObjId($role_id,false))
			{
				if(!strcmp($tmp_role->getTitle(),"il_crs_tutor_".$this->getRefId()))
				{
					return $role_id;
				}
			}
		}
		return false;
	}
	function getDefaultAdminRole()
	{
		$local_roles = $this->__getLocalRoles();

		foreach($local_roles as $role_id)
		{
			if($tmp_role =& ilObjectFactory::getInstanceByObjId($role_id,false))
			{
				if(!strcmp($tmp_role->getTitle(),"il_crs_admin_".$this->getRefId()))
				{
					return $role_id;
				}
			}
		}
		return false;
	}

	// static method for condition handler
	function _checkCondition($a_obj_id,$a_operator,$a_value)
	{
		global $ilUser;

		include_once "./Modules/Course/classes/class.ilCourseParticipants.php";
		
		switch($a_operator)
		{
			case 'passed':
				return ilCourseParticipants::_hasPassed($a_obj_id,$ilUser->getId());
				
			default:
				return true;
		}
	}

	function _deleteUser($a_usr_id)
	{
		// Delete all user related data
		// delete lm_history
		include_once './Modules/Course/classes/class.ilCourseLMHistory.php';
		ilCourseLMHistory::_deleteUser($a_usr_id);

		include_once './Modules/Course/classes/class.ilCourseParticipants.php';
		ilCourseParticipants::_deleteUser($a_usr_id);

		// Course objectives
		include_once './Modules/Course/classes/class.ilCourseObjectiveResult.php';
		ilCourseObjectiveResult::_deleteUser($a_usr_id);
	}

} //END class.ilObjCourse
?>
