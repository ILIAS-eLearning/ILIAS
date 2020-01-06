<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Page;

use ILIAS\UI\Implementation\Component\Link\Standard;
use ReflectionClass;
use ilAsqAnswerOptionFeedbackPageGUI;
use ilAsqQuestionFeedbackEditorGUI;

/**
 * Class AsqPageObject
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AsqPageObject extends \ilPageObject
{
    public $parent_type = 'asqq';

    /**
     * @return string parent type
     */
    public function getParentType() : string
    {
        return $this->parent_type;
    }

    /**
     * @return string parent type
     */
    public function setParentType($parent_type) : void
    {
        $this->parent_type = $parent_type;
    }

    /**
     * @param int $questionIntId
     *
     * @return string
     */
    public function getXMLContent($a_incl_head = false)
    {
        $xml = "<PageObject>";
        $xml .= "<PageContent>";
        $xml .= "<Question QRef=\"il__qst_{$this->getId()}\"/>";
        $xml .= "</PageContent>";
        $xml .= "</PageObject>";

        return $xml;
    }


    /**
     * @return string
     */
    public function getPageEditingLink():string {
        global $DIC;

        $DIC->ctrl()->setParameterByClass($DIC->ctrl()->getCmdClass(), 'page_type', $this->getParentType());
        $DIC->ctrl()->setParameterByClass($DIC->ctrl()->getCmdClass(), 'parent_int_id', $this->getParentId());
        $DIC->ctrl()->setParameterByClass($DIC->ctrl()->getCmdClass(), 'answer_option_int_id', $this->getId());
        $label = $DIC->language()->txt('asq_link_edit_feedback_page');

        //TODO
        $action = $DIC->ctrl()->getLinkTargetByClass([ilAsqQuestionFeedbackEditorGUI::class, ilAsqAnswerOptionFeedbackPageGUI::class], ilAsqAnswerOptionFeedbackPageGUI::CMD_EDIT);

        $link = new Standard($label,$action);

        return $DIC->ui()->renderer()->render($link);
    }
}