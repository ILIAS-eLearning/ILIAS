<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Answer;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use JsonSerializable;

/**
 * Abstract Class FeedbackDefinition
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
Abstract class AnswerFeedback extends AbstractValueObject implements JsonSerializable
{

    const VAR_ANSWER_FEEDBACK_CORRECT = 'answer_feedback_correct';
    const VAR_ANSWER_FEEDBACK_WRONG = 'answer_feedback_wrong';
    const VAR_ANSWER_FEEDBACK_CORRECT_PAGE_ID = 1;
    const VAR_ANSWER_FEEDBACK_WRONG_PAGE_ID = 2;
    const VAR_FEEDBACK_TYPE_INT_ID = 'feedback_type_int_id';
    /**
     * @var string
     */
    protected $answer_feedback;


    public function __construct(?string $answer_feedback = "")
    {
        $this->answer_feedback = $answer_feedback;
    }


    public function getAnswerFeedback() : string
    {
        return $this->answer_feedback;
    }


    function equals(AbstractValueObject $other) : bool
    {
        if (get_class($this) !== get_class($other)) {
            return false;
        }

        if ($this->getAnswerFeedback() !== $other->getAnswerFeedback()) {
            return false;
        }

        return true;
    }


    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}