<?php

namespace ILIAS\Services\AssessmentQuestion\DomainModel\Feedback;

use ilFormPropertyGUI;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\Answer\AnswerFeedback;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;

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
class AnswerCorrectFeedback extends AnswerFeedback
{

    public static function generateField(QuestionDto $question) : ilFormPropertyGUI
    {
        global $DIC;

        $label = $DIC->language()->txt('asq_input_feedback_correct');

        //TODO -> does not work!
        if (TRUE || $question->getContentEditingMode()->isRteTextarea()) {
            $answer_feedback = "";
            if (is_object($question->getFeedback()->getAnswerCorrectFeedback())) {
                $answer_feedback = $question->getFeedback()->getAnswerCorrectFeedback()->getAnswerFeedback();
            }

            return self::generateRteField($question, $label, self::VAR_ANSWER_FEEDBACK_CORRECT, $answer_feedback);
        }

        //TODO -> does not work!
        if (FALSE && $question->getContentEditingMode()->isPageObject()) {
            return self::generatePageObjectField($question, $label, self::VAR_ANSWER_FEEDBACK_CORRECT_PAGE_ID);
        }
    }

    public static function getValueFromPost()
    {
        return new AnswerCorrectFeedback(
            strval($_POST[self::VAR_ANSWER_FEEDBACK_CORRECT]));
    }


    public static function deserialize(?string $data) : ?AbstractValueObject
    {
        return new AnswerCorrectFeedback($data->answer_feedback);
    }
}