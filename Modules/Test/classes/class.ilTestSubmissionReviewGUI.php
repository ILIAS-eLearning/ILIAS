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
 * Class ilTestSubmissionReviewGUI
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ctrl_calls 	  ilTestSubmissionReviewGUI: ilAssQuestionPageGUI
 */
class ilTestSubmissionReviewGUI extends ilTestServiceGUI
{
    /** @var ilTestOutputGUI */
    protected $testOutputGUI = null;

    /** @var \ilTestSession */
    protected $testSession;

    public function __construct(ilTestOutputGUI $testOutputGUI, ilObjTest $testOBJ, ilTestSession $testSession)
    {
        $this->testOutputGUI = $testOutputGUI;
        $this->testSession = $testSession;

        parent::__construct($testOBJ);
    }

    public function executeCommand(): string
    {
        if (!$this->object->getEnableExamview()) {
            return '';
        }

        switch ($this->ctrl->getNextClass($this)) {
            default:
                $this->dispatchCommand();
                break;
        }

        return '';
    }

    protected function dispatchCommand()
    {
        switch ($this->ctrl->getCmd()) {
            case 'show':
            default:

                $this->show();
        }
    }

    /**
     * Returns the name of the current content block (depends on the kiosk mode setting)
     *
     * @return string The name of the content block
     * @access public
     */
    private function getContentBlockName(): string
    {
        if ($this->object->getKioskMode()) {
            // See: https://mantis.ilias.de/view.php?id=27784
            //$this->tpl->setBodyClass("kiosk");
            $this->tpl->hideFooter();
            return "CONTENT";
        } else {
            return "ADM_CONTENT";
        }
    }

    /**
     * @return ilToolbarGUI
     */
    protected function buildToolbar($toolbarId): ilToolbarGUI
    {
        $toolbar = new ilToolbarGUI();
        $toolbar->setId($toolbarId);

        $backUrl = $this->ctrl->getLinkTarget(
            $this->testOutputGUI,
            $this->object->getListOfQuestionsEnd() ?
            ilTestPlayerCommands::QUESTION_SUMMARY : ilTestPlayerCommands::BACK_FROM_FINISHING
        );

        $button = ilLinkButton::getInstance();
        $button->setCaption('btn_previous');
        $button->setUrl($backUrl);
        $toolbar->addButtonInstance($button);

        $this->ctrl->setParameter($this->testOutputGUI, 'reviewed', 1);
        $nextUrl = $this->ctrl->getLinkTarget($this->testOutputGUI, ilTestPlayerCommands::FINISH_TEST);
        $this->ctrl->setParameter($this->testOutputGUI, 'reviewed', 0);

        $button = ilLinkButton::getInstance();
        $button->setPrimary(true);
        $button->setCaption('btn_next');
        $button->setUrl($nextUrl);
        $toolbar->addButtonInstance($button);

        return $toolbar;
    }

    protected function buildUserReviewOutput(): string
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $ilObjDataCache = $DIC['ilObjDataCache'];

        $testResultHeaderLabelBuilder = new ilTestResultHeaderLabelBuilder($this->lng, $ilObjDataCache);

        $objectivesList = null;

        if ($this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
            $testSequence = $this->testSequenceFactory->getSequenceByActiveIdAndPass($this->testSession->getActiveId(), $this->testSession->getPass());
            $testSequence->loadFromDb();
            $testSequence->loadQuestions();

            $objectivesAdapter = ilLOTestQuestionAdapter::getInstance($this->testSession);

            $objectivesList = $this->buildQuestionRelatedObjectivesList($objectivesAdapter, $testSequence);
            $objectivesList->loadObjectivesTitles();

            $testResultHeaderLabelBuilder->setObjectiveOrientedContainerId($this->testSession->getObjectiveOrientedContainerId());
            $testResultHeaderLabelBuilder->setUserId($this->testSession->getUserId());
            $testResultHeaderLabelBuilder->setTestObjId($this->object->getId());
            $testResultHeaderLabelBuilder->setTestRefId($this->object->getRefId());
            $testResultHeaderLabelBuilder->initObjectiveOrientedMode();
        }

        $results = $this->object->getTestResult(
            $this->testSession->getActiveId(),
            $this->testSession->getPass(),
            false,
            !$this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()
        );

        $testevaluationgui = new ilTestEvaluationGUI($this->object);
        $testevaluationgui->setContextResultPresentation(false);

        $results_output = $testevaluationgui->getPassListOfAnswers(
            $results,
            $this->testSession->getActiveId(),
            $this->testSession->getPass(),
            false,
            false,
            false,
            false,
            false,
            $objectivesList,
            $testResultHeaderLabelBuilder
        );

        return $results_output;
    }

    protected function show()
    {
        $html = $this->buildToolbar('review_nav_top')->getHTML();
        $html .= $this->buildUserReviewOutput() . '<br />';
        $html .= $this->buildToolbar('review_nav_bottom')->getHTML();

        if ($this->object->isShowExamIdInTestPassEnabled() && !$this->object->getKioskMode()) {
            $examIdTpl = new ilTemplate("tpl.exam_id_block.html", true, true, 'Modules/Test');
            $examIdTpl->setVariable('EXAM_ID_VAL', ilObjTest::lookupExamId(
                $this->testSession->getActiveId(),
                $this->testSession->getPass(),
                $this->object->getId()
            ));
            $examIdTpl->setVariable('EXAM_ID_TXT', $this->lng->txt('exam_id'));
            $html .= $examIdTpl->get();
        }

        $this->tpl->setVariable(
            $this->getContentBlockName(),
            $html
        );
    }
}
