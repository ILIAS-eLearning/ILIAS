<?php

namespace ILIAS\Changelog\Infrastructure\Repository;


use ilDBInterface;
use ILIAS\Changelog\Events\Membership\MembershipRequestAccepted;
use ILIAS\Changelog\Events\Membership\MembershipRequestDenied;
use ILIAS\Changelog\Events\Membership\MembershipRequested;
use ILIAS\Changelog\Events\Membership\SubscribedToCourse;
use ILIAS\Changelog\Events\Membership\UnsubscribedFromCourse;
use ILIAS\Changelog\Infrastructure\AR\EventAR;

/**
 * Class ilDBMembershipRepository
 * @package ILIAS\Changelog\Membership\Repository
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilDBMembershipEventRepository extends MembershipRepository {

	/**
	 * @var ilDBInterface
	 */
	protected $database;

	/**
	 * ilDBMembershipRepository constructor.
	 */
	public function __construct() {
		global $DIC;
		$this->database = $DIC->database();
	}


	/**
	 * @param MembershipRequested $membershipRequested
	 */
	public function saveMembershipRequested(MembershipRequested $membershipRequested) {
		$event_ar = new EventAR();
		$event_ar->setUserId($membershipRequested->getRequestingUserId());
		$event_ar->setTypeId($membershipRequested->getTypeId());
		$event_ar->setTimestamp(time());
		$event_ar->create();

		$membership_event_ar = new MembershipEventAR();
		$membership_event_ar->setUserId($membershipRequested->getRequestingUserId());
		$membership_event_ar->setObjId($membershipRequested->getCrsObjId());
		$membership_event_ar->setEventId($event_ar->getId());
		$membership_event_ar->create();
	}


	/**
	 * @param MembershipRequestAccepted $membershipRequestAccepted
	 */
	public function saveMembershipRequestAccepted(MembershipRequestAccepted $membershipRequestAccepted) {
		$event_ar = new EventAR();
		$event_ar->setUserId($membershipRequestAccepted->getAcceptingUserId());
		$event_ar->setTypeId($membershipRequestAccepted->getTypeId());
		$event_ar->setTimestamp(time());
		$event_ar->create();

		$membership_event_ar = new MembershipEventAR();
		$membership_event_ar->setUserId($membershipRequestAccepted->getRequestingUserId());
		$membership_event_ar->setObjId($membershipRequestAccepted->getCrsObjId());
		$membership_event_ar->setEventId($event_ar->getId());
		$membership_event_ar->create();
	}

	/**
	 * @param MembershipRequestDenied $membershipRequestDenied
	 */
	public function saveMembershipRequestDenied(MembershipRequestDenied $membershipRequestDenied) {
		$event_ar = new EventAR();
		$event_ar->setUserId($membershipRequestDenied->getDenyingUserId());
		$event_ar->setTypeId($membershipRequestDenied->getTypeId());
		$event_ar->setTimestamp(time());
		$event_ar->create();

		$membership_event_ar = new MembershipEventAR();
		$membership_event_ar->setUserId($membershipRequestDenied->getRequestingUserId());
		$membership_event_ar->setObjId($membershipRequestDenied->getCrsObjId());
		$membership_event_ar->setEventId($event_ar->getId());
		$membership_event_ar->create();
	}

	/**
	 * @param SubscribedToCourse $subscribedToCourse
	 */
	public function saveSubscribedToCourse(SubscribedToCourse $subscribedToCourse) {
		$event_ar = new EventAR();
		$event_ar->setUserId($subscribedToCourse->getSubscribingUserId());
		$event_ar->setTypeId($subscribedToCourse->getTypeId());
		$event_ar->setTimestamp(time());
		$event_ar->create();

		$membership_event_ar = new MembershipEventAR();
		$membership_event_ar->setUserId($subscribedToCourse->getSubscribingUserId());
		$membership_event_ar->setObjId($subscribedToCourse->getCrsObjId());
		$membership_event_ar->setEventId($event_ar->getId());
		$membership_event_ar->create();
	}

	/**
	 * @param UnsubscribedFromCourse $unsubscribedFromCourse
	 */
	public function saveUnsubscribedFromCourse(UnsubscribedFromCourse $unsubscribedFromCourse) {
		$event_ar = new EventAR();
		$event_ar->setUserId($unsubscribedFromCourse->getUnsubscribingUserId());
		$event_ar->setTypeId($unsubscribedFromCourse->getTypeId());
		$event_ar->setTimestamp(time());
		$event_ar->create();

		$membership_event_ar = new MembershipEventAR();
		$membership_event_ar->setUserId($unsubscribedFromCourse->getUnsubscribingUserId());
		$membership_event_ar->setObjId($unsubscribedFromCourse->getCrsObjId());
		$membership_event_ar->setEventId($event_ar->getId());
		$membership_event_ar->create();
	}
}