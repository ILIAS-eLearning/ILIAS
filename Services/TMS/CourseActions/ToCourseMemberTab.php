<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

use ILIAS\TMS;

/**
 * This action links to the member tab of the course
 */
class ToCourseMemberTab extends TMS\CourseActionImpl
{
	/**
	 * @inheritdoc
	 */
	public function isAllowedFor($usr_id)
	{
		$course = $this->entity->object();
		return $this->hasAccess($course->getRefId());
	}

	/**
	 * @inheritdoc
	 */
	public function getLink(\ilCtrl $ctrl, $usr_id)
	{
		$course = $this->entity->object();

		require_once("Services/Link/classes/class.ilLink.php");
		return ilLink::_getStaticLink($course->getRefId(), "crs", true, "_mem");
	}

	/**
	 * @inheritdoc
	 */
	public function getLabel()
	{
		global $DIC;
		$lng = $DIC->language();
		return $lng->txt("to_course_member_tab");
	}

	/**
	 * Has user read access to the course
	 *
	 * @param int 	$crs_ref_id
	 *
	 * @return bool
	 */
	protected function hasAccess($crs_ref_id)
	{
		global $DIC;
		$access = $DIC->access();
		$course = $this->entity->object();
		if($access->checkAccess("manage_members", "", $crs_ref_id)) {
			return true;
		}

		return false;
	}
}
