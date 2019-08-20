<?php

namespace ILIAS\Changelog\Infrastructure\Repository;

use Exception;
use ilDateTimeException;
use ILIAS\Changelog\Infrastructure\AR\EventAR;
use ILIAS\Changelog\Infrastructure\AR\EventID;
use ILIAS\Changelog\Interfaces\Event;
use ILIAS\Changelog\Interfaces\EventRepository;
use ILIAS\Changelog\Query\Requests\getLogsOfUserRequest;
use ILIAS\Changelog\Query\Responses\getLogsOfUserResponse;
use ilObjUser;

/**
 * Class ilDBEventRepository
 *
 * @package ILIAS\Changelog\Infrastructure\Repository
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilDBEventRepository implements EventRepository
{

    // WRITING

    /**
     * @param Event $event
     *
     * @throws Exception
     */
    public function storeEvent(Event $event)
    {
        $event_id = new EventID();

        $event_ar = new EventAR();
        $event_ar->setEventId($event_id);
        $event_ar->setEventName($event->getName());
        $event_ar->setActorUserId($event->getActorUserId());
        $event_ar->setSubjectUserId($event->getSubjectUserId());
        $event_ar->setSubjectObjId($event->getSubjectObjId());
        $event_ar->setAdditionalData($event->getAdditionalData());
        $event_ar->setILIASComponent($event->getILIASComponent());
        $event_ar->setTimestamp(time());
        $event_ar->create();
    }


    // READING


    /**
     * @param getLogsOfUserRequest $getLogsOfUserRequest
     *
     * @return getLogsOfUserResponse
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
}