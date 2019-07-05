<?php

namespace ILIAS\Changelog\Membership;


use ILIAS\Changelog\Membership\Events\MembershipRequestAccepted;
use ILIAS\Changelog\Membership\Events\MembershipRequestDenied;
use ILIAS\Changelog\Membership\Events\MembershipRequested;
use ILIAS\Changelog\Membership\Events\SubscribedToCourse;
use ILIAS\Changelog\Membership\Events\UnsubscribedFromCourse;
use ILIAS\Changelog\Repository;

/**
 * Class MembershipRepository
 * @package ILIAS\Changelog\Membership\Repository
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class MembershipRepository implements Repository {

	/**
	 * @param MembershipRequested $membershipRequested
	 */
	abstract public function saveMembershipRequested(MembershipRequested $membershipRequested): void;

	/**
	 * @param MembershipRequestAccepted $membershipRequestAccepted
	 */
	abstract public function saveMembershipRequestAccepted(MembershipRequestAccepted $membershipRequestAccepted): void;

	/**
	 * @param MembershipRequestDenied $membershipRequestDenied
	 */
	abstract public function saveMembershipRequestDenied(MembershipRequestDenied $membershipRequestDenied): void;

	/**
	 * @param SubscribedToCourse $subscribedToCourse
	 */
	abstract public function saveSubscribedToCourse(SubscribedToCourse $subscribedToCourse): void;

	/**
	 * @param UnsubscribedFromCourse $unsubscribedFromCourse
	 */
	abstract public function saveUnsubscribedFromCourse(UnsubscribedFromCourse $unsubscribedFromCourse): void;
}