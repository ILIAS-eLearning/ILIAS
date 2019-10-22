<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\AssessmentQuestion\DomainModel;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;

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
class Feedback extends AbstractValueObject
{
    const OPT_ANSWER_OPTION_FEEDBACK_MODE_NONE = 0;
    const OPT_ANSWER_OPTION_FEEDBACK_MODE_ALL = 1;
    const OPT_ANSWER_OPTION_FEEDBACK_MODE_CHECKED = 2;
    const OPT_ANSWER_OPTION_FEEDBACK_MODE_CORRECT = 3;
    
    /**
     * @var string
     */
    protected $answer_correct_feedback;
    /**
     * @var string
     */
    protected $answer_wrong_feedback;
    /**
     * @var int
     */
    protected $answer_option_feedback_mode;


    public static function create(
        string $answer_correct_feedback,
        string $answer_wrong_feedback,
        int $answer_option_feedback_mode
    ) {
        $obj = new Feedback();
        $obj->answer_correct_feedback = $answer_correct_feedback;
        $obj->answer_wrong_feedback = $answer_wrong_feedback;
        $obj->answer_option_feedback_mode = $answer_option_feedback_mode;
        return $obj;
    }


    /**
     * @return string
     */
    public function getAnswerCorrectFeedback() : ?string
    {
        return $this->answer_correct_feedback;
    }

    /**
     * @return string
     */
    public function getAnswerWrongFeedback() : ?string
    {
        return $this->answer_wrong_feedback; 
    }

    /**
     * @return int
     */
    public function getAnswerOptionFeedbackMode() : ?int
    {
        return $this->answer_option_feedback_mode;
    }

    public function equals(AbstractValueObject $other) : bool
    {
        /** @var Feedback $other */
        return (get_class($this) === get_class($other) &&
                $this->answer_correct_feedback === $other->answer_correct_feedback &&
                $this->answer_wrong_feedback === $other->answer_wrong_feedback &&
                $this->answer_option_feedback_mode === $other->answer_option_feedback_mode);
    }
}