<?php
/* Copyright (c) 2019 Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\CQRS\Event;

use ilDateTime;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;

/**
 * Class AbstractDomainEvent
 *
 * @package ILIAS\Data\Domain\Event
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class AbstractIlContainerDomainEvent implements IlContainerDomainEvent
{

    /**
     * @var DomainObjectId
     */
    protected $aggregate_id;
    /**
     * @var ilDateTime;
     */
    protected $occurred_on;
    /**
     * @var int;
     */
    protected $container_obj_id;
    /**
     * @var int
     */
    protected $initiating_user_id;
    /**
     * @var int
     */
    protected $question_int_id;
    
    /**
     * AbstractQuestionEvent constructor.
     *
     * @param DomainObjectId $aggregate_id
     * @param int            $container_obj_id
     * @param int            $initiating_user_id
     * @param int            $object_id
     *
     * @throws \ilDateTimeException
     */
    public function __construct(DomainObjectId $aggregate_id, int $container_obj_id, int $initiating_user_id, int $question_int_id)
    {

        $this->aggregate_id = $aggregate_id;
        $this->occurred_on = new ilDateTime(time(), IL_CAL_UNIX);
        $this->container_obj_id = $container_obj_id;
        $this->initiating_user_id = $initiating_user_id;
        $this->question_int_id = $question_int_id;
    }


    /**
     * The Aggregate this event belongs to.
     *
     * @return DomainObjectId
     */
    public function getAggregateId() : DomainObjectId
    {
        return $this->aggregate_id;
    }


    /**
     * @return string
     *
     * Add a Constant EVENT_NAME to your class: Name it: [aggregate].[event]
     * e.g. 'question.created'
     */
    public abstract function getEventName() : string;


    /**
     * @return ilDateTime
     */
    public function getOccurredOn() : ilDateTime
    {
        return $this->occurred_on;
    }


    /**
     * @return int
     */
    public function getContainerObjId() : int
    {
        return $this->container_obj_id;
    }


    /**
     * @return int
     */
    public function getInitiatingUserId() : int
    {
        return $this->initiating_user_id;
    }

    /**
     * @return int
     */
    public function getQuestionIntId(): int
    {
        return $this->question_int_id;
    }

    /**
     * @return string
     */
    public function getEventBody() : string
    {
        return json_encode($this);
    }


    public abstract function restoreEventBody(string $json_data);
}