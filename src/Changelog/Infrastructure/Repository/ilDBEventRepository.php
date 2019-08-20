<?php

namespace ILIAS\Changelog\Infrastructure\Repository;

use ActiveRecordList;
use arException;
use Exception;
use ilDateTimeException;
use ILIAS\Changelog\Infrastructure\AR\EventAR;
use ILIAS\Changelog\Infrastructure\AR\EventID;
use ILIAS\Changelog\Interfaces\Event;
use ILIAS\Changelog\Interfaces\EventRepository;
use ILIAS\Changelog\Query\DTOBuilder;
use ILIAS\Changelog\Query\EventDTO;
use ILIAS\Changelog\Query\Filter;
use ILIAS\Changelog\Query\Options;
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


    /**
     * @param Filter  $filter
     *
     * @param Options $options
     *
     * @return EventDTO[]
     * @throws Exception
     */
    public function getEvents(Filter $filter, Options $options) : array
    {
        $EventAR = EventAR::getCollection();

        $this->setWheres($EventAR, $filter);
        $this->setOptions($EventAR, $options);

        return DTOBuilder::getInstance()->buildEventDTOsFromARs($EventAR->get());
    }




    // private

    /**
     * @param ActiveRecordList $EventAR &$EventAR
     * @param Filter           $filter
     *
     * @throws Exception
     */
    protected function setWheres(ActiveRecordList &$EventAR, Filter $filter)
    {
        if ($filter->getTimestampFrom() !== 0) {
            $EventAR->where(['timestamp' => $filter->getTimestampFrom()], '>');
        }

        if ($filter->getTimestampTo() !== 0) {
            $EventAR->where(['timestamp' => $filter->getTimestampTo()], '<');
        }

        if (!empty($filter->getActorUserIds())) {
            $EventAR->where(['actor_user_id' => $filter->getActorUserIds()], 'IN');
        }

        if (!empty($filter->getSubjectUserIds())) {
            $EventAR->where(['subject_user_id' => $filter->getSubjectUserIds()], 'IN');
        }

        if (!empty($filter->getSubjectObjIds())) {
            $EventAR->where(['subject_obj_id' => $filter->getSubjectObjIds()], 'IN');
        }

        if (!empty($filter->getEventNames())) {
            $EventAR->where(['event_name' => $filter->getEventNames()], 'IN');
        }

        if (!empty($filter->getEventIds())) {
            $EventAR->where(['event_id' => $filter->getEventIds()], 'IN');
        }
    }


    /**
     * @param ActiveRecordList $EventAR
     * @param Options          $options
     *
     * @throws arException
     */
    protected function setOptions(ActiveRecordList &$EventAR, Options $options)
    {
        if ($options->getLimit() !== 0) {
            $EventAR->limit($options->getOffset(), ($options->getOffset() + $options->getLimit()));
        }

        if ($options->getOrderBy() !== '') {
            $EventAR->orderBy($options->getOrderBy(), $options->getOrderDirection());
        }
    }
}