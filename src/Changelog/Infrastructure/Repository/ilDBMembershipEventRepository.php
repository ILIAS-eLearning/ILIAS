<?php

namespace ILIAS\Changelog\Infrastructure\Repository;


use Exception;
use ilDateTime;
use ilDateTimeException;
use ilDBInterface;
use ILIAS\Changelog\Events\Membership\AddedToCourse;
use ILIAS\Changelog\Events\Membership\MembershipRequestAccepted;
use ILIAS\Changelog\Events\Membership\MembershipRequestDenied;
use ILIAS\Changelog\Events\Membership\MembershipRequested;
use ILIAS\Changelog\Events\Membership\RemovedFromCourse;
use ILIAS\Changelog\Events\Membership\SubscribedToCourse;
use ILIAS\Changelog\Events\Membership\UnsubscribedFromCourse;
use ILIAS\Changelog\Infrastructure\AR\EventAR;
use ILIAS\Changelog\Infrastructure\AR\EventID;
use ILIAS\Changelog\Infrastructure\AR\MembershipEventAR;
use ILIAS\Changelog\Query\Requests\getLogsOfCourseRequest;
use ILIAS\Changelog\Query\Requests\getLogsOfUserRequest;
use ILIAS\Changelog\Query\Responses\getLogsOfCourseResponse;
use ILIAS\Changelog\Query\Responses\getLogsOfUserResponse;
use ILIAS\Changelog\Query\Responses\LogOfCourse;
use ILIAS\Changelog\Query\Responses\LogOfUser;
use ilObjCourse;
use ilObjUser;

