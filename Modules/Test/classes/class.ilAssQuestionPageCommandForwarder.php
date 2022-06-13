<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    /**
     * @var ilObjTest
     */
    protected $testObj;

    /**
     * @return ilObjTest
     */
    public function getTestObj()
    {
        return $this->testObj;
    }
    
    /**
     * @param ilObjTest $testObj
     */
    public function setTestObj($testObj)
    {
        $this->testObj = $testObj;
    }
    
    public function forward()
    {
        global $DIC;
        $ctrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ui = $DIC->ui()->mainTemplate();
        
        $q_gui = assQuestionGUI::_getQuestionGUI("", $_GET["q_id"]);
        $q_gui->setRenderPurpose(assQuestionGUI::RENDER_PURPOSE_PREVIEW);
        $q_gui->setQuestionTabs();
        $q_gui->outAdditionalOutput();
        $q_gui->object->setObjId($this->getTestObj()->getId());
        $question = &$q_gui->object;
        
        
        if ($ctrl->getCmd() === 'edit' && $question->isInActiveTest()) {
            ilUtil::sendFailure($lng->txt("question_is_part_of_running_test"), true);
            $ctrl->redirectByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
        }
        
        if ($_REQUEST['prev_qid']) {
            $ctrl->setParameter($this, 'prev_qid', $_REQUEST['prev_qid']);
        }
        
        $_GET['calling_test'] = $this->getTestObj()->getRefId();
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        $ui->setCurrentBlock("ContentStyle");
        $ui->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath(0)
        );
        $ui->parseCurrentBlock();
        
        // syntax style
        $ui->setCurrentBlock("SyntaxStyle");
        $ui->setVariable(
            "LOCATION_SYNTAX_STYLESHEET",
            ilObjStyleSheet::getSyntaxStylePath()
        );
        $ui->parseCurrentBlock();
        $ctrl->saveParameter($this, "q_id");
        $lng->loadLanguageModule("content");
        $ctrl->setReturnByClass("ilAssQuestionPageGUI", "view");
        $ctrl->setReturnByClass("ilObjTestGUI", "questions");
        $page_gui = new ilAssQuestionPageGUI($_GET["q_id"]);
        $page_gui->setEditPreview(true);
        if (strlen($ctrl->getCmd()) == 0) {
            $ctrl->setCmdClass(get_class($page_gui));
            $ctrl->setCmd("preview");
        }
        $page_gui->setQuestionHTML(array($q_gui->object->getId() => $q_gui->getPreview(true)));
        $page_gui->setTemplateTargetVar("ADM_CONTENT");
        $page_gui->setOutputMode($this->getTestObj()->evalTotalPersons() == 0 ? "edit" : 'preview');
        $page_gui->setHeader($question->getTitle());
        $page_gui->setPresentationTitle($question->getTitle() . ' [' . $DIC->language()->txt('question_id_short') . ': ' . $question->getId() . ']');
        
        $html = $ctrl->forwardCommand($page_gui);
        $ui->setContent($html);
    }
}
