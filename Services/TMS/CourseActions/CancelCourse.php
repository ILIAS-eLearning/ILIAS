<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

use ILIAS\TMS;

/**
 * This actions allows user to cancel a course
 */
class CancelCourse extends TMS\CourseActionImpl
{
	/**
	 * @inheritdoc
	 */
	public function isAllowedFor($usr_id)
	{
		$course = $this->entity->object();
		return $this->hasAccess($course->getRefId()) && $this->maybeCancelled($course);
	}

	/**
	 * @inheritdoc
	 */
	public function getLink(\ilCtrl $ctrl, $usr_id)
	{
		return "";
	}

	/**
	 * @inheritdoc
	 */
	public function getLabel()
	{
		global $DIC;
		$lng = $DIC->language();
		return $lng->txt("cancel_course");
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
		if($access->checkAccess("visible", "", $crs_ref_id)
			&& $access->checkAccess("read", "", $crs_ref_id)
			&& $access->checkAccess("write", "", $crs_ref_id)
		) {
			return true;
		}

		return false;
	}

	/**
	 * Checks the course can be cancelled
	 *
	 * @param \ilObjCourse 	$course
	 *
	 * @return true
	 */
	protected function maybeCancelled(\ilObjCourse $course) {
		return false;
	}
}
