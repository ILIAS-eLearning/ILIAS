<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Answer;

use ilAsqAnswerOptionFeedbackPageGUI;
use ilAsqGenericFeedbackPageGUI;
use ilAsqQuestionAuthoringGUI;
use ilAsqQuestionFeedbackEditorGUI;
use ilFormPropertyGUI;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\UI\Implementation\Component\Link\Standard;
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
Abstract class AnswerFeedback extends AbstractValueObject implements JsonSerializable {

    const VAR_ANSWER_FEEDBACK_CORRECT = 'answer_feedback_correct';
    const VAR_ANSWER_FEEDBACK_WRONG = 'answer_feedback_wrong';
    const VAR_ANSWER_FEEDBACK_CORRECT_PAGE_ID = 1;
    const VAR_ANSWER_FEEDBACK_WRONG_PAGE_ID = 2;
    const VAR_FEEDBACK_TYPE_INT_ID = 'feedback_type_int_id';

    /**
     * @var string
     */
    protected $answer_feedback;

    public function __construct(string $answer_feedback = "") {
        $this->answer_feedback = $answer_feedback;
    }

    abstract public static function generateField(QuestionDto $question) : ilFormPropertyGUI;


    public static function generateRteField(QuestionDto $question, string $label, string $post_var, string $answer_feedback) : ilFormPropertyGUI {
        global $DIC;

        $feedback = new \ilTextAreaInputGUI($label, $post_var);
        $feedback->setRequired(true);
        $feedback->setRows(10);
        //TODO FIXME POST IS EMPTY WITH RTE
        /*$feedback->setUseRte(true);
        $feedback->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("assessment"));
        $feedback->addPlugin("latex");
        $feedback->addButton("latex");
        $feedback->addButton("pastelatex");
        $feedback->setRTESupport($question->getQuestionIntId(), $question->getIlComponentid(), "assessment");*/
        /*
       else
       {
           $property->setRteTags(\ilAssSelfAssessmentQuestionFormatter::getSelfAssessmentTags());
           $property->setUseTagsForRteOnly(false);
       }*/

        $feedback->setValue($answer_feedback);

        return $feedback;
    }

    public static function generatePageObjectField(QuestionDto $question, string $label, int $page_id) : ilFormPropertyGUI {
        global $DIC;
        $feedback = new \ilNonEditableValueGUI($label,'', true);

        $DIC->ctrl()->setParameterByClass($DIC->ctrl()->getCmdClass(), 'page_type', ilAsqAnswerOptionFeedbackPageGUI::PAGE_TYPE);
        $DIC->ctrl()->setParameterByClass($DIC->ctrl()->getCmdClass(), ilAsqQuestionAuthoringGUI::VAR_QUESTION_ID, $question->getId());
        $DIC->ctrl()->setParameterByClass($DIC->ctrl()->getCmdClass(), self::VAR_FEEDBACK_TYPE_INT_ID, $page_id);
        $action = $DIC->ctrl()->getLinkTargetByClass([ilAsqQuestionFeedbackEditorGUI::class,ilAsqGenericFeedbackPageGUI::class], ilAsqAnswerOptionFeedbackPageGUI::CMD_EDIT);

        $label = $DIC->language()->txt('asq_link_edit_feedback_page');

        $link = new Standard($label,$action);
        $feedback->setValue($DIC->ui()->renderer()->render($link));

        return $feedback;
    }

    public function getAnswerFeedback() : string {
        return $this->answer_feedback;
    }


    abstract public static function getValueFromPost();


    function equals(AbstractValueObject $other) : bool
    {
        if (get_class($this) !== get_class($other))
        {
            return false;
        }

        if($this->getAnswerFeedback() !== $other->getAnswerFeedback()) {
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
    public function jsonSerialize() {
        return get_object_vars($this);
    }

    /**
     * @return bool
     */
    public static function checkInput() : bool {
        return true;
    }

    /**
     * @return string
     */
    public static function getErrorMessage() : string {
        return '';
    }
}