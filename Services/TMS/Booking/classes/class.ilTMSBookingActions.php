<?php
/**
 * cat-tms-patch start
 */

use ILIAS\TMS\Booking;
use ILIAS\TMS\Mailing;

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

/**
 * This is how the booking of users really works. Dark magic happening here.
 */
class ilTMSBookingActions implements Booking\Actions {

	public function __construct(Mailing\Actions $mailing) {
		$this->mailing = $mailing;
	}
	/**
	 * @inheritdoc
	 */
	public function bookUser($crs_ref_id, $user_id) {
		$course = ilObjectFactory::getInstanceByRefId($crs_ref_id);
		assert('$course instanceof \ilObjCourse');
		$user = ilObjectFactory::getInstanceByObjId($user_id);
		assert('$user instanceof \ilObjUser');

		return $this->maybeMakeCourseMember($course, $user);
	}

	/**
	 * @inheritdoc
	 */
	public function cancelUser($crs_ref_id, $user_id) {
		require_once("Modules/Course/classes/class.ilCourseParticipants.php");
		require_once("Services/Membership/classes/class.ilWaitingList.php");
		$course = ilObjectFactory::getInstanceByRefId($crs_ref_id);
		if(ilCourseParticipants::_isParticipant($course->getRefId(), $user_id)) {
			$course->getMemberObject()->delete($user_id);
			$this->mailing->sendCourseMail(Mailing\Actions::CANCELED_FROM_COURSE, $course->getRefId(), $user_id);
			return Booking\Actions::STATE_REMOVED_FROM_COURSE;
		}

		$crs_id = $course->getId();
		if(ilWaitingList::_isOnList($user_id, $crs_id)) {
			ilWaitingList::deleteUserEntry($user_id, $crs_id);
			$this->mailing->sendCourseMail(Mailing\Actions::CANCELED_FROM_WAITINGLIST, $course->getRefId(), $user_id);
			return Booking\Actions::STATE_REMOVED_FROM_WAITINGLIST;
		}

		throw new \LogicException("User can not be canceld. Not booked on course or waitinglist.");
	}

	/**
	 * Make the user be a member of the course, if he does not already have a member role.
	 *
	 * @throws	\LogicException if user does have another role on the course than member
	 * @throws	\LogicException if user could not booked or added to waitinglist
	 * @param	\ilObjCourse    $course
	 * @param	\ilObjUser      $user
	 * @return	string
	 */
	protected function maybeMakeCourseMember(\ilObjCourse $course, \ilObjUser $user) {
		require_once("Modules/Course/classes/class.ilCourseParticipant.php");
		$participant = \ilCourseParticipant::_getInstancebyObjId($course->getId(), $user->getId());
		if ($participant->isMember()) {
			return Booking\Actions::STATE_BOOKED;
		}
		if ($participant->isParticipant()) {
			throw new \LogicException("User already has a local role on the course. Won't be able to make him a member.");
		}


		$booking_modality = $this->getFirstBookingModalities((int)$course->getRefId());
		if(! $booking_modality) {
			throw new \LogicException("There are not BookingModalitites below the course. User should have never come here.");
		}

		require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/classes/class.ilObjBookingModalities.php");

		if($this->maybeBookAsMember((int)$course->getRefId(), $booking_modality)) {
			$participant->add($user->getId(), IL_CRS_MEMBER);
			return Booking\Actions::STATE_BOOKED;
		}

		if($this->maybeAddOnWaitingList($course, $booking_modality)) {
			$course->waiting_list_obj->addToList((int)$user->getId());
			return Booking\Actions::STATE_WAITING_LIST;
		}

		throw new \LogicException("User can not be booked. Course and waitinglist are overbooked");
	}

	/**
	 * Check user can be booked as member
	 *
	 * @param int 	$crs_ref_id
	 * @param $booking_modality
	 *
	 * @return bool
	 */
	protected function maybeBookAsMember($crs_ref_id, \ilObjBookingModalities $booking_modality) {
		require_once("Modules/Course/classes/class.ilCourseParticipants.php");
		$max_member = $booking_modality->getMember()->getMax();
		$current_member = \ilCourseParticipants::lookupNumberOfMembers($crs_ref_id);

		if($max_member === null || $current_member < $max_member){
			return true;
		}

		return false;
	}

	/**
	 * Checks user can be added to waitinglist
	 *
	 * @param \ilObjCourse 	$course
	 * @param $booking_modality
	 *
	 * @return bool
	 */
	protected function maybeAddOnWaitingList(\ilObjCourse $course, \ilObjBookingModalities $booking_modality) {
		if ($booking_modality->getWaitinglist()->getModus() == "no_waitinglist") {
			return false;
		}
		$course->initWaitingList();
		$max_waiting = $booking_modality->getWaitinglist()->getMax();
		$current_waiting = $course->waiting_list_obj->getCountUsers();

		if($max_waiting === null || $current_waiting < $max_waiting) {
			return true;
		}

		return false;
	}

	/**
	 * Get the first booking modalities below crs
	 *
	 * @param int 	$ref_id
	 *
	 * @return BookingModalities | null
	 */
	protected function getFirstBookingModalities($ref_id) {
		global $DIC;
		$tree = $DIC->repositoryTree();
		$objDefinition = $DIC["objDefinition"];

		$childs = $tree->getChilds($ref_id);

		foreach ($childs as $child) {
			$type = $child["type"];
			$child_ref = $child["child"];
			if($type == "xbkm") {
				return \ilObjectFactory::getInstanceByRefId($child_ref);
			}
			if($objDefinition->isContainer($type)) {
				$mods =  $this->getFirstBookingModalities($child["child"]);
				if(!is_null($mods)) {
					return $mods;
				}
			}
		}
		return null;
	}
}

/**
 * cat-tms-patch end
 */
