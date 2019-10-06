<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Answer;

use ilAsqAnswerOptionFeedbackPageGUI;
use ilAsqGenericFeedbackPageGUI;
use ilAsqQuestionAuthoringGUI;
use ilAsqQuestionFeedbackEditorGUI;
use ILIAS\UI\Implementation\Component\Link\Standard;
use ilRadioGroupInputGUI;
use ilRadioOption;
use JsonSerializable;
use stdClass;

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
class AnswerFeedbackDefinition implements JsonSerializable {

    const VAR_FEEDBACK_TYPE_INT_ID = 'feedback_type_int_id';
    const VAR_FEEDBACK_CORRECT = 'feedback_correct';
    const VAR_FEEDBACK_WRONG = 'feedback_wrong';
    const GENERIC_FEEDBACK_TYPE_IDS = [self::VAR_FEEDBACK_CORRECT => 1, self::VAR_FEEDBACK_WRONG => 2];


	public static function getFields(string $question_id) : array {
	    global $DIC;

        $fields = [];

        foreach(self::GENERIC_FEEDBACK_TYPE_IDS as $key => $feedback_type_id) {
            $answer_specific_feedback = new \ilNonEditableValueGUI('test',$key, true);

            $DIC->ctrl()->setParameterByClass($DIC->ctrl()->getCmdClass(), 'page_type', ilAsqAnswerOptionFeedbackPageGUI::PAGE_TYPE);
            $DIC->ctrl()->setParameterByClass($DIC->ctrl()->getCmdClass(), ilAsqQuestionAuthoringGUI::VAR_QUESTION_ID, $question_id);
            $DIC->ctrl()->setParameterByClass($DIC->ctrl()->getCmdClass(), self::VAR_FEEDBACK_TYPE_INT_ID, $feedback_type_id);
            $action = $DIC->ctrl()->getLinkTargetByClass([ilAsqQuestionFeedbackEditorGUI::class,ilAsqGenericFeedbackPageGUI::class], ilAsqAnswerOptionFeedbackPageGUI::CMD_EDIT);

            $label = $DIC->language()->txt('asq_link_edit_feedback_page');

            $link = new Standard($label,$action);


            $answer_specific_feedback->setValue($DIC->ui()->renderer()->render($link));
            $fields[] = $answer_specific_feedback;
        }

        return $fields;
    }

	public function getValues() : array {

    }

	public static function getValueFromPost(string $index) {

    }

	public static function deserialize(stdClass $data) {

    }
	
	/**
	 * Specify data which should be serialized to JSON
	 *
	 * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize() {
	    return get_object_vars($this);
	}
	
	/**
	 * @return bool
	 */
	public static function checkInput(string $index) : bool {
	    return true;
	}
	
	/**
	 * @return string
	 */
	public static function getErrorMessage() : string {
	    return '';
	}
}