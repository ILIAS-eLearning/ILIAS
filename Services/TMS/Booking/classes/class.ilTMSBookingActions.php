<?php
/**
 * cat-tms-patch start
 */

use ILIAS\TMS\Booking;

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

/**
 * This is how the booking of users really works. Dark magic happening here.
 */
class ilTMSBookingActions implements Booking\Actions {
	/**
	 * Book the given user on the course. 
	 *
	 * @param	int		$crs_ref_id
	 * @param	int		$user_id
	 */
	public function bookUser($crs_ref_id, $user_id) {
		$course = ilObjectFactory::getInstanceByRefId($crs_ref_id);
		assert('$course instanceof \ilObjCourse');
		$user = ilObjectFactory::getInstanceByObjId($user_id);
		assert('$user instanceof \ilObjUser');

		$this->maybeMakeCourseMember($course, $user);

		return Booking\Actions::STATE_BOOKED;
	}

	/**
	 * Make the user be a member of the course, if he does not already have a member role.
	 *
	 * @throws	\LogicException if user does have another role on the course than member
	 * @param	\ilObjCourse    $course
	 * @param	\ilObjUser      $user
	 * @return	void
	 */
	protected function maybeMakeCourseMember(\ilObjCourse $course, \ilObjUser $user) {
		require_once("Modules/Course/classes/class.ilCourseParticipant.php");
		$participant = \ilCourseParticipant::_getInstancebyObjId($course->getId(), $user->getId());
		if ($participant->isMember()) {
			return;
		}
		if ($participant->isParticipant()) {
			throw new \LogicException("User already has a local role on the course. Won't be able to make him a member.");
		}
		$participant->add($user->getId(), IL_CRS_MEMBER);
	}
}

/**
 * cat-tms-patch end
 */
