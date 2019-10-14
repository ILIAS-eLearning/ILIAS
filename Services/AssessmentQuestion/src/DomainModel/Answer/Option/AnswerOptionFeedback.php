<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\DomainModel\Answer\Option;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\ImageAndTextDisplayDefinition;
use ILIAS\UI\Implementation\Component\Link\Standard;
use JsonSerializable;
use ilAsqAnswerOptionFeedbackPageGUI;
use AsqQuestionFeedbackEditorGUI;
use ilFormPropertyGUI;
use stdClass;

/**
 * Class AnswerOptionFeedback
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AnswerOptionFeedback implements JsonSerializable
{

    const VAR_FEEDBACK_FOR_ANSWER = "feedback_for_answer";
    /**
     * @var string
     */
    protected $answer_feedback;


    public function __construct(? string $answer_feedback = "")
    {
        $this->answer_feedback = $answer_feedback;
    }


    /**
     * @return string
     */
    public function getAnswerFeedback() : ?string
    {
        return $this->answer_feedback;
    }


    public static function deserialize(stdClass $data)
    {
        return new AnswerOptionFeedback($data->answer_feedback);
    }


    public function getValues() : array
    {
        return [self::VAR_FEEDBACK_FOR_ANSWER => $this->answer_feedback];
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


    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}