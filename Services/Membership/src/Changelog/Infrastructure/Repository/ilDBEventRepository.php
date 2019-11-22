<?php

namespace ILIAS\Membership\Changelog\Infrastructure\Repository;

use ActiveRecordList;
use arException;
use Exception;
use ILIAS\Membership\Changelog\Infrastructure\AR\EventAR;
use ILIAS\Membership\Changelog\Infrastructure\AR\EventID;
use ILIAS\Membership\Changelog\Interfaces\Event;
use ILIAS\Membership\Changelog\Interfaces\EventRepository;
use ILIAS\Membership\Changelog\Query\DTOBuilder;
use ILIAS\Membership\Changelog\Query\EventDTO;
use ILIAS\Membership\Changelog\Query\Filter;
use ILIAS\Membership\Changelog\Query\Options;

/**
 * Class ilDBEventRepository
 *
 * @package ILIAS\Membership\Changelog\Infrastructure\Repository
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
    public function storeEvent(Event $event) : void
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
    protected function setWheres(ActiveRecordList &$EventAR, Filter $filter) : void
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
    protected function setOptions(ActiveRecordList &$EventAR, Options $options) : void
    {
        if ($options->getLimit() !== 0) {
            $EventAR->limit($options->getOffset(), ($options->getOffset() + $options->getLimit()));
        }

        if ($options->getOrderBy() !== '') {
            $EventAR->orderBy($options->getOrderBy(), $options->getOrderDirection());
        }
    }
}