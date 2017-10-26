<?php
/**
 * cat-tms-patch start
 */

use ILIAS\TMS\Booking;

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

/**
 * This is how the booking of users really works. Dark magic happening here.
 */
class ilTMSCancelActions implements Booking\Actions {
	/**
	 * @inheritdoc
	 */
	public function cancelUser($crs_ref_id, $user_id) {
		require_once("Modules/Course/classes/class.ilCourseParticipants.php");
		require_once("Services/Membership/classes/class.ilWaitingList.php");
		$course = ilObjectFactory::getInstanceByRefId($crs_ref_id);
		if(ilCourseParticipants::_isParticipant($course->getRefId(), $user_id)) {
			$course->getMemberObject()->delete($user_id);
			return Booking\Actions::STATE_REMOVED_FROM_COURSE;
		}

		$crs_id = $course->getId();
		if(ilWaitingList::_isOnList($user_id, $crs_id)) {
			ilWaitingList::deleteUserEntry($user_id, $crs_id);
			return Booking\Actions::STATE_REMOVED_FROM_WAITINGLIST;
		}

		throw new \LogicException("User can not be canceld. Not booked on course or waitinglist.");
	}
}

/**
 * cat-tms-patch end
 */
