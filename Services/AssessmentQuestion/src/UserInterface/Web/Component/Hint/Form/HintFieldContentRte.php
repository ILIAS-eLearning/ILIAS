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
class HintFieldContentRte
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

        $field_content = new \ilTextAreaInputGUI($DIC->language()->txt('asq_question_hints_label_hint'), self::VAR_HINT_CONTENT_RTE);
        $field_content->setRequired(true);
        $field_content->setRows(10);
        //TODO FIXME POST IS EMPTY WITH RTE
        $field_content->setUseRte(true);
        $field_content->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("assessment"));
        $field_content->addPlugin("latex");
        $field_content->addButton("latex");
        $field_content->addButton("pastelatex");
        $field_content->setRTESupport(22,'asq' , "assessment");
        /*
       else
       {
           $property->setRteTags(\ilAssSelfAssessmentQuestionFormatter::getSelfAssessmentTags());
           $property->setUseTagsForRteOnly(false);
       }*/

        $field_content->setValue($this->content);


        return $field_content;
    }

    public static function getValueFromPost() {
        return filter_input(INPUT_POST, self::VAR_HINT_CONTENT_RTE);
    }
}