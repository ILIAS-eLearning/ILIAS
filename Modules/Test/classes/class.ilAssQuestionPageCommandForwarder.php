<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilTestCtrlForwarder
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test
 */
class ilAssQuestionPageCommandForwarder
{
    protected ?ilObjTest $testObj;

    protected \ILIAS\Test\InternalRequestService $testrequest;

    public function getTestObj() : ?ilObjTest
    {
        return $this->testObj;
    }

    public function setTestObj(ilObjTest $testObj) : void
    {
        $this->testObj = $testObj;
    }
    
    public function forward() : void
    {
        /* @var ILIAS\DI\Container $DIC */
        global $DIC;
        $ctrl = $DIC->ctrl();
        $main_template = $DIC->ui()->mainTemplate();
        $lng = $DIC->language();
        
        $this->testrequest = $DIC->test()->internal()->request();
        
        //echo $_REQUEST['prev_qid'];
        if ($this->testrequest->raw('prev_qid')) {
            $ctrl->setParameter($this, 'prev_qid', $this->testrequest->raw('prev_qid'));
        }
        
        $main_template->setCurrentBlock("ContentStyle");
        $main_template->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath(0)
        );
        $main_template->parseCurrentBlock();
        
        // syntax style
        $main_template->setCurrentBlock("SyntaxStyle");
        $main_template->setVariable(
            "LOCATION_SYNTAX_STYLESHEET",
            ilObjStyleSheet::getSyntaxStylePath()
        );
        $main_template->parseCurrentBlock();
        $q_gui = assQuestionGUI::_getQuestionGUI("", $this->testrequest->getQuestionId());

        $q_gui->setRenderPurpose(assQuestionGUI::RENDER_PURPOSE_PREVIEW);
        $q_gui->setQuestionTabs();
        $q_gui->outAdditionalOutput();
        $q_gui->object->setObjId($this->getTestObj()->getId());
        $question = &$q_gui->object;
 
        if ($ctrl->getCmd() === 'edit' && $question->isInActiveTest()) {
            $main_template->setOnScreenMessage('failure', $lng->txt("question_is_part_of_running_test"));
            $ctrl->redirectByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
        }

        $ctrl->saveParameter($this, "q_id");
        $lng->loadLanguageModule("content");
        $ctrl->setReturnByClass("ilAssQuestionPageGUI", "view");
        $ctrl->setReturnByClass("ilObjTestGUI", "questions");
        $page_gui = new ilAssQuestionPageGUI($this->testrequest->getQuestionId());

        $page_gui->setEditPreview(true);
        if (strlen($ctrl->getCmd()) == 0) {
            $ctrl->setCmdClass(get_class($page_gui));
            $ctrl->setCmd("preview");
        }
        $page_gui->setQuestionHTML(array($q_gui->object->getId() => $q_gui->getPreview(true)));
        $page_gui->setTemplateTargetVar("ADM_CONTENT");
        $page_gui->setOutputMode($this->getTestObj()->evalTotalPersons() == 0 ? "edit" : 'preview');
        $page_gui->setHeader($question->getTitle());
        $page_gui->setPresentationTitle($question->getTitle() . ' [' . $lng->txt('question_id_short') . ': ' . $question->getId() . ']');
        
        $html = $ctrl->forwardCommand($page_gui);
        $main_template->setContent($html);
    }
}
