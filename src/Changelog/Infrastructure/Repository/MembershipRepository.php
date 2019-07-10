<?php

namespace ILIAS\Changelog\Infrastructure\Repository;


use getLogsOfUserRequest;
use getLogsOfUserResponse;
use ILIAS\Changelog\Events\Membership\MembershipRequestAccepted;
use ILIAS\Changelog\Events\Membership\MembershipRequestDenied;
use ILIAS\Changelog\Events\Membership\MembershipRequested;
use ILIAS\Changelog\Events\Membership\SubscribedToCourse;
use ILIAS\Changelog\Events\Membership\UnsubscribedFromCourse;
use ILIAS\Changelog\Interfaces\Repository;

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
	abstract public function saveMembershipRequested(MembershipRequested $membershipRequested);

	/**
	 * @param MembershipRequestAccepted $membershipRequestAccepted
	 */
	abstract public function saveMembershipRequestAccepted(MembershipRequestAccepted $membershipRequestAccepted);

	/**
	 * @param MembershipRequestDenied $membershipRequestDenied
	 */
	abstract public function saveMembershipRequestDenied(MembershipRequestDenied $membershipRequestDenied);

	/**
	 * @param SubscribedToCourse $subscribedToCourse
	 */
	abstract public function saveSubscribedToCourse(SubscribedToCourse $subscribedToCourse);

	/**
	 * @param UnsubscribedFromCourse $unsubscribedFromCourse
	 */
	abstract public function saveUnsubscribedFromCourse(UnsubscribedFromCourse $unsubscribedFromCourse);

	/**
	 * @param getLogsOfUserRequest $getLogsOfUserRequest
	 * @return getLogsOfUserResponse
	 */
	abstract public function getLogsOfUser(getLogsOfUserRequest $getLogsOfUserRequest): getLogsOfUserResponse;
}