<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Hint;

use ilFormPropertyGUI;
use ilHiddenInputGUI;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\UI\Implementation\Component\Button\Standard;
use ilNumberInputGUI;
use JsonSerializable;
use stdClass;

/**
 * Interface Hint
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class Hint implements JsonSerializable
{
    const VAR_HINT_ORDER_NUMBER = "hint_order_number";
    const VAR_HINT_CONTENT = "hint_content";
    const VAR_HINT_POINTS_DEDUCTION = "hint_points_deuction";

    /**
     * @var integer
     */
    private $order_number;
    /**
     * @var string
     */
    private $content;
    /**
     * @var float
     */
    private $point_deduction;


    public function __construct(int $order_number, string $content, float $point_deduction)
    {
        $this->order_number = $order_number;
        $this->content = $content;
        $this->point_deduction = $point_deduction;
    }


    /**
     * @return int
     */
    public function getOrderNumber() : int
    {
        return $this->order_number;
    }


    /**
     * @return string
     */
    public function getContent() : string
    {
        return $this->content;
    }


    /**
     * @return float
     */
    public function getPointDeduction() : float
    {
        return $this->point_deduction;
    }


    public function equals(Hint $other) : bool
    {
        if ($this->order_number !== $other->order_number
            || $this->content !== $other->content
            || $this->point_deduction !== $other->point_deduction
        ) {
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
    public function jsonSerialize()
    {
        $vars = get_object_vars($this);

        return $vars;
    }


    public function deserialize(stdClass $data)
    {
        return new Hint(
            $data->order_number,
            $data->content,
            $data->point_deduction);
    }


    /**
     * @param QuestionDto $question
     * @param Hint        $hint
     *
     * @return ilFormPropertyGUI[]
     */
    public static function generateField(QuestionDto $question,Hint $hint) : array
    {
        global $DIC;

        $fields = [];
        $label = $DIC->language()->txt('asq_question_hints_label_hint');

        $order_number = new ilHiddenInputGUI(self::VAR_HINT_ORDER_NUMBER);
        $order_number->setValue($hint->getOrderNumber());

        //TODO -> does not work!
        if (TRUE || $question->getContentEditingMode()->isRteTextarea()) {
            $answer_feedback = "";

            $fields[] = self::generateRteField($question, $label, self::VAR_HINT_CONTENT,$hint);
        }

        //TODO -> does not work!
        if (FALSE && $question->getContentEditingMode()->isPageObject()) {
           // $fields[] = self::generatePageObjectField($question, $label, $page_id, self::VAR_HINT_CONTENT,$hint);
        }

        $point_deduction = new ilNumberInputGUI($DIC->language()->txt('asq_question_hints_label_points_deduction'));
       // $point_deduction->setRequired(true);
        $point_deduction->setSize(2);
        $point_deduction->setValue($hint->getPointDeduction());

        $fields[] = $point_deduction;
        return $fields;
    }


    public static function generateRteField(QuestionDto $question, string $label, string $post_var, Hint $hint) : ilFormPropertyGUI {
        $content = new \ilTextAreaInputGUI($label, $post_var);
        $content->setRequired(true);
        $content->setRows(10);
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

        $content->setValue($hint->getContent());

        return $content;
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

    public static function getValueFromPost():Hint {
        return new Hint( intval(filter_input(INPUT_POST, self::VAR_HINT_CONTENT, FILTER_VALIDATE_INT)),
                         strval(filter_input(INPUT_POST, self::VAR_HINT_CONTENT, FILTER_SANITIZE_STRING)),
                         intval(filter_input(INPUT_POST, self::VAR_HINT_POINTS_DEDUCTION, FILTER_VALIDATE_INT)));
    }



}
