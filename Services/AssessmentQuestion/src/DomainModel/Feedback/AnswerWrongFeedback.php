<?php

namespace ILIAS\Services\AssessmentQuestion\DomainModel\Feedback;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\Answer\AnswerFeedback;
use ilFormPropertyGUI;

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
class AnswerWrongFeedback  extends AnswerFeedback {

    public static function generateField(QuestionDto $question) : ilFormPropertyGUI
    {
        global $DIC;

        $label = $DIC->language()->txt('asq_input_feedback_wrong');

        //TODO -> does not work!
        if (TRUE || $question->getContentEditingMode()->isRteTextarea()) {
            $answer_feedback = "";
            if (is_object($question->getFeedback()->getAnswerWrongFeedback())) {
                $answer_feedback = $question->getFeedback()->getAnswerWrongFeedback()->getAnswerFeedback();
            }

            return self::generateRteField($question, $label, self::VAR_ANSWER_FEEDBACK_WRONG, $answer_feedback);
        }

        //TODO -> does not work!
        if (FALSE && $question->getContentEditingMode()->isPageObject()) {
            return self::generatePageObjectField($question, $label, self::VAR_ANSWER_FEEDBACK_WRONG_PAGE_ID);
        }
    }


    public static function getValueFromPost() {
        return new AnswerWrongFeedback(
            strval($_POST[self::VAR_ANSWER_FEEDBACK_WRONG]));
    }


    public static function deserialize(?string $data) : ?AbstractValueObject {
        return new AnswerWrongFeedback($data->answer_feedback);
    }
}