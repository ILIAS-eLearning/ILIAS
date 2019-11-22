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
use ILIAS\Membership\Changelog\Query\Response;

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
     * @return Response
     * @throws arException
     */
    public function getEvents(Filter $filter, Options $options) : Response
    {
        $EventAR = EventAR::getCollection();

        $this->setWheres($EventAR, $filter);
        $this->joinTables($EventAR, $options->getFetchObjectTitle());

        $max_count = $EventAR->count();

        $this->setOptions($EventAR, $options);
        return new Response(
            DTOBuilder::getInstance()->buildEventDTOsFromARs($EventAR->get()),
            $max_count
        );
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
            $EventAR->where(['timestamp' => date('Y-m-d H:i:s', $filter->getTimestampFrom())], '>');
        }

        if ($filter->getTimestampTo() !== 0) {
            $EventAR->where(['timestamp' => date('Y-m-d H:i:s', $filter->getTimestampTo())], '<');
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
        if (!empty($filter->getSubjectLogins())) {
            $EventAR->where(['usr_data_2.login' => $filter->getSubjectLogins()], 'IN');
        }
        if (!empty($filter->getActorLogins())) {
            $EventAR->where(['usr_data.login' => $filter->getActorLogins()], 'IN');
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
            $EventAR->limit($options->getOffset(), $options->getLimit());
        }

        if ($options->getOrderBy() !== '') {
            $EventAR->orderBy($options->getOrderBy(), $options->getOrderDirection());
        }
    }


    /**
     * @param ActiveRecordList $EventAR
     * @param bool             $fetch_object_title
     */
    protected function joinTables(ActiveRecordList &$EventAR, bool $fetch_object_title)
    {
        $EventAR->leftjoin('usr_data', 'actor_user_id', 'usr_id', ['firstname', 'lastname', 'login']);
        $EventAR->leftjoin('usr_data', 'subject_user_id', 'usr_id', ['firstname', 'lastname', 'login']);

        if ($fetch_object_title) {
            $EventAR->leftjoin('object_data', 'subject_obj_id', 'obj_id', ['title']);
        }
    }
}