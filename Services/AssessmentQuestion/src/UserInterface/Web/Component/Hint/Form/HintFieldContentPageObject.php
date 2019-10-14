<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Hint\Form;

use ilFormPropertyGUI;
use ILIAS\AssessmentQuestion\DomainModel\Hint\Hint;
use ilNumberInputGUI;
use ilObjAdvancedEditing;

/**
 * Class HintPointsDeduction
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class HintFieldContentPageObject
{
    const VAR_HINT_CONTENT_RTE = "hint_content_rte";


    /**
     * HintFieldContentRte constructor.
     *
     * @param string $content
     */
    public function __construct(string $content, int $container_obj_id, string $container_obj_type) {
        $this->content = $content;
    }


    public function getField(): ilFormPropertyGUI {
        global $DIC;

        /*
        $field_content = new \ilTextAreaInputGUI($DIC->language()->txt('asq_question_hints_label_hint'), self::VAR_HINT_CONTENT_RTE);
        $field_content->setRequired(true);
        $field_content->setRows(10);
        //TODO FIXME POST IS EMPTY WITH RTE
        $field_content->setUseRte(true);
        $field_content->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("assessment"));
        $field_content->addPlugin("latex");
        $field_content->addButton("latex");
        $field_content->addButton("pastelatex");
        $field_content->setRTESupport(22,'asq' , "assessment");*/
        /*
       else
       {
           $property->setRteTags(\ilAssSelfAssessmentQuestionFormatter::getSelfAssessmentTags());
           $property->setUseTagsForRteOnly(false);
       }*/

        //$field_content->setValue($this->content);
/*HintFieldContentRte.php

global $DIC;
        $feedback = new \ilNonEditableValueGUI($label,'', true);

        $DIC->ctrl()->setParameterByClass($DIC->ctrl()->getCmdClass(), 'page_type', ilAsqAnswerOptionFeedbackPageGUI::PAGE_TYPE);
        $DIC->ctrl()->setParameterByClass($DIC->ctrl()->getCmdClass(), ilAsqQuestionAuthoringGUI::VAR_QUESTION_ID, $question->getId());
        $DIC->ctrl()->setParameterByClass($DIC->ctrl()->getCmdClass(), self::VAR_FEEDBACK_TYPE_INT_ID, $page_id);
        $action = $DIC->ctrl()->getLinkTargetByClass([AsqQuestionFeedbackEditorGUI::class,ilAsqGenericFeedbackPageGUI::class], ilAsqAnswerOptionFeedbackPageGUI::CMD_EDIT);

        $label = $DIC->language()->txt('asq_link_edit_feedback_page');

        $link = new Standard($label,$action);
        $feedback->setValue($DIC->ui()->renderer()->render($link));

        return $feedback;

        return $field_content;*/
    }

    public static function getValueFromPost() {
        return filter_input(INPUT_POST, self::VAR_HINT_CONTENT_RTE);
    }
}