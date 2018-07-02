<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

use ILIAS\TMS;

/**
 * This actions links to the course itself
 */
class ToCourse extends TMS\CourseActionImpl
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
		return ilLink::_getStaticLink($course->getRefId(), "crs");
	}

	/**
	 * @inheritdoc
	 */
	public function getLabel()
	{
		global $DIC;
		$lng = $DIC->language();
		return $lng->txt("to_course");
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
		if($access->checkAccess("read", "", $crs_ref_id)) {
			return true;
		}

		return false;
	}
}
