<?php

namespace ILIAS\Changelog\Infrastructure\Repository;


use getLogsOfUserRequest;
use getLogsOfUserResponse;
use ilDateTime;
use ilDBInterface;
use ILIAS\Changelog\Events\Membership\MembershipRequestAccepted;
use ILIAS\Changelog\Events\Membership\MembershipRequestDenied;
use ILIAS\Changelog\Events\Membership\MembershipRequested;
use ILIAS\Changelog\Events\Membership\SubscribedToCourse;
use ILIAS\Changelog\Events\Membership\UnsubscribedFromCourse;
use ILIAS\Changelog\Infrastructure\AR\EventAR;
use ILIAS\Changelog\Infrastructure\AR\MembershipEventAR;
use ILIAS\Changelog\Query\Responses\LogOfUser;
use ilObjUser;

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
	 * @param int $type_id
	 * @param int $course_obj_id
	 * @param int $agent_user_id
	 * @param int $affected_user_id
	 */
	protected function saveMembershipEvent(int $type_id, int $course_obj_id, int $agent_user_id, int $affected_user_id) {
		$event_ar = new EventAR();
		$event_ar->setActorLogin(ilObjUser::_lookupLogin($agent_user_id));
		$event_ar->setTypeId($type_id);
		$event_ar->setTimestamp(time());
		$event_ar->create();

		$membership_event_ar = new MembershipEventAR();
		$membership_event_ar->setMemberUserId($affected_user_id);
		$membership_event_ar->setMemberLogin(ilObjUser::_lookupLogin($affected_user_id));
		$membership_event_ar->setObjId($course_obj_id);
		$membership_event_ar->setEventId($event_ar->getId());
		$membership_event_ar->create();
	}

	/**
	 * @param MembershipRequested $membershipRequested
	 */
	public function saveMembershipRequested(MembershipRequested $membershipRequested) {
		$this->saveMembershipEvent(
			$membershipRequested->getTypeId(),
			$membershipRequested->getCrsObjId(),
			$membershipRequested->getRequestingUserId(),
			$membershipRequested->getRequestingUserId()
		);
	}


	/**
	 * @param MembershipRequestAccepted $membershipRequestAccepted
	 */
	public function saveMembershipRequestAccepted(MembershipRequestAccepted $membershipRequestAccepted) {
		$this->saveMembershipEvent(
			$membershipRequestAccepted->getTypeId(),
			$membershipRequestAccepted->getCrsObjId(),
			$membershipRequestAccepted->getAcceptingUserId(),
			$membershipRequestAccepted->getRequestingUserId()
		);
	}

	/**
	 * @param MembershipRequestDenied $membershipRequestDenied
	 */
	public function saveMembershipRequestDenied(MembershipRequestDenied $membershipRequestDenied) {
		$this->saveMembershipEvent(
			$membershipRequestDenied->getTypeId(),
			$membershipRequestDenied->getCrsObjId(),
			$membershipRequestDenied->getDenyingUserId(),
			$membershipRequestDenied->getRequestingUserId()
		);
	}

	/**
	 * @param SubscribedToCourse $subscribedToCourse
	 */
	public function saveSubscribedToCourse(SubscribedToCourse $subscribedToCourse) {
		$this->saveMembershipEvent(
			$subscribedToCourse->getTypeId(),
			$subscribedToCourse->getCrsObjId(),
			$subscribedToCourse->getSubscribingUserId(),
			$subscribedToCourse->getSubscribingUserId()
		);
	}

	/**
	 * @param UnsubscribedFromCourse $unsubscribedFromCourse
	 */
	public function saveUnsubscribedFromCourse(UnsubscribedFromCourse $unsubscribedFromCourse) {
		$this->saveMembershipEvent(
			$unsubscribedFromCourse->getTypeId(),
			$unsubscribedFromCourse->getCrsObjId(),
			$unsubscribedFromCourse->getUnsubscribingUserId(),
			$unsubscribedFromCourse->getUnsubscribingUserId()
		);
	}

	/**
	 * @param getLogsOfUserRequest $getLogsOfUserRequest
	 * @return getLogsOfUserResponse
	 */
	public function getLogsOfUser(getLogsOfUserRequest $getLogsOfUserRequest): getLogsOfUserResponse {
		$AR = EventAR::innerjoin(MembershipEventAR::TABLE_NAME, 'id', 'event_id');
		if ($date_from = $getLogsOfUserRequest->getFilter()->getDateFrom()) {
			$AR->where(['timestamp' => $date_from->getUnixTime()], ['timestamp' => '>=']);
		}
		if ($date_to = $getLogsOfUserRequest->getFilter()->getDateTo()) {
			$AR->where(['timestamp' => $date_to->getUnixTime()], ['timestamp' => '<=']);
		}
		if ($event_type = $getLogsOfUserRequest->getFilter()->getEventType()) {
			$AR->where(['type_id' => $event_type]);
		}
		if ($orderBy = $getLogsOfUserRequest->getOrderBy()) {
			$AR->orderBy($orderBy, $getLogsOfUserRequest->getOrderDirection());
		}
		if ($limit = $getLogsOfUserRequest->getLimit()) {
			$AR->limit($getLogsOfUserRequest->getOffset(), $limit);
		}

		$getLogsOfUserResponse = new getLogsOfUserResponse();
		foreach ($AR->get() as $record) {
			$LogOfUser = new LogOfUser();
			$LogOfUser->acting_user_login = $record->actor_login;
			$LogOfUser->date = new ilDateTime($record->timestamp, IL_CAL_UNIX);
			$LogOfUser->event_type_title = 'test';
			$getLogsOfUserResponse->logsOfUser[] = $LogOfUser;
		}

		return $getLogsOfUserResponse;
	}


}