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
* @extends Object
* @package ilias-core
*/

require_once "./classes/class.ilObject.php";

class ilObjCourse extends ilObject
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

	// SET/GET
	function setId($a_id)
	{
		parent::setId($a_id);

//		unset($this->meta_data);
		#$this->__initMetaObject();
	}


	function getDescription()
	{
		return parent::getDescription();
	}
	function setDescription($a_description)
	{
		parent::setDescription($a_description);

//		$this->__initMetaObject();
//		$this->meta_data->setDescription($a_description);

		return true;
	}
	function getTitle()
	{
		return parent::getTitle();
	}
	function setTitle($a_title)
	{
		parent::setTitle($a_title);
//		$this->__initMetaObject();
//		$this->meta_data->setTitle($a_title);
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
	function getcontactEmail()
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
	function getActivationUnlimitedStatus()
	{
		return $this->activation_unlimited ? true : false;
	} 
	function setActivationUnlimitedStatus($a_unlimited)
	{
		$this->activation_unlimited = (bool) $a_unlimited;
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
		return $this->offline_status ? true : false;
	}
	function setOfflineStatus($a_value)
	{
		$this->offline_status = $a_value ? true : false;
	}
	function getSubscriptionUnlimitedStatus()
	{
		return $this->subscription_unlimited ? true : false;
	} 
	function setSubscriptionUnlimitedStatus($a_unlimited)
	{
		$this->subscription_unlimited = (bool) $a_unlimited;
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
	function getSubscriptionType()
	{
		return $this->subscription_type ? $this->subscription_type : $this->SUBSCRIPTION_DEACTIVATED;
	}
	function setSubscriptionType($a_value)
	{
		$this->subscription_type = $a_value;
	}
	function setSubscriptionEnd($a_value)
	{
		$this->subscription_end = $a_value;
	}
	function getSubscriptionPassword()
	{
		return $this->subscription_password;
	}
	function setSubscriptionPassword($a_value)
	{
		$this->subscription_password = $a_value;
	}
	function setObjectiveViewStatus($a_status)
	{
		$this->objective_view = $a_status;
	}
	function enabledObjectiveView()
	{
		return (bool) $this->objective_view;
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
	function getSubscriptionNotify()
	{
		return $this->subscription_notify ? true : false;
	}
	function setSubscriptionNotify($a_value)
	{
		$this->subscription_notify = $a_value ? true : false;
	}
	function getOrderType()
	{
		return $this->order_type ? $this->order_type : $this->SORT_TITLE;
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
		return $this->archive_type ? $this->archive_type : $this->ARCHIVE_DISABLED;
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

	function isArchived()
	{
		if($this->getArchiveType() == $this->ARCHIVE_DISABLED)
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
		$this->createMetaData();
/*
		if (!$a_upload)
		{
			$this->__initMetaObject();
			$this->meta_data->setId($this->getId());
			$this->meta_data->setType($this->getType());
			$this->meta_data->setTitle($this->getTitle());
			$this->meta_data->setDescription($this->getDescription());
			$this->meta_data->setObject($this);
			$this->meta_data->create();

			$this->__createDefaultSettings();
		}
*/
	}

	function validate()
	{
		$this->initCourseMemberObject();

		$this->setMessage('');

		if(!$this->getSubscriptionType())
		{
			$this->appendMessage($this->lng->txt('crs_select_registration_type'));
		}

		if(!$this->getActivationUnlimitedStatus() and $this->getActivationEnd() < $this->getActivationStart())
		{
			$this->appendMessage($this->lng->txt("activation_times_not_valid"));
		}
		if(!$this->getSubscriptionUnlimitedStatus() and $this->getSubscriptionStart() > $this->getSubscriptionEnd())
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
		if($this->getSubscriptionType() == $this->SUBSCRIPTION_PASSWORD and !$this->getSubscriptionPassword())
		{
			$this->appendMessage($this->lng->txt("password_required"));
		}
		if($this->getSubscriptionMaxMembers() and !is_numeric($this->getSubscriptionMaxMembers()))
		{
			$this->appendMessage($this->lng->txt("max_members_not_numeric"));
		}
		if($this->getSubscriptionMaxMembers() and
			$this->getSubscriptionMaxMembers() < $this->members_obj->getCountMembers())
		{
			$this->appendMessage($this->lng->txt("crs_max_members_smaller_members"));
		}
		if($this->getArchiveStart() > $this->getArchiveEnd())
		{
			$this->appendMessage($this->lng->txt("archive_times_not_valid"));
		}
		if($this->getContactEmail() and 
		   !(ilUtil::is_email($this->getContactEmail()) or 
			 ilObjUser::getUserIdByLogin($this->getContactEmail())))
		{
			$this->appendMessage($this->lng->txt("contact_email_not_valid"));
		}
		return $this->getMessage() ? false : true;
	}
			
	/**
	* copy all properties and subobjects of a course.
	* 
	* @access	public
	* @return	integer	new ref id
	*/
	function ilClone($a_parent_ref)
	{		
		global $rbacadmin;

		// always call parent ilClone function first!!
		$new_ref_id = parent::ilClone($a_parent_ref);
		
		// put here crs specific stuff
		$new_course =& ilObjectFactory::getInstanceByRefId($new_ref_id);

		$new_course->__initDefaultRoles();
		$new_course->initCourseMemberObject();
		$new_course->members_obj->add($this->ilias->account,$new_course->members_obj->ROLE_ADMIN);
		$new_course->__createDefaultSettings();


		$new_course->setSyllabus($this->getSyllabus());
		$new_course->setContactName($this->getContactName());
		$new_course->setContactConsultation($this->getContactConsultation());
		$new_course->setContactPhone($this->getContactPhone());
		$new_course->setContactEmail($this->getContactEmail());
		$new_course->setContactResponsibility($this->getContactResponsibility());

		$new_course->setActivationUnlimitedStatus($this->getActivationUnlimitedStatus());
		$new_course->setActivationStart($this->getActivationStart());
		$new_course->setActivationEnd($this->getActivationEnd());
		$new_course->setOfflineStatus($this->getOfflineStatus());

		$new_course->setSubscriptionUnlimitedStatus($this->getSubscriptionUnlimitedStatus());
		$new_course->setSubscriptionStart($this->getSubscriptionStart());
		$new_course->setSubscriptionEnd($this->getSubscriptionEnd());
		$new_course->setSubscriptionType($this->getSubscriptionType());
		$new_course->setSubscriptionPassword($this->getSubscriptionPassword());
		$new_course->setSubscriptionMaxMembers($this->getSubscriptionMaxMembers());
		$new_course->setSubscriptionNotify($this->getSubscriptionNotify());
		$new_course->setOrderType($this->getOrderType());
		$new_course->setArchiveStart($this->getArchiveStart());
		$new_course->setArchiveEnd($this->getArchiveEnd());
		$new_course->setArchiveType($this->getArchiveType());
		$new_course->setAboStatus($this->getAboStatus());
		$new_course->setObjectiveViewStatus($this->enabledObjectiveView());

		$new_course->update();

		// ... and finally always return new reference ID!!
		return $new_ref_id;
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

		$this->initCourseMemberObject();
		$this->members_obj->deleteAllEntries();

		$this->initCourseArchiveObject();
		$this->archives_obj->deleteAll();

		include_once './course/classes/class.ilCourseObjective.php';

		ilCourseObjective::_deleteAll($this->getId());

		include_once './course/classes/class.ilObjCourseGrouping.php';

		ilObjCourseGrouping::_deleteAll($this->getId());

		return true;
	}

	/**
	* notifys an object about an event occured
	* Based on the event happend, each object may decide how it reacts.
	* 
	* @access	public
	* @param	string	event
	* @param	integer	reference id of object where the event occured
	* @param	array	passes optional paramters if required
	* @return	boolean
	*/
	function notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params = 0)
	{
		global $tree;
		
		switch ($a_event)
		{
			case "link":
				
				break;
			
			case "cut":
				
				break;
				
			case "copy":
			
				break;

			case "paste":
				
				break;
			
			case "new":
				
				break;
		}
		
		// At the beginning of the recursive process it avoids second call of the notify function with the same parameter
		if ($a_node_id==$_GET["ref_id"])
		{	
			$parent_obj =& $this->ilias->obj_factory->getInstanceByRefId($a_node_id);
			$parent_type = $parent_obj->getType();
			if($parent_type == $this->getType())
			{
				$a_node_id = (int) $tree->getParentId($a_node_id);
			}
		}
		
		parent::notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params);
	}

	// META DATA METHODS
/*
	function &getMetaData()
	{
		// CALLED BY META DATA GUI

		$this->__initMetaObject();

		return $this->meta_data;
	}
*/

	/**
	* update meta data only
	*/
/*
	function updateMetaData()
	{
		$this->__initMetaObject();

		$this->meta_data->update();
		if ($this->meta_data->section != "General")
		{
			$meta = $this->meta_data->getElement("Title", "General");
			$this->meta_data->setTitle($meta[0]["value"]);
			$meta = $this->meta_data->getElement("Description", "General");
			$this->meta_data->setDescription($meta[0]["value"]);
		}
		else
		{
			$this->setTitle($this->meta_data->getTitle());
			$this->setDescription($this->meta_data->getDescription());
		}
		parent::update();
	}
*/

	/**
	* update complete object
	*/
	function update()
	{
		$this->updateMetaData();
		$this->__updateSettings();
		parent::update();
	}

/*
	function __initMetaObject()
	{
		include_once "./classes/class.ilMetaData.php";

		if(is_object($this->meta_data))
		{
			return true;
		}

		if($this->getId())
		{
			$this->meta_data =& new ilMetaData($this->getType(),$this->getId());
		}
		else
		{
			$this->meta_data =& new ilMetaData();
		}

		return true;
	}
*/

	function __updateSettings()
	{
		global $ilDB;

		$query = "UPDATE crs_settings SET ".
			"syllabus = '".ilUtil::prepareDBString($this->getSyllabus())."', ".
			"contact_name = '".ilUtil::prepareDBString($this->getContactName())."', ".
			"contact_responsibility = '".ilUtil::prepareDBString($this->getContactResponsibility())."', ".
			"contact_phone = '".ilUtil::prepareDBString($this->getContactPhone())."', ".
			"contact_email = '".ilUtil::prepareDBString($this->getContactEmail())."', ".
			"contact_consultation = '".ilUtil::prepareDBString($this->getContactConsultation())."', ".
			"activation_unlimited = '".(int) $this->getActivationUnlimitedStatus()."', ".
			"activation_start = '".$this->getActivationStart()."', ".
			"activation_end = '".$this->getActivationEnd()."', ".
			"activation_offline = '".(int) $this->getOfflineStatus()."', ".
			"subscription_unlimited = '".(int) $this->getSubscriptionUnlimitedStatus()."', ".
			"subscription_start = '".$this->getSubscriptionStart()."', ".
			"subscription_end = '".$this->getSubscriptionEnd()."', ".
			"subscription_type = '".(int) $this->getSubscriptionType()."', ".
			"subscription_password = '".ilUtil::prepareDBString($this->getSubscriptionPassword())."', ".
			"subscription_max_members = '".(int) $this->getSubscriptionMaxMembers()."', ".
			"subscription_notify = '".(int) $this->getSubscriptionNotify()."', ".
			"sortorder = '".(int) $this->getOrderType()."', ".
			"archive_start = '".$this->getArchiveStart()."', ".
			"archive_end = '".$this->getArchiveEnd()."', ".
			"archive_type = '".(int) $this->getArchiveType()."', ".
			"abo = '".(int) $this->getAboStatus()."', ".
			"objective_view = '".(int) $this->enabledObjectiveView()."' ".
			"WHERE obj_id = '".$this->getId()."'";

		$res = $ilDB->query($query);
	}

	function __createDefaultSettings()
	{
		global $ilDB;

		$query = "INSERT INTO crs_settings SET ".
			"obj_id = '".$this->getId()."', ".
			"syllabus = '".ilUtil::prepareDBString($this->getSyllabus())."', ".
			"contact_name = '".ilUtil::prepareDBString($this->getContactName())."', ".
			"contact_responsibility = '".ilUtil::prepareDBString($this->getContactResponsibility())."', ".
			"contact_phone = '".ilUtil::prepareDBString($this->getContactPhone())."', ".
			"contact_email = '".ilUtil::prepareDBString($this->getContactEmail())."', ".
			"contact_consultation = '".ilUtil::prepareDBString($this->getContactConsultation())."', ".
			"activation_unlimited = '1', ".
			"activation_start = '".$this->getActivationStart()."', ".
			"activation_end = '".$this->getActivationEnd()."', ".
			"activation_offline = '1', ".
			"subscription_unlimited = '1', ".
			"subscription_start = '".$this->getSubscriptionStart()."', ".
			"subscription_end = '".$this->getSubscriptionEnd()."', ".
			"subscription_type = '".(int) $this->SUBSCRIPTION_DEACTIVATED."', ".
			"subscription_password = '".ilUtil::prepareDBString($this->getSubscriptionPassword())."', ".
			"subscription_max_members = '".(int) $this->getSubscriptionMaxMembers()."', ".
			"subscription_notify = '1', ".
			"sortorder = '".(int) $this->SORT_MANUAL."', ".
			"archive_start = '".$this->getArchiveStart()."', ".
			"archive_end = '".$this->getArchiveEnd()."', ".
			"archive_type = '".(int) $this->ARCHIVE_DISABLED."', ".
			"abo = '".(int) $this->ABO_ENABLED."', ".
			"objective_view = '0'";

		$res = $ilDB->query($query);
	}
	

	function __readSettings()
	{
		global $ilDB;

		$query = "SELECT * FROM crs_settings WHERE obj_id = '".$this->getId()."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setSyllabus($row->syllabus);
			$this->setContactName($row->contact_name);
			$this->setContactResponsibility($row->contact_responsibility);
			$this->setContactPhone($row->contact_phone);
			$this->setContactEmail($row->contact_email);
			$this->setContactConsultation($row->contact_consultation);
			$this->setActivationUnlimitedStatus($row->activation_unlimited);
			$this->setActivationStart($row->activation_start);
			$this->setActivationEnd($row->activation_end);
			$this->setOfflineStatus($row->activation_offline);
			$this->setSubscriptionUnlimitedStatus($row->subscription_unlimited);
			$this->setSubscriptionStart($row->subscription_start);
			$this->setSubscriptionEnd($row->subscription_end);
			$this->setSubscriptionType($row->subscription_type);
			$this->setSubscriptionPassword($row->subscription_password);
			$this->setSubscriptionMaxMembers($row->subscription_max_members);
			$this->setSubscriptionNotify($row->subscription_notify);
			$this->setOrderType($row->sortorder);
			$this->setArchiveStart($row->archive_start);
			$this->setArchiveEnd($row->archive_end);
			$this->setArchiveType($row->archive_type);
			$this->setAboStatus($row->abo);
			$this->setObjectiveViewStatus($row->objective_view);
		}
		return true;
	}

	function initCourseMemberObject()
	{
		include_once "./course/classes/class.ilCourseMembers.php";

		if(!is_object($this->members_obj))
		{
			$this->members_obj =& new ilCourseMembers($this);
		}
		return true;
	}

	function initCourseItemObject($a_child_id = 0)
	{
		include_once "./course/classes/class.ilCourseItems.php";
		
		if(!is_object($this->items_obj))
		{
			$this->items_obj =& new ilCourseItems($this,$a_child_id);
		}
		return true;
	}

	function initCourseArchiveObject()
	{
		include_once "./course/classes/class.ilCourseArchives.php";

		if(!is_object($this->archives_obj))
		{
			$this->archives_obj =& new ilCourseArchives($this);
		}
		return true;
	}
		


	// RBAC METHODS
	function __initDefaultRoles()
	{
		global $rbacadmin,$rbacreview;

		$rolf_obj = $this->createRoleFolder();

		// CREATE ADMIN ROLE
		$role_obj = $rolf_obj->createRole("il_crs_admin_".$this->getRefId(),"Admin of course obj_no.".$this->getId());
		$admin_id = $role_obj->getId();
		
		// SET PERMISSION TEMPLATE OF NEW LOCAL ADMIN ROLE
		$query = "SELECT obj_id FROM object_data ".
			" WHERE type='rolt' AND title='il_crs_admin'";

		$res = $this->ilias->db->getRow($query, DB_FETCHMODE_OBJECT);
		$rbacadmin->copyRolePermission($res->obj_id,ROLE_FOLDER_ID,$rolf_obj->getRefId(),$role_obj->getId());

		// SET OBJECT PERMISSIONS OF COURSE OBJECT
		$ops = $rbacreview->getOperationsOfRole($role_obj->getId(),"crs",$rolf_obj->getRefId());
		$rbacadmin->grantPermission($role_obj->getId(),$ops,$this->getRefId());

		// SET OBJECT PERMISSIONS OF ROLE FOLDER OBJECT
		$ops = $rbacreview->getOperationsOfRole($role_obj->getId(),"rolf",$rolf_obj->getRefId());
		$rbacadmin->grantPermission($role_obj->getId(),$ops,$rolf_obj->getRefId());

		// CREATE TUTOR ROLE
		// CREATE ROLE AND ASSIGN ROLE TO ROLEFOLDER...
		$role_obj = $rolf_obj->createRole("il_crs_tutor_".$this->getRefId(),"Tutors of course obj_no.".$this->getId());
		$member_id = $role_obj->getId();

		// SET PERMISSION TEMPLATE OF NEW LOCAL ROLE
		$query = "SELECT obj_id FROM object_data ".
			" WHERE type='rolt' AND title='il_crs_tutor'";
		$res = $this->ilias->db->getRow($query, DB_FETCHMODE_OBJECT);
		$rbacadmin->copyRolePermission($res->obj_id,ROLE_FOLDER_ID,$rolf_obj->getRefId(),$role_obj->getId());

		// SET OBJECT PERMISSIONS OF COURSE OBJECT
		$ops = $rbacreview->getOperationsOfRole($role_obj->getId(),"crs",$rolf_obj->getRefId());
		$rbacadmin->grantPermission($role_obj->getId(),$ops,$this->getRefId());

		// SET OBJECT PERMISSIONS OF ROLE FOLDER OBJECT
		$ops = $rbacreview->getOperationsOfRole($role_obj->getId(),"rolf",$rolf_obj->getRefId());
		$rbacadmin->grantPermission($role_obj->getId(),$ops,$rolf_obj->getRefId());

		// CREATE MEMBER ROLE
		// CREATE ROLE AND ASSIGN ROLE TO ROLEFOLDER...
		$role_obj = $rolf_obj->createRole("il_crs_member_".$this->getRefId(),"Member of course obj_no.".$this->getId());
		$member_id = $role_obj->getId();

		// SET PERMISSION TEMPLATE OF NEW LOCAL ROLE
		$query = "SELECT obj_id FROM object_data ".
			" WHERE type='rolt' AND title='il_crs_member'";
		$res = $this->ilias->db->getRow($query, DB_FETCHMODE_OBJECT);
		$rbacadmin->copyRolePermission($res->obj_id,ROLE_FOLDER_ID,$rolf_obj->getRefId(),$role_obj->getId());
		
		// SET OBJECT PERMISSIONS OF COURSE OBJECT
		$ops = $rbacreview->getOperationsOfRole($role_obj->getId(),"crs",$rolf_obj->getRefId());
		$rbacadmin->grantPermission($role_obj->getId(),$ops,$this->getRefId());

		// SET OBJECT PERMISSIONS OF ROLE FOLDER OBJECT
		$ops = $rbacreview->getOperationsOfRole($role_obj->getId(),"rolf",$rolf_obj->getRefId());
		$rbacadmin->grantPermission($role_obj->getId(),$ops,$rolf_obj->getRefId());

		unset($role_obj);
		unset($rolf_obj);

		return true;
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
		$query = "DELETE FROM crs_settings ".
			"WHERE obj_id = '".$this->getId()."'";

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
		global $ilias;

		include_once "./course/classes/class.ilCourseMembers.php";
		
		switch($a_operator)
		{
			case 'passed':
				return ilCourseMembers::_hasPassed($a_obj_id,$ilias->account->getId());
				
			default:
				return true;
		}
	}
} //END class.ilObjCourse
?>
