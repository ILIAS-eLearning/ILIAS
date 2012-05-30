<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectListGUI.php";

/**
 * Class ilObjCourseListGUI
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * $Id$
 *
 * @ingroup ModulesCourse
 */
class ilObjCourseListGUI extends ilObjectListGUI
{
	/**
	* constructor
	*
	*/
	function ilObjCourseListGUI()
	{
		$this->ilObjectListGUI();
	}

	/**
	* initialisation
	*/
	function init()
	{
		$this->static_link_enabled = true;
		$this->delete_enabled = true;
		$this->cut_enabled = true;
		$this->copy_enabled = true;
		$this->subscribe_enabled = true;
		$this->link_enabled = false;
		$this->payment_enabled = true;
		$this->info_screen_enabled = true;
		$this->type = "crs";
		$this->gui_class_name = "ilobjcoursegui";
		
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDSubstitution.php');
		$this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
		if($this->substitutions->isActive())
		{
			$this->substitutions_enabled = true;
		}

		// general commands array
		include_once('Modules/Course/classes/class.ilObjCourseAccess.php');
		$this->commands = ilObjCourseAccess::_getCommands();
	}
	
	/**
	 * Only check cmd access for cmd 'register' and 'unregister'
	 * @param string $a_permission
	 * @param object $a_cmd
	 * @param object $a_ref_id
	 * @param object $a_type
	 * @param object $a_obj_id [optional]
	 * @return 
	 */
	public function checkCommandAccess($a_permission,$a_cmd,$a_ref_id,$a_type,$a_obj_id="")
	{
		if($a_cmd != 'view' and $a_cmd != 'leave')
		{
			$a_cmd = '';
		}
		return parent::checkCommandAccess($a_permission,$a_cmd,$a_ref_id,$a_type,$a_obj_id);
	}
	

	/**
	* inititialize new item
	*
	* @param	int			$a_ref_id		reference id
	* @param	int			$a_obj_id		object id
	* @param	string		$a_title		title
	* @param	string		$a_description	description
	*/
	function initItem($a_ref_id, $a_obj_id, $a_title = "", $a_description = "")
	{
		global $ilBench;

		parent::initItem($a_ref_id, $a_obj_id, $a_title, $a_description);

		$this->conditions_ok = ilConditionHandler::_checkAllConditionsOfTarget($a_ref_id,$this->obj_id);
	}


	/**
	* Get item properties
	*
	* @return	array		array of property arrays:
	*						"alert" (boolean) => display as an alert property (usually in red)
	*						"property" (string) => property name
	*						"value" (string) => property value
	*/
	function getProperties()
	{
		global $lng, $ilUser;

		// BEGIN WebDAV: Get parent properties
		// BEGIN ChangeEvent: Get parent properties
		$props = parent::getProperties();
		// END ChangeEvent: Get parent properties
		// END WebDAV: Get parent properties

		// offline
		include_once 'Modules/Course/classes/class.ilObjCourseAccess.php';
		if(ilObjCourseAccess::_isOffline($this->obj_id))
		{
			$props[] = array("alert" => true, "property" => $lng->txt("status"),
				"value" => $lng->txt("offline"));
		}

		// blocked
		include_once 'Modules/Course/classes/class.ilCourseParticipant.php';
		$members = ilCourseParticipant::_getInstanceByObjId($this->obj_id,$ilUser->getId());
		if($members->isBlocked($ilUser->getId()) and $members->isAssigned($ilUser->getId()))
		{
			$props[] = array("alert" => true, "property" => $lng->txt("member_status"),
				"value" => $lng->txt("crs_status_blocked"));
		}

		// pending subscription
		include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
		if (ilCourseParticipants::_isSubscriber($this->obj_id,$ilUser->getId()))
		{
			$props[] = array("alert" => true, "property" => $lng->txt("member_status"),
				"value" => $lng->txt("crs_status_pending"));
		}
		// waiting list
		include_once './Modules/Course/classes/class.ilCourseWaitingList.php';
		if(ilCourseWaitingList::_isOnList($ilUser->getId(),$this->obj_id))
		{
			$props[] = array(
				"alert" 	=> true,
				"property" 	=> $lng->txt('member_status'),
				"value"		=> $lng->txt('on_waiting_list')
			);
		}
		
		// check for certificates	
		include_once "./Modules/Course/classes/class.ilCourseCertificateAdapter.php";
		if(ilCourseCertificateAdapter::_hasUserCertificate($ilUser->getId(), $this->obj_id))
		{
			$lng->loadLanguageModule('certificate');
			$cmd_link = "repository.php?ref_id=".$this->ref_id.
					"&amp;cmd=deliverCertificate";
			$props[] = array("alert" => false, "property" => $lng->txt("passed"),
				"value" => '<a href="' . $cmd_link . '">' . $lng->txt("download_certificate") . '</a>');
		}

		return $props;
	}
} // END class.ilObjCategoryGUI
?>
