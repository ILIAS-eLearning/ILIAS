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
* Class ilObjCourseListGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @extends ilObjectListGUI
*/


include_once "classes/class.ilObjectListGUI.php";

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
		include_once('class.ilObjCourseAccess.php');
		$this->commands = ilObjCourseAccess::_getCommands();
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

		// to do: get rid of all this...

//echo "A-".memory_get_usage();echo "-".$full_class;
		$ilBench->start("ilObjCourseListGUI", "1000_checkAllConditions");
		$this->conditions_ok = ilConditionHandler::_checkAllConditionsOfTarget($this->obj_id);
		$ilBench->stop("ilObjCourseListGUI", "1000_checkAllConditions");
//echo "B-".memory_get_usage();echo "-".$full_class;
		$ilBench->start("ilObjCourseListGUI", "2000_getInstance");
		#$this->course_obj =& ilObjectFactory::getInstanceByRefId($this->ref_id);
		$ilBench->stop("ilObjCourseListGUI", "2000_getInstance");
//echo "C-".memory_get_usage();echo "-".$full_class;
		$ilBench->start("ilObjCourseListGUI", "3000_initCourseMemberObject");
		#$this->course_obj->initCourseMemberObject();
		$ilBench->stop("ilObjCourseListGUI", "3000_initCourseMemberObject");
//echo "D-".memory_get_usage();echo "-".$full_class;
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

		$props = array();

		// offline
		include_once 'Modules/Course/classes/class.ilObjCourse.php';
		if(!ilObjCourse::_isActivated($this->obj_id))
		{
			$props[] = array("alert" => true, "property" => $lng->txt("status"),
				"value" => $lng->txt("offline"));
		}

		// blocked
		include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
		$members = ilCourseParticipants::_getInstanceByObjId($this->obj_id);
		if($members->isBlocked($ilUser->getId()) and $members->isAssigned($ilUser->getId()))
		{
			$props[] = array("alert" => true, "property" => $lng->txt("member_status"),
				"value" => $lng->txt("crs_status_blocked"));
		}

		// pending subscription
		if (ilCourseParticipants::_isSubscriber($this->obj_id,$ilUser->getId()))
		{
			$props[] = array("alert" => true, "property" => $lng->txt("member_status"),
				"value" => $lng->txt("crs_status_pending"));
		}

		return $props;
	}
} // END class.ilObjCategoryGUI
?>
