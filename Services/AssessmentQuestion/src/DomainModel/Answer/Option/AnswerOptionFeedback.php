<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\DomainModel\Answer\Option;

use ilAsqAnswerOptionFeedbackPageGUI;
use ilAsqGenericFeedbackPageGUI;
use ilAsqQuestionAuthoringGUI;
use ilAsqQuestionFeedbackEditorGUI;
use ilFormPropertyGUI;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOption;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\ImageAndTextDisplayDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\QuestionFeedbackFormGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\PageFactory;
use ILIAS\Services\AssessmentQuestion\DomainModel\Feedback\AnswerCorrectFeedback;
use ILIAS\UI\Implementation\Component\Link\Standard;
use ilRadioGroupInputGUI;
use ilRadioOption;
use JsonSerializable;
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
class AnswerOptionFeedback  implements JsonSerializable
{
    const VAR_FEEDBACK_FOR_ANSWER = "feedback_for_answer";

    /**
     * @var string
     */
    protected $answer_feedback;

    public function __construct(string $answer_feedback = "") {
        $this->answer_feedback = $answer_feedback;
    }


    /**
     * @return string
     */
    public function getAnswerFeedback() : string
    {
        return $this->answer_feedback;
    }


    public static function generateField(QuestionDto $question,AnswerOption $answer_option) : ilFormPropertyGUI
    {
        global $DIC;

        $arr_option = $answer_option->getDisplayDefinition()->getValues();
        $label = $arr_option[ImageAndTextDisplayDefinition::VAR_MCDD_TEXT];

        //TODO -> does not work!
        if (TRUE || $question->getContentEditingMode()->isRteTextarea()) {
            $answer_feedback = "";
            if (is_object($answer_option->getAnswerOptionFeedback())) {
                $answer_feedback = $answer_option->getAnswerOptionFeedback()->getAnswerFeedback();
            }

            return self::generateRteField($question, $label, self::VAR_FEEDBACK_FOR_ANSWER."[".$answer_option->getOptionId()."]", $answer_feedback);
        }

        //TODO -> does not work!
        if (FALSE && $question->getContentEditingMode()->isPageObject()) {
            return self::generatePageObjectField($question, $label, self::VAR_FEEDBACK_FOR_ANSWER);
        }
    }

    public static function generateRteField(QuestionDto $question, string $label, string $post_var, string $answer_feedback) : ilFormPropertyGUI {
        global $DIC;

        $feedback = new \ilTextAreaInputGUI($label, $post_var);
       // $feedback->setRequired(true);
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
        $DIC->ctrl()->setParameterByClass($DIC->ctrl()->getCmdClass(), 'parent_int_id',  $question->getId());
        $DIC->ctrl()->setParameterByClass($DIC->ctrl()->getCmdClass(), 'answer_option_int_id', $page_id);

        $action = $DIC->ctrl()->getLinkTargetByClass([ilAsqQuestionFeedbackEditorGUI::class,ilAsqAnswerOptionFeedbackPageGUI::class], ilAsqAnswerOptionFeedbackPageGUI::CMD_EDIT);

        $link = new Standard($label,$action);

        $feedback->setValue($DIC->ui()->renderer()->render($link));

        return $feedback;
    }


    public static function deserialize(stdClass $data)
    {
        return new AnswerOptionFeedback($data->answer_feedback);
    }

    public function getValues(): array {
            return [self::VAR_FEEDBACK_FOR_ANSWER => $this->answer_feedback];
    }

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


    public function jsonSerialize()
    {
        return get_object_vars($this);
    }


    /**
     * @param int $option_id
     *
     * @return AnswerOptionFeedback
     */
    public static function getValueFromPost(int $option_id)
    {
        return new AnswerOptionFeedback(
            strval($_POST[self::VAR_FEEDBACK_FOR_ANSWER][$option_id]));
    }
}