/**
 * Class ilDBMembershipEventRepository
 * @package ILIAS\Changelog\Infrastructure\Repository
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilDBMembershipEventRepository extends MembershipRepository {

	/**
	 * @var ilDBInterface
	 */
	protected $database;

	/**
	 * ilDBMembershipEventRepository constructor.
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
	 * @throws Exception
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
	 * @param RemovedFromCourse $RemovedFromCourse
	 */
	public function saveRemovedFromCourse(RemovedFromCourse $RemovedFromCourse) {
		$this->saveMembershipEvent(
			$RemovedFromCourse->getTypeId(),
			$RemovedFromCourse->getCrsObjId(),
			$RemovedFromCourse->getRemovingUserId(),
			$RemovedFromCourse->getMemberUserId()
		);
	}
	/**
	 * @param AddedToCourse $AddedToCourse
	 */
	public function saveAddedToCourse(AddedToCourse $AddedToCourse) {
		$this->saveMembershipEvent(
			$AddedToCourse->getTypeId(),
			$AddedToCourse->getCrsObjId(),
			$AddedToCourse->getAddingUserId(),
			$AddedToCourse->getMemberUserId()
		);
	}

	/**
	 * @param getLogsOfUserRequest $getLogsOfUserRequest
	 * @return getLogsOfUserResponse
	 * @throws ilDateTimeException
	 */
	public function getLogsOfUser(getLogsOfUserRequest $getLogsOfUserRequest): getLogsOfUserResponse {
		$query = 'SELECT event.type_id as event_type_id, event.actor_user_id as acting_user_id, event.timestamp, member.crs_obj_id, member.hist_crs_title, acting_usr.login as acting_user_login, acting_usr.firstname as acting_user_firstname, acting_usr.lastname as acting_user_lastname' .
			' FROM ' . EventAR::TABLE_NAME . ' event ' .
			'INNER JOIN ' . MembershipEventAR::TABLE_NAME . ' member ON event.event_id = member.event_id ' .
			'INNER JOIN usr_data acting_usr ON acting_usr.usr_id = event.actor_user_id ';

		$where = [];
		if ($date_from = $getLogsOfUserRequest->getFilter()->getDateFrom()) {
			$where[] = 'timestamp >= ' . $this->database->quote($date_from->get(IL_CAL_DATETIME, 'Y-m-d'), 'timestamp');
		}
		if ($date_to = $getLogsOfUserRequest->getFilter()->getDateTo()) {
			$where[] = 'timestamp <= ' . $this->database->quote($date_to->get(IL_CAL_DATETIME, 'Y-m-d'), 'timestamp');
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

		$member_user = new ilObjUser($getLogsOfUserRequest->getUserId());
		$getLogsOfUserResponse = new getLogsOfUserResponse();
		$res = $this->database->query($query);
		while ($record = $this->database->fetchObject($res)) {
			$LogOfUser = new LogOfUser();
			$LogOfUser->acting_user_id = $record->acting_user_id;
			$LogOfUser->acting_user_login = $record->acting_user_login;
			$LogOfUser->acting_user_firstname = $record->acting_user_firstname;
			$LogOfUser->acting_user_lastname = $record->acting_user_lastname;
			$LogOfUser->event_title_lang_var = 'changelog_event_type_' . $record->event_type_id;
			$LogOfUser->event_type_id = $record->event_type_id;
			$LogOfUser->crs_obj_id = $record->crs_obj_id;
			$LogOfUser->hist_crs_title = $record->hist_crs_title;
			$LogOfUser->date = new ilDateTime($record->timestamp, IL_CAL_DATETIME);
			$LogOfUser->member_user_id = $member_user->getId();
			$LogOfUser->member_login = $member_user->getLogin();
			$LogOfUser->member_firstname = $member_user->getFirstname();
			$LogOfUser->member_lastname = $member_user->getLastname();

			$getLogsOfUserResponse->logsOfUser[] = $LogOfUser;
		}

		return $getLogsOfUserResponse;
	}

	/**
	 * @param getLogsOfCourseRequest $getLogsOfCourseRequest
	 * @return getLogsOfCourseResponse
	 * @throws ilDateTimeException
	 */
	public function getLogsOfCourse(getLogsOfCourseRequest $getLogsOfCourseRequest): getLogsOfCourseResponse {
		$query = 'SELECT event.type_id as event_type_id, event.actor_user_id as acting_user_id, event.timestamp, member.crs_obj_id, member.hist_crs_title, acting_usr.login as acting_user_login, acting_usr.firstname as acting_user_firstname, acting_usr.lastname as acting_user_lastname, member_usr.usr_id as member_user_id, member_usr.login as member_login, member_usr.firstname as member_firstname, member_usr.lastname as member_lastname ' .
			' FROM ' . EventAR::TABLE_NAME . ' event ' .
			'INNER JOIN ' . MembershipEventAR::TABLE_NAME . ' member ON event.event_id = member.event_id ' .
			'INNER JOIN usr_data acting_usr ON acting_usr.usr_id = event.actor_user_id ' .
			'INNER JOIN usr_data member_usr ON member.member_user_id = member_usr.usr_id ';

		$where = [];
		if ($date_from = $getLogsOfCourseRequest->getFilter()->getDateFrom()) {
			$where[] = 'timestamp >= ' . $this->database->quote($date_from->get(IL_CAL_DATETIME, 'Y-m-d'), 'timestamp');
		}
		if ($date_to = $getLogsOfCourseRequest->getFilter()->getDateTo()) {
			$where[] = 'timestamp <= ' . $this->database->quote($date_to->get(IL_CAL_DATETIME, 'Y-m-d'), 'timestamp');
		}
		if ($event_type = $getLogsOfCourseRequest->getFilter()->getEventType()) {
			$where[] = 'event.type_id = ' . $this->database->quote($event_type, 'integer');
		}
		if ($user_id = $getLogsOfCourseRequest->getFilter()->getUserId()) {
			$where[] = 'member.member_user_id = ' . $this->database->quote($user_id, 'integer');
		}

		$query .= 'WHERE member.crs_obj_id = ' . $this->database->quote($getLogsOfCourseRequest->getCrsObjId(), 'integer');

		if (!empty($where)) {
			$query .= ' AND ' . implode(' AND ', $where);
		}

		$query .= ' ORDER BY ' . $getLogsOfCourseRequest->getOrderBy() . ' ' . $getLogsOfCourseRequest->getOrderDirection();



		if ($limit = $getLogsOfCourseRequest->getLimit()) {
			$query .= ' LIMIT ' . $limit . ',' . $getLogsOfCourseRequest->getOffset();
		}

		$getLogsOfCourseResponse = new getLogsOfCourseResponse();
		$res = $this->database->query($query);
		while ($record = $this->database->fetchObject($res)) {
			$LogOfCourse = new LogOfCourse();
			$LogOfCourse->acting_user_id = $record->acting_user_id;
			$LogOfCourse->acting_user_login = $record->acting_user_login;
			$LogOfCourse->acting_user_firstname = $record->acting_user_firstname;
			$LogOfCourse->acting_user_lastname = $record->acting_user_lastname;
			$LogOfCourse->event_title_lang_var = 'changelog_event_type_' . $record->event_type_id;
			$LogOfCourse->event_type_id = $record->event_type_id;
			$LogOfCourse->crs_obj_id = $record->crs_obj_id;
			$LogOfCourse->hist_crs_title = $record->hist_crs_title;
			$LogOfCourse->date = new ilDateTime($record->timestamp, IL_CAL_DATETIME);
			$LogOfCourse->member_user_id = $record->member_user_id;
			$LogOfCourse->member_login = $record->member_login;
			$LogOfCourse->member_firstname = $record->member_firstname;
			$LogOfCourse->member_lastname = $record->member_lastname;

			$getLogsOfCourseResponse->logsOfCourse[] = $LogOfCourse;
		}

		return $getLogsOfCourseResponse;
	}

}