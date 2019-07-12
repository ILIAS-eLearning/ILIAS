<?php

namespace ILIAS\Changelog\Infrastructure\Repository;


use Exception;
use ilDateTime;
use ilDateTimeException;
use ilDBInterface;
use ILIAS\Changelog\Events\GlobalEvents\ChangelogActivated;
use ILIAS\Changelog\Events\GlobalEvents\ChangelogDeactivated;
use ILIAS\Changelog\Events\Membership\AddedToCourse;
use ILIAS\Changelog\Events\Membership\AutofilledFromWaitingList;
use ILIAS\Changelog\Events\Membership\ManuallyAddedFromWaitingList;
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
use ILIAS\Changelog\Query\Requests\getLogsOfUserAnonymizedRequest;
use ILIAS\Changelog\Query\Requests\getLogsOfUserRequest;
use ILIAS\Changelog\Query\Responses\getLogsOfCourseResponse;
use ILIAS\Changelog\Query\Responses\getLogsOfUserAnonymizedResponse;
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

	const ANONYMOUS = '[anonymous]';
	const DELETED = '[deleted]';

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
	 * @throws Exception
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
	 * @throws Exception
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
	 * @throws Exception
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
	 * @throws Exception
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
	 * @throws Exception
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
	 * @throws Exception
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
	 * @throws Exception
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
	 * @param ManuallyAddedFromWaitingList $manuallyAddedFromWaitingList
	 * @return void
	 * @throws Exception
	 */
	public function saveManuallyAddedFromWaitingList(ManuallyAddedFromWaitingList $manuallyAddedFromWaitingList) {
		$this->saveMembershipEvent(
			$manuallyAddedFromWaitingList->getTypeId(),
			$manuallyAddedFromWaitingList->getCrsObjId(),
			$manuallyAddedFromWaitingList->getAddingUserId(),
			$manuallyAddedFromWaitingList->getMemberUserId()
		);
	}

	/**
	 * @param AutofilledFromWaitingList $autofilledFromWaitingList
	 * @return mixed|void
	 * @throws Exception
	 */
	public function saveAutofilledFromWaitingList(AutofilledFromWaitingList $autofilledFromWaitingList) {
		$this->saveMembershipEvent(
			$autofilledFromWaitingList->getTypeId(),
			$autofilledFromWaitingList->getCrsObjId(),
			$autofilledFromWaitingList->getActingUserId(),
			$autofilledFromWaitingList->getAddedUserId()
		);
	}


	/**
	 * @param getLogsOfUserRequest $getLogsOfUserRequest
	 * @return getLogsOfUserResponse
	 * @throws ilDateTimeException
	 */
	public function getLogsOfUser(getLogsOfUserRequest $getLogsOfUserRequest): getLogsOfUserResponse {
		$member_user = new ilObjUser($getLogsOfUserRequest->getUserId());

		$query = '(SELECT 
			event.type_id as event_type_id, 
			event.actor_user_id as acting_user_id, 
			event.timestamp, 
			member.crs_obj_id, 
			member.hist_crs_title, 
			acting_usr.login as acting_user_login, 
			acting_usr.firstname as acting_user_firstname, 
			acting_usr.lastname as acting_user_lastname,
			' . $member_user->getId() . ' as member_user_id,
			"' . $member_user->getLogin() . '" as member_login, 
			"' . $member_user->getFirstname() . '" as member_firstname, 
			"' . $member_user->getLastname() . '" as member_lastname 
			FROM ' . EventAR::TABLE_NAME . ' event 
			INNER JOIN ' . MembershipEventAR::TABLE_NAME . ' member ON event.event_id = member.event_id 
			INNER JOIN usr_data acting_usr ON acting_usr.usr_id = event.actor_user_id 
			WHERE member.member_user_id = ' . $this->database->quote($getLogsOfUserRequest->getUserId(), 'integer')
		;

		// filters
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

		if (!empty($where)) {
			$query .= ' AND ' . implode(' AND ', $where);
		}
		$query .= ') ';


		// UNION for global events
		$query .= 'UNION (SELECT 
			event.type_id AS event_type_id,
			event.actor_user_id AS acting_user_id,
			event.timestamp,
			0 AS crs_obj_id,
			"" AS hist_crs_title,
			acting_usr.login AS acting_user_login,
			acting_usr.firstname AS acting_user_firstname,
			acting_usr.lastname AS acting_user_lastname,
			0 as member_user_id,
			"" as member_login, 
			"" as member_firstname, 
			"" as member_lastname 
			FROM ' . EventAR::TABLE_NAME . ' event
			INNER JOIN usr_data acting_usr ON acting_usr.usr_id = event.actor_user_id 
			WHERE event.type_id IN (' . ChangelogActivated::TYPE_ID . ',' . ChangelogDeactivated::TYPE_ID . ')';

		if (!empty($where)) {
			$query .= ' AND ' . implode(' AND ', $where);
		}
		$query .= ') ';


		// ORDER BY and LIMIT/OFFSET
		$query .= ' ORDER BY ' . $getLogsOfUserRequest->getOrderBy() . ' ' . $getLogsOfUserRequest->getOrderDirection();

		if ($limit = $getLogsOfUserRequest->getLimit()) {
			$query .= ' LIMIT ' . $getLogsOfUserRequest->getOffset() . ',' . $limit;
		}

		// build response
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
			$LogOfUser->member_user_id = $record->member_user_id;
			$LogOfUser->member_login = $record->member_login;
			$LogOfUser->member_firstname = $record->member_firstname;
			$LogOfUser->member_lastname = $record->member_lastname;

			$getLogsOfUserResponse->logsOfUser[] = $LogOfUser;
		}

		return $getLogsOfUserResponse;
	}

	/**
	 * @param getLogsOfUserAnonymizedRequest $getLogsOfUserAnonymizedRequest $getLogsOfUserRequest
	 * @return getLogsOfUserAnonymizedResponse
	 * @throws ilDateTimeException
	 */
	public function getLogsOfUserAnonymized(getLogsOfUserAnonymizedRequest $getLogsOfUserAnonymizedRequest): getLogsOfUserAnonymizedResponse {
		$member_user = new ilObjUser($getLogsOfUserAnonymizedRequest->getUserId());

		$query = '(SELECT 
			event.type_id as event_type_id, 
			event.actor_user_id as acting_user_id, 
			event.timestamp, 
			member.crs_obj_id, 
			member.hist_crs_title ,
			' . $member_user->getId() . ' as member_user_id,
			"' . $member_user->getLogin() . '" as member_login, 
			"' . $member_user->getFirstname() . '" as member_firstname, 
			"' . $member_user->getLastname() . '" as member_lastname 
			FROM ' . EventAR::TABLE_NAME . ' event 
			INNER JOIN ' . MembershipEventAR::TABLE_NAME . ' member ON event.event_id = member.event_id 
			WHERE member.member_user_id = ' . $this->database->quote($getLogsOfUserAnonymizedRequest->getUserId(), 'integer');

		// filters
		$where = [];
		if ($date_from = $getLogsOfUserAnonymizedRequest->getFilter()->getDateFrom()) {
			$where[] = 'timestamp >= ' . $this->database->quote($date_from->get(IL_CAL_DATETIME, 'Y-m-d'), 'timestamp');
		}
		if ($date_to = $getLogsOfUserAnonymizedRequest->getFilter()->getDateTo()) {
			$where[] = 'timestamp <= ' . $this->database->quote($date_to->get(IL_CAL_DATETIME, 'Y-m-d'), 'timestamp');
		}
		if ($event_type = $getLogsOfUserAnonymizedRequest->getFilter()->getEventType()) {
			$where[] = 'event.type_id = ' . $this->database->quote($event_type, 'integer');
		}

		if (!empty($where)) {
			$query .= ' AND ' . implode(' AND ', $where);
		}
		$query .= ') ';


		// UNION for global events
		$query .= 'UNION (SELECT 
			event.type_id AS event_type_id,
			event.actor_user_id AS acting_user_id,
			event.timestamp,
			0 AS crs_obj_id,
			"" AS hist_crs_title,
			0 as member_user_id,
			"" as member_login, 
			"" as member_firstname, 
			"" as member_lastname 
			FROM ' . EventAR::TABLE_NAME . ' event
			INNER JOIN usr_data acting_usr ON acting_usr.usr_id = event.actor_user_id 
			WHERE event.type_id IN (' . ChangelogActivated::TYPE_ID . ',' . ChangelogDeactivated::TYPE_ID . ')';

		if (!empty($where)) {
			$query .= ' AND ' . implode(' AND ', $where);
		}
		$query .= ') ';


		// ORDER BY and LIMIT/OFFSET
		$query .= ' ORDER BY ' . $getLogsOfUserAnonymizedRequest->getOrderBy() . ' ' . $getLogsOfUserAnonymizedRequest->getOrderDirection();

		if ($limit = $getLogsOfUserAnonymizedRequest->getLimit()) {
			$query .= ' LIMIT ' . $getLogsOfUserAnonymizedRequest->getOffset() . ',' . $limit;
		}

		// build response
		$getLogsOfUserAnonymizedResponse = new getLogsOfUserAnonymizedResponse();
		$res = $this->database->query($query);
		while ($record = $this->database->fetchObject($res)) {
			$actor_is_member = ($record->acting_user_id == $member_user->getId());
			$LogOfUser = new LogOfUser();
			$LogOfUser->acting_user_id = $actor_is_member ? $member_user->getId() : 0;
			$LogOfUser->acting_user_login = $actor_is_member ? $member_user->getLogin() : self::ANONYMOUS;
			$LogOfUser->acting_user_firstname = $actor_is_member ? $member_user->getFirstname() : self::ANONYMOUS;
			$LogOfUser->acting_user_lastname = $actor_is_member ? $member_user->getLastname() : self::ANONYMOUS;
			$LogOfUser->event_title_lang_var = 'changelog_event_type_' . $record->event_type_id;
			$LogOfUser->event_type_id = $record->event_type_id;
			$LogOfUser->crs_obj_id = $record->crs_obj_id;
			$LogOfUser->hist_crs_title = $record->hist_crs_title;
			$LogOfUser->date = new ilDateTime($record->timestamp, IL_CAL_DATETIME);
			$LogOfUser->member_user_id = $record->member_user_id;
			$LogOfUser->member_login = $record->member_login;
			$LogOfUser->member_firstname = $record->member_firstname;
			$LogOfUser->member_lastname = $record->member_lastname;

			$getLogsOfUserAnonymizedResponse->logsOfUser[] = $LogOfUser;
		}

		return $getLogsOfUserAnonymizedResponse;
	}

	/**
	 * @param getLogsOfCourseRequest $getLogsOfCourseRequest
	 * @return getLogsOfCourseResponse
	 * @throws ilDateTimeException
	 */
	public function getLogsOfCourse(getLogsOfCourseRequest $getLogsOfCourseRequest): getLogsOfCourseResponse {
		$query = '(SELECT 
			event.type_id as event_type_id, 
			event.actor_user_id as acting_user_id, 
			event.timestamp, member.crs_obj_id, 
			member.hist_crs_title, 
			IFNULL(acting_usr.login, "' . self::DELETED . '") as acting_user_login, 
			IFNULL(acting_usr.firstname, "' . self::DELETED . '") as acting_user_firstname, 
			IFNULL(acting_usr.lastname, "' . self::DELETED . '") as acting_user_lastname, 
			member.member_user_id as member_user_id, 
			IFNULL(member_usr.login,"' . self::DELETED . '") as member_login, 
			IFNULL(member_usr.firstname, "' . self::DELETED . '") as member_firstname, 
			IFNULL(member_usr.lastname, "' . self::DELETED . '") as member_lastname 
			FROM ' . EventAR::TABLE_NAME . ' event 
			INNER JOIN ' . MembershipEventAR::TABLE_NAME . ' member ON event.event_id = member.event_id 
			LEFT JOIN usr_data acting_usr ON acting_usr.usr_id = event.actor_user_id 
			LEFT JOIN usr_data member_usr ON member.member_user_id = member_usr.usr_id 
			WHERE member.crs_obj_id = ' . $this->database->quote($getLogsOfCourseRequest->getCrsObjId(), 'integer');

		// filters
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

		if (!empty($where)) {
			$query .= ' AND ' . implode(' AND ', $where);
		}
		$query .= ') ';


		// UNION for global events
		$query .= 'UNION (SELECT 
			event.type_id AS event_type_id,
			event.actor_user_id AS acting_user_id,
			event.timestamp,
			0 AS crs_obj_id,
			"" AS hist_crs_title,
			acting_usr.login AS acting_user_login,
			acting_usr.firstname AS acting_user_firstname,
			acting_usr.lastname AS acting_user_lastname,
			0 as member_user_id,
			"" as member_login, 
			"" as member_firstname, 
			"" as member_lastname 
			FROM ' . EventAR::TABLE_NAME . ' event
			INNER JOIN usr_data acting_usr ON acting_usr.usr_id = event.actor_user_id 
			WHERE event.type_id IN (' . ChangelogActivated::TYPE_ID . ',' . ChangelogDeactivated::TYPE_ID . ')';

		if (!empty($where)) {
			$query .= ' AND ' . implode(' AND ', $where);
		}
		$query .= ') ';


		// ORDER BY and LIMIT/OFFSET
		$query .= ' ORDER BY ' . $getLogsOfCourseRequest->getOrderBy() . ' ' . $getLogsOfCourseRequest->getOrderDirection();

		if ($limit = $getLogsOfCourseRequest->getLimit()) {
			$query .= ' LIMIT ' . $getLogsOfCourseRequest->getOffset() . ',' . $limit;
		}

		// build response
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