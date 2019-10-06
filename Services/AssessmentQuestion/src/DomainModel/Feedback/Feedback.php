<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\AssessmentQuestion\DomainModel\Feedback;



use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptionFeedbackModeDefinition;
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
    const ANSWER_OPTION_FEEDBACK_MODE_DEFINITION = "aofmdclass";

    /**
     * @var AnswerOptionFeedbackModeDefinition
     */
    protected $answer_option_feedback_mode_setting;

    public function __construct(AnswerOptionFeedbackModeDefinition $answer_option_feedback_mode_setting) {
        $this->answer_option_feedback_mode_setting = $answer_option_feedback_mode_setting;
    }


    /**
     * @return int
     */
    public function getAnswerOptionFeedbackModeSetting() : AnswerOptionFeedbackModeDefinition
    {
        return $this->answer_option_feedback_mode_setting;
    }


    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        $vars[self::ANSWER_OPTION_FEEDBACK_MODE_DEFINITION] = get_class($this->answer_option_feedback_mode_setting);
        return $vars;
    }


    public function equals(AbstractValueObject $other) : bool {
        if (get_class($this->answer_option_feedback_mode_setting) !== get_class($other->answer_option_feedback_mode_setting))
        {
            return false;
        }

        /*$my_values = $this->rawValues();
        $other_values = $other->rawValues();

        foreach ($my_values as $key => $value)
        {
            if ($my_values[$key] !== $other_values[$key])
            {
                return false;
            }
        }*/

        return true;
    }

    public static function deserialize(?string $json_data) : ?AbstractValueObject {
        $data = json_decode($json_data);

        $feedback = new Feedback(new AnswerOptionFeedbackModeDefinition($data->answer_option_feedback_mode_setting->answer_option_feedback_mode_setting));


        return $feedback;
    }


}