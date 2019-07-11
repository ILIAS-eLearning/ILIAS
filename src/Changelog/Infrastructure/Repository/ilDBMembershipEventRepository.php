<?php

namespace ILIAS\Changelog\Infrastructure\Repository;


use ilDateTime;
use ilDBInterface;
use ILIAS\Changelog\Events\Membership\MembershipRequestAccepted;
use ILIAS\Changelog\Events\Membership\MembershipRequestDenied;
use ILIAS\Changelog\Events\Membership\MembershipRequested;
use ILIAS\Changelog\Events\Membership\SubscribedToCourse;
use ILIAS\Changelog\Events\Membership\UnsubscribedFromCourse;
use ILIAS\Changelog\Infrastructure\AR\EventAR;
use ILIAS\Changelog\Infrastructure\AR\EventID;
use ILIAS\Changelog\Infrastructure\AR\MembershipEventAR;
use ILIAS\Changelog\Query\Requests\getLogsOfUserRequest;
use ILIAS\Changelog\Query\Responses\getLogsOfUserResponse;
use ILIAS\Changelog\Query\Responses\LogOfUser;
use ilObjCourse;
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
	 * @param int $actor_user_id
	 * @param int $affected_user_id
	 * @throws \Exception
	 */
	protected function saveMembershipEvent(int $type_id, int $course_obj_id, int $actor_user_id, int $affected_user_id) {
		$event_id = new EventID();

		$event_ar = new EventAR();
		$event_ar->setEventId($event_id);
		$event_ar->setTypeId($type_id);
		$event_ar->setActorUserId($actor_user_id);
		$event_ar->setTimestamp(time());
		$event_ar->create();

		$membership_event_ar = new MembershipEventAR();
		$membership_event_ar->setMemberUserId($affected_user_id);
		$membership_event_ar->setCrsObjId($course_obj_id);
		$membership_event_ar->setHistCrsTitle(ilObjCourse::_lookupTitle($course_obj_id));
		$membership_event_ar->setEventId($event_id);
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
		$query = 'SELECT even.type_id, event.actor_user_id, event.timestamp, member.member_user_id, member.crs_obj_id, member.hist_crs_title, acting_usr.login as acting_user_login, acting_usr.firstname as acting_user_login, acting_usr.lastname as acting_user_lastname' .
			' FROM ' . EventAR::TABLE_NAME . ' event ' .
			'INNER JOIN ' . MembershipEventAR::TABLE_NAME . ' member ON event.event_id = member.event_id ' .
			'INNER JOIN usr_data acting_usr ON acting_usr.usr_id = event.actor_user_id ' .
			'INNER JOIN usr_data member_usr ON member_usr.usr_id = member.member_user_id ';

		$where = [];
		if ($date_from = $getLogsOfUserRequest->getFilter()->getDateFrom()) {
			$where[] = 'timestamp >= ' . $this->database->quote($date_from->get(IL_CAL_DATETIME, 'Y-m-d'), 'timestamp');
		}
		if ($date_to = $getLogsOfUserRequest->getFilter()->getDateTo()) {
			$where[] = 'timestamp <= ' . $date_to->get(IL_CAL_DATETIME, 'Y-m-d');
		}
		if ($event_type = $getLogsOfUserRequest->getFilter()->getEventType()) {
			$where[] = 'event.type_id = ' . $this->database->quote($event_type, 'integer');
		}

		$query .= 'WHERE member.member_user_id = ' . $this->database->quote($getLogsOfUserRequest->getUserId(), 'integer');

		if (!empty($where)) {
			$query .= ' AND ' . implode(' AND ', $where);
		}

		$query .= ' ORDER BY ' . $getLogsOfUserRequest->getOrderBy() . ' ' . $getLogsOfUserRequest->getOrderDirection();



		if ($limit = $getLogsOfUserRequest->getLimit()) {
			$query .= ' LIMIT ' . $limit . ',' . $getLogsOfUserRequest->getOffset();
		}

		$getLogsOfUserResponse = new getLogsOfUserResponse();
		$res = $this->database->query($query);
		while ($record = $this->database->fetchAssoc($res)) {
			$LogOfUser = new LogOfUser();
			$LogOfUser->acting_user_login = $record->actor_login;
			$LogOfUser->date = new ilDateTime($record->timestamp, IL_CAL_UNIX);
			$LogOfUser->event_type_title = 'test';
			$getLogsOfUserResponse->logsOfUser[] = $LogOfUser;
		}

		return $getLogsOfUserResponse;
	}


}