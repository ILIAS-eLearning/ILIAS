<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Badge/interfaces/interface.ilBadgeType.php";
require_once "./Services/Badge/interfaces/interface.ilBadgeManual.php";

/**
 * Class ilCourseParticipationBadge
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ModulesCourse
 */
class ilCourseMeritBadge implements ilBadgeType, ilBadgeManual
{
	public function getId()
	{
		return "merit";
	}
	
	public function getCaption()
	{
		global $lng;
		return $lng->txt("badge_crs_merit");
	}
	
	public function isSingleton()
	{
		return true;
	}
	
	public function getValidObjectTypes()
	{
		return array("crs");
	}
	
	public function getConfigGUIInstance()
	{
		// no config
	}
	
	public function getAvailableUserIds($a_obj_id)
	{
		include_once "Modules/Course/classes/class.ilCourseParticipants.php";
		$part = new ilCourseParticipants($a_obj_id);
		return $part->getMembers();
	}
}