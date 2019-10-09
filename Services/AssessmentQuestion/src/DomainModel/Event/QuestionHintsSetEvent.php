<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Event;



use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\CQRS\Event\AbstractIlContainerDomainEvent;
use ILIAS\AssessmentQuestion\DomainModel\Hint\Hints;

/**
 * Class QuestionHintsSetEvent
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QuestionHintsSetEvent extends AbstractIlContainerDomainEvent {

    public const NAME = 'QuestionHintsSetEvent';
    /**
     * @var Hints
     */
    protected $hints;


    /**
     * QuestionHintsSetEvent constructor.
     *
     * @param DomainObjectId     $id
     * @param int                $container_obj_id
     * @param int                $initiating_user_id
     * @param Hints|null $hints
     *
     * @throws \ilDateTimeException
     */
    public function __construct(DomainObjectId $id,
        int $container_obj_id,
        int $initiating_user_id,
        int $question_int_id,
        Hints $hints = null)
    {
        parent::__construct($id, $container_obj_id, $initiating_user_id, $question_int_id);
        $this->hints = $hints;
    }

    /**
     * @return string
     *
     * Add a Constant EVENT_NAME to your class: Name it: Classname
     * e.g. 'QuestionCreatedEvent'
     */
    public function getEventName(): string {
        return self::NAME;
    }

    /**
     * @return Hints
     */
    public function getHints(): Hints {
        return $this->hints;
    }

    public function getEventBody(): string {
        return json_encode($this->hints->getHints());
    }

    /**
     * @param string $json_data
     */
    public function restoreEventBody(string $json_data) {
        $this->hints = Hints::deserialize($json_data);
    }
}