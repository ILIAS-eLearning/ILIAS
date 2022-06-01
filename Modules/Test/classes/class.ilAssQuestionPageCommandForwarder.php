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
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $this->testrequest = $DIC->test()->internal()->request();
        require_once "./Modules/TestQuestionPool/classes/class.ilAssQuestionPageGUI.php";
        //echo $_REQUEST['prev_qid'];
        if ($this->testrequest->raw('prev_qid')) {
            $DIC->ctrl()->setParameter($this, 'prev_qid', $this->testrequest->raw('prev_qid'));
        }
        
        //global $___test_express_mode;
        //$___test_express_mode = true;
        $_GET['calling_test'] = $this->getTestObj()->getRefId();
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        $DIC->ui()->mainTemplate()->setCurrentBlock("ContentStyle");
        $DIC->ui()->mainTemplate()->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath(0)
        );
        $DIC->ui()->mainTemplate()->parseCurrentBlock();
        
        // syntax style
        $DIC->ui()->mainTemplate()->setCurrentBlock("SyntaxStyle");
        $DIC->ui()->mainTemplate()->setVariable(
            "LOCATION_SYNTAX_STYLESHEET",
            ilObjStyleSheet::getSyntaxStylePath()
        );
        $DIC->ui()->mainTemplate()->parseCurrentBlock();
        require_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
        $q_gui = assQuestionGUI::_getQuestionGUI("", $this->testrequest->getQuestionId());
        $q_gui->setRenderPurpose(assQuestionGUI::RENDER_PURPOSE_PREVIEW);
        $q_gui->setQuestionTabs();
        $q_gui->outAdditionalOutput();
        $q_gui->object->setObjId($this->getTestObj()->getId());
        $question = &$q_gui->object;
        $DIC->ctrl()->saveParameter($this, "q_id");
        $DIC->language()->loadLanguageModule("content");
        $DIC->ctrl()->setReturnByClass("ilAssQuestionPageGUI", "view");
        $DIC->ctrl()->setReturnByClass("ilObjTestGUI", "questions");
        $page_gui = new ilAssQuestionPageGUI($this->testrequest->getQuestionId());
        $page_gui->setEditPreview(true);
        if (strlen($DIC->ctrl()->getCmd()) == 0) {
            $DIC->ctrl()->setCmdClass(get_class($page_gui));
            $DIC->ctrl()->setCmd("preview");
        }
        $page_gui->setQuestionHTML(array($q_gui->object->getId() => $q_gui->getPreview(true)));
        $page_gui->setTemplateTargetVar("ADM_CONTENT");
        $page_gui->setOutputMode($this->getTestObj()->evalTotalPersons() == 0 ? "edit" : 'preview');
        $page_gui->setHeader($question->getTitle());
        $page_gui->setPresentationTitle($question->getTitle() . ' [' . $DIC->language()->txt('question_id_short') . ': ' . $question->getId() . ']');
        
        $html = $DIC->ctrl()->forwardCommand($page_gui);
        $DIC->ui()->mainTemplate()->setContent($html);
    }
}
