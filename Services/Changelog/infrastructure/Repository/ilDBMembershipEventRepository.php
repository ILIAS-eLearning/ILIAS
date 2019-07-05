<?php

namespace ILIAS\Changelog\Membership\Repository;


use ilDBInterface;
use ILIAS\Changelog\Infrastructure\AR\EventAR;
use ILIAS\Changelog\Membership\AR\MembershipEventAR;
use ILIAS\Changelog\Membership\Events\MembershipRequestAccepted;
use ILIAS\Changelog\Membership\Events\MembershipRequestDenied;
use ILIAS\Changelog\Membership\Events\MembershipRequested;
use ILIAS\Changelog\Membership\Events\SubscribedToCourse;
use ILIAS\Changelog\Membership\Events\UnsubscribedFromCourse;
use ILIAS\Changelog\Membership\MembershipRepository;

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
	public function saveMembershipRequested(MembershipRequested $membershipRequested): void {
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
	public function saveMembershipRequestAccepted(MembershipRequestAccepted $membershipRequestAccepted): void {
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
	public function saveMembershipRequestDenied(MembershipRequestDenied $membershipRequestDenied): void {
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
	public function saveSubscribedToCourse(SubscribedToCourse $subscribedToCourse): void {
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
	public function saveUnsubscribedFromCourse(UnsubscribedFromCourse $unsubscribedFromCourse): void {
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