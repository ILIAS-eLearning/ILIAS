<?php

namespace ILIAS\Changelog\Infrastructure\Repository;


use ILIAS\Changelog\Events\Membership\AddedToCourse;
use ILIAS\Changelog\Events\Membership\AutofilledFromWaitingList;
use ILIAS\Changelog\Events\Membership\ManuallyAddedFromWaitingList;
use ILIAS\Changelog\Events\Membership\MembershipRequestAccepted;
use ILIAS\Changelog\Events\Membership\MembershipRequestDenied;
use ILIAS\Changelog\Events\Membership\MembershipRequested;
use ILIAS\Changelog\Events\Membership\RemovedFromCourse;
use ILIAS\Changelog\Events\Membership\SubscribedToCourse;
use ILIAS\Changelog\Events\Membership\UnsubscribedFromCourse;
use ILIAS\Changelog\Interfaces\Repository;
use ILIAS\Changelog\Query\Requests\getLogsOfCourseRequest;
use ILIAS\Changelog\Query\Requests\getLogsOfUserAnonymizedRequest;
use ILIAS\Changelog\Query\Requests\getLogsOfUserRequest;
use ILIAS\Changelog\Query\Responses\getLogsOfCourseResponse;
use ILIAS\Changelog\Query\Responses\getLogsOfUserAnonymizedResponse;
use ILIAS\Changelog\Query\Responses\getLogsOfUserResponse;

/**
 * Class MembershipRepository
 * @package ILIAS\Changelog\Infrastructure\Repository
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
	 * @param RemovedFromCourse $removedFromCourse
	 * @return mixed
	 */
	abstract public function saveRemovedFromCourse(RemovedFromCourse $removedFromCourse);

	/**
	 * @param AddedToCourse $addedToCourse
	 * @return mixed
	 */
	abstract public function saveAddedToCourse(AddedToCourse $addedToCourse);

	/**
	 * @param ManuallyAddedFromWaitingList $manuallyAddedFromWaitingList
	 * @return mixed
	 */
	abstract public function saveManuallyAddedFromWaitingList(ManuallyAddedFromWaitingList $manuallyAddedFromWaitingList);

	/**
	 * @param AutofilledFromWaitingList $autofilledFromWaitingList
	 * @return mixed
	 */
	abstract public function saveAutofilledFromWaitingList(AutofilledFromWaitingList $autofilledFromWaitingList);

	/**
	 * @param getLogsOfUserRequest $getLogsOfUserRequest
	 * @return getLogsOfUserResponse
	 */
	abstract public function getLogsOfUser(getLogsOfUserRequest $getLogsOfUserRequest): getLogsOfUserResponse;

	/**
	 * @param getLogsOfUserAnonymizedRequest $getLogsOfUserAnonymizedAnonymizedRequest
	 * @return getLogsOfUserAnonymizedResponse
	 */
	abstract public function getLogsOfUserAnonymized(getLogsOfUserAnonymizedRequest $getLogsOfUserAnonymizedAnonymizedRequest): getLogsOfUserAnonymizedResponse;

	/**
	 * @param getLogsOfCourseRequest $getLogsOfCourseRequest
	 * @return getLogsOfCourseResponse
	 */
	abstract public function getLogsOfCourse(getLogsOfCourseRequest $getLogsOfCourseRequest): getLogsOfCourseResponse;
}