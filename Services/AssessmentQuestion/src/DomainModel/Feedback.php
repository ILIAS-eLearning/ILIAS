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

    /**
     * @var string[]
     */
    protected $answer_option_feedbacks;

    public static function create(
        string $answer_correct_feedback,
        string $answer_wrong_feedback,
        int $answer_option_feedback_mode,
        array $answer_option_feedbacks = []
    ) {
        $obj = new Feedback();
        $obj->answer_correct_feedback = $answer_correct_feedback;
        $obj->answer_wrong_feedback = $answer_wrong_feedback;
        $obj->answer_option_feedback_mode = $answer_option_feedback_mode;
        $obj->answer_option_feedbacks = $answer_option_feedbacks;
        return $obj;
    }

    public function __construct() {
        $this->answer_option_feedbacks = [];
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
    
    /**
     * @param int $option_id
     * @return bool
     */
    public function hasAnswerOptionFeedback(int $option_id) : bool {
        return array_key_exists($option_id, $this->answer_option_feedbacks);
    }
    
    public function getFeedbackForAnswerOption(int $option_id) : string {
        return $this->answer_option_feedbacks[$option_id];
    }

    public function equals(AbstractValueObject $other) : bool
    {
        /** @var Feedback $other */
        return (get_class($this) === get_class($other) &&
                $this->answer_correct_feedback === $other->answer_correct_feedback &&
                $this->answer_wrong_feedback === $other->answer_wrong_feedback &&
                $this->answer_option_feedback_mode === $other->answer_option_feedback_mode &&
                $this->answerOptionFeedbacksEqual($other->answer_option_feedbacks));
    }
    
    private function answerOptionFeedbacksEqual(array $other_options): bool {
        if (count($this->answer_option_feedbacks) !== count($other_options)) {
            return false;
        }
        
        foreach ($this->answer_option_feedbacks as $key => $value) {
            if (array_key_exists($key, $other_options)) {
                return false;
            }
            
            if ($this->answer_option_feedbacks[$key] !== $other_options[$key]) {
                return false;
            }
        }
        
        return true;
    }
}