<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\AssessmentQuestion\DomainModel\Feedback;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptionFeedbackMode;
use JsonSerializable;

/**
 * Class Feedback
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class Feedback extends AbstractValueObject implements JsonSerializable
{

    const ANSWER_CORRECT_FEEDBACK = "acfclass";
    const ANSWER_WRONG_FEEDBACK = "awfdclass";
    const ANSWER_OPTION_FEEDBACK_MODE = "aofmdclass";
    /**
     * @var AnswerCorrectFeedback
     */
    protected $answer_correct_feedback;
    /**
     * @var AnswerWrongFeedback
     */
    protected $answer_wrong_feedback;
    /**
     * @var AnswerOptionFeedbackMode
     */
    protected $answer_option_feedback_mode;


    public function __construct(
        AnswerCorrectFeedback $answer_correct_feedback = null,
        AnswerWrongFeedback $answer_wrong_feedback = null,
        AnswerOptionFeedbackMode $answer_option_feedback_mode = null
    ) {
        $this->answer_correct_feedback = $answer_correct_feedback;
        $this->answer_wrong_feedback = $answer_wrong_feedback;
        $this->answer_option_feedback_mode = $answer_option_feedback_mode;
    }


    /**
     * @return AnswerCorrectFeedback
     */
    public function getAnswerCorrectFeedback() : ?AnswerCorrectFeedback
    {
        return $this->answer_correct_feedback;
    }


    /**
     * @return AnswerWrongFeedback
     */
    public function getAnswerWrongFeedback() : ?AnswerWrongFeedback
    {
        return $this->answer_wrong_feedback;
    }


    /**
     * @return Null|AnswerOptionFeedbackMode
     */
    public function getAnswerOptionFeedbackMode() : ?AnswerOptionFeedbackMode
    {
        return $this->answer_option_feedback_mode;
    }


    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        $vars[self::ANSWER_CORRECT_FEEDBACK] = get_class($this->answer_correct_feedback);
        $vars[self::ANSWER_WRONG_FEEDBACK] = get_class($this->answer_wrong_feedback);
        $vars[self::ANSWER_OPTION_FEEDBACK_MODE] = get_class($this->answer_option_feedback_mode);

        return $vars;
    }


    public function equals(AbstractValueObject $other) : bool
    {
        if (get_class($this) !== get_class($other)) {
            return false;
        }

        if (!AbstractValueObject::isNullableEqual($this->answer_correct_feedback, $other->answer_correct_feedback)) {
            return false;
        }

        if (!AbstractValueObject::isNullableEqual($this->answer_wrong_feedback, $other->answer_wrong_feedback)) {
            return false;
        }

        if (!AbstractValueObject::isNullableEqual($this->answer_option_feedback_mode, $other->answer_option_feedback_mode)) {
            return false;
        }

        return true;
    }


    public static function deserialize(?string $json_data) : ?AbstractValueObject
    {
        $data = json_decode($json_data);

        $feedback = new Feedback(
            new AnswerCorrectFeedback((string) $data->answer_correct_feedback->answer_feedback),
            new AnswerWrongFeedback((string) $data->answer_wrong_feedback->answer_feedback),
            new AnswerOptionFeedbackMode((int) $data->answer_option_feedback_mode->answer_option_feedback_mode));

        return $feedback;
    }
}