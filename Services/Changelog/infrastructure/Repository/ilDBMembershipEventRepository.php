<?php

namespace ILIAS\Changelog\Membership\Repository;


use ilDBInterface;
use ILIAS\Changelog\Infrastructure\AR\EventAR;
use ILIAS\Changelog\Membership\AR\MembershipEventAR;
use ILIAS\Changelog\Membership\Events\MembershipRequested;
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


}