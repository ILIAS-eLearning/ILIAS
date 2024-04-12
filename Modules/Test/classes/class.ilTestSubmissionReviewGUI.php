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

declare(strict_types=1);

use ILIAS\UI\Component\Modal\Interruptive as InterruptiveModal;

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
    private ?InterruptiveModal $finish_test_modal = null;

    public function __construct(
        protected ilTestOutputGUI $test_output_gui,
        ilObjTest $testOBJ,
        protected ilTestSession $testSession
    ) {
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

        $back_url = $this->ctrl->getLinkTarget(
            $this->test_output_gui,
            $this->object->getListOfQuestionsEnd() ?
            ilTestPlayerCommands::QUESTION_SUMMARY : ilTestPlayerCommands::BACK_FROM_FINISHING
        );

        $toolbar->addComponent(
            $this->ui_factory->button()->standard($this->lng->txt('tst_resume_test'), $back_url)
        );

        if ($this->finish_test_modal === null) {
            $class = get_class($this->test_output_gui);
            $this->ctrl->setParameterByClass($class, 'reviewed', 1);
            $this->finish_test_modal = $this->test_output_gui->buildFinishTestModal();
            $this->ctrl->setParameterByClass($class, 'reviewed', 0);
        }

        $toolbar->addComponent(
            $this->ui_factory->button()->primary($this->lng->txt('finish_test'), $this->finish_test_modal->getShowSignal())
        );

        return $toolbar;
    }

    protected function buildUserReviewOutput(): string
    {
        $testResultHeaderLabelBuilder = new ilTestResultHeaderLabelBuilder($this->lng, $this->obj_cache);

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
        $this->global_screen->tool()->context()->current()->getAdditionalData()->replace(
            ilTestPlayerLayoutProvider::TEST_PLAYER_VIEW_TITLE,
            $this->object->getTitle() . ' - ' . $this->lng->txt('tst_results_overview')
        );

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

        $html .= $this->ui_renderer->render($this->finish_test_modal);

        $this->tpl->setVariable(
            $this->getContentBlockName(),
            $html
        );
    }
}
