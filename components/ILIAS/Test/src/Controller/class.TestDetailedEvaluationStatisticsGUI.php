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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Test\RequestDataCollector;
use ILIAS\Test\Table\DetailedEvaluationStatisticsTable;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

/**
 * @author Matheus Zych <mzych@databay.de>
 */
class TestDetailedEvaluationStatisticsGUI
{
    public const CMD_DETAILED_EVALUATION = 'detailedEvaluation';

    public function __construct(
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly ilCtrl $ctrl,
        private readonly ilLanguage $lng,
        private readonly ilTabsGUI $tabs,
        private readonly UIFactory $uiFactory,
        private readonly UIRenderer $uiRenderer,
        private readonly ilObjTest $testObject,
        private readonly RequestDataCollector $testRequest,
        private readonly ilTestAccess $testAccess,
        private readonly ilToolbarGUI $toolbar,
        private readonly GlobalHttpState $httpState
    ) {
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand(): bool
    {
        switch (strtolower((string) $this->ctrl->getNextClass($this))) {
            case strtolower(__CLASS__):
            case '':
                $cmd = $this->ctrl->getCmd() . 'Cmd';
                return $this->$cmd();
            default:
                $this->ctrl->setReturn($this, self::CMD_DETAILED_EVALUATION);
                return false;
        }
    }

    /**
     * @throws ilCtrlException
     * @throws ilDateTimeException
     */
    public function detailedEvaluationCmd(): bool
    {
        if (!$this->testAccess->checkStatisticsAccess()) {
            ilObjTestGUI::accessViolationRedirect();
        }

        $this->tabs->activateTab(ilTestTabsManager::TAB_ID_STATISTICS);
        $activeId = $this->testRequest->int('active_id');

        if (!$this->testAccess->checkResultsAccessForActiveId($activeId, $this->testObject->getTestId())) {
            ilObjTestGUI::accessViolationRedirect();
        }

        if ($activeId === 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('detailed_evaluation_missing_active_id'), true);
            $this->ctrl->redirectByClass(ilTestEvaluationGUI::class, ilTestEvaluationGUI::CMD_OUT_EVALUATION);
        }

        $this->tpl->addCss(ilUtil::getStyleSheetLocation('output', 'test_print.css'), 'print');

        $backBtn = $this->uiFactory->button()->standard(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTargetByClass(
                ilTestEvaluationGUI::class,
                ilTestEvaluationGUI::CMD_OUT_EVALUATION
            )
        );
        $this->toolbar->addComponent($backBtn);

        $this->testObject->setAccessFilteredParticipantList($this->testObject->buildStatisticsAccessFilteredParticipantList());

        $data = $this->testObject->getCompleteEvaluationData();
        $this->tpl->setContent($this->getForm($data, $activeId)->getHTML() . implode('', $this->getTables($data, $activeId)));
        return true;
    }

    /**
     * @throws ilCtrlException
     */
    private function getTables(ilTestEvaluationData $data, int $activeId): array
    {
        $tables = [];

        for ($pass = 0; $pass <= $data->getParticipant($activeId)->getLastPass(); $pass++) {
            if (ilObjTest::lookupPassResultsUpdateTimestamp($activeId, $pass) > 0) {
                if (($this->testAccess->getAccess()->checkAccess('write', '', $this->testRequest->getRefId()))) {
                    $this->ctrl->setParameter($this, 'statistics', '1');
                    $this->ctrl->setParameter($this, 'active_id', $activeId);
                    $this->ctrl->setParameter($this, 'pass', $pass);
                } else {
                    $this->ctrl->setParameter($this, 'statistics', '');
                    $this->ctrl->setParameter($this, 'active_id', '');
                    $this->ctrl->setParameter($this, 'pass', '');
                }

                $tableHtml = $this->uiRenderer->render(
                    $this
                        ->getTable($activeId, $data, $pass)
                        ->getComponent()
                        ->withRequest($this->httpState->request())
                );

                if (($this->testAccess->getAccess()->checkAccess('write', '', $this->testRequest->getRefId()))) {
                    $this->ctrl->setParameterByClass(ilTestEvaluationGUI::class, 'pass', $pass);

                    $button = $this->uiRenderer->render(
                        $this->uiFactory->button()->standard(
                            $this->lng->txt('tst_show_answer_sheet'),
                            $this->ctrl->getLinkTargetByClass(
                                ilTestEvaluationGUI::class,
                                ilTestEvaluationGUI::CMD_OUT_PARTICIPANTS_PASS_DETAILS
                            )
                        )
                    );

                    $tables[] = $tableHtml . '<br>' . $button;
                    continue;
                }

                $tables[] = $tableHtml;
            }
        }

        return $tables;
    }

    private function getTable(int $activeId, ilTestEvaluationData $data, int $pass): DetailedEvaluationStatisticsTable
    {
        return new DetailedEvaluationStatisticsTable(
            $this->lng,
            $this->uiFactory,
            $activeId,
            $data,
            $pass
        );
    }

    /**
     * @throws ilDateTimeException
     */
    private function getForm(ilTestEvaluationData $data, int $activeId): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle(sprintf(
            $this->lng->txt('detailed_evaluation_for'),
            $data->getParticipant($activeId)->getName(),
        ));

        $resultPoints = new ilNonEditableValueGUI($this->lng->txt('tst_stat_result_resultspoints'));
        $resultPoints->setValue($data->getParticipant($activeId)->getReached() . ' ' . strtolower($this->lng->txt('of')) . ' ' . $data->getParticipant($activeId)->getMaxpoints() . ' (' . sprintf('%2.2f', $data->getParticipant($activeId)->getReachedPointsInPercent()) . ' %' . ')');
        $form->addItem($resultPoints);

        if ($data->getParticipant($activeId)->getMark() !== '') {
            $resultMarks = new ilNonEditableValueGUI($this->lng->txt('tst_stat_result_resultsmarks'));
            $resultMarks->setValue($data->getParticipant($activeId)->getMark());
            $form->addItem($resultMarks);
        }

        if ($this->testObject->isOfferingQuestionHintsEnabled()) {
            $requestHints = new ilNonEditableValueGUI($this->lng->txt('tst_question_hints_requested_hint_count_header'));
            $requestHints->setValue($data->getParticipant($activeId)->getRequestedHintsCountFromScoredPass());
            $form->addItem($requestHints);
        }

        $timeSeconds = $data->getParticipant($activeId)->getTimeOfWork();
        $atimeSeconds = $data->getParticipant($activeId)->getNumberOfQuestions() ? $timeSeconds / $data->getParticipant($activeId)->getNumberOfQuestions() : 0;
        $timeHours = floor($timeSeconds / 3600);
        $timeSeconds -= $timeHours * 3600;
        $timeMinutes = floor($timeSeconds / 60);
        $timeSeconds -= $timeMinutes * 60;
        $timeOfWork = new ilNonEditableValueGUI($this->lng->txt('tst_stat_result_timeofwork'));
        $timeOfWork->setValue(sprintf('%02d:%02d:%02d', $timeHours, $timeMinutes, $timeSeconds));
        $form->addItem($timeOfWork);

        $this->tpl->setVariable('TXT_ATIMEOFWORK', $this->lng->txt(''));
        $timeHours = floor($atimeSeconds / 3600);
        $atimeSeconds -= $timeHours * 3600;
        $timeMinutes = floor($atimeSeconds / 60);
        $atimeSeconds -= $timeMinutes * 60;
        $avgTimeOfWork = new ilNonEditableValueGUI($this->lng->txt('tst_stat_result_atimeofwork'));
        $avgTimeOfWork->setValue(sprintf('%02d:%02d:%02d', $timeHours, $timeMinutes, $atimeSeconds));
        $form->addItem($avgTimeOfWork);

        $firstVisit = new ilNonEditableValueGUI($this->lng->txt('tst_stat_result_firstvisit'));
        $firstVisit->setValue(ilDatePresentation::formatDate(new ilDateTime($data->getParticipant($activeId)->getFirstVisit(), IL_CAL_UNIX)));
        $form->addItem($firstVisit);

        $lastVisit = new ilNonEditableValueGUI($this->lng->txt('tst_stat_result_lastvisit'));
        $lastVisit->setValue(ilDatePresentation::formatDate(new ilDateTime($data->getParticipant($activeId)->getLastVisit(), IL_CAL_UNIX)));
        $form->addItem($lastVisit);

        $nrPasses = new ilNonEditableValueGUI($this->lng->txt('tst_nr_of_passes'));
        $nrPasses->setValue($data->getParticipant($activeId)->getLastPass() + 1);
        $form->addItem($nrPasses);

        $scoredPass = new ilNonEditableValueGUI($this->lng->txt('scored_pass'));
        if ($this->testObject->getPassScoring() === ilObjTest::SCORE_BEST_PASS) {
            $scoredPass->setValue($data->getParticipant($activeId)->getBestPass() + 1);
        } else {
            $scoredPass->setValue($data->getParticipant($activeId)->getLastPass() + 1);
        }
        $form->addItem($scoredPass);

        $median = $data->getStatistics()->getStatistics()->median();
        $pct = $data->getParticipant($activeId)->getMaxpoints() ? ($median / $data->getParticipant($activeId)->getMaxpoints()) * 100.0 : 0;
        $mark = $this->testObject->getMarkSchema()->getMatchingMark($pct);
        if ($mark instanceof ASS_Mark) {
            $markMedian = new ilNonEditableValueGUI($this->lng->txt('tst_stat_result_mark_median'));
            $markMedian->setValue($mark->getShortName());
            $form->addItem($markMedian);
        }

        $rankParticipant = new ilNonEditableValueGUI($this->lng->txt('tst_stat_result_rank_participant'));
        $rankParticipant->setValue($data->getStatistics()->getStatistics()->rank($data->getParticipant($activeId)->getReached()));
        $form->addItem($rankParticipant);

        $rankMedian = new ilNonEditableValueGUI($this->lng->txt('tst_stat_result_rank_median'));
        $rankMedian->setValue($data->getStatistics()->getStatistics()->rank_median());
        $form->addItem($rankMedian);

        $totalParticipants = new ilNonEditableValueGUI($this->lng->txt('tst_stat_result_total_participants'));
        $totalParticipants->setValue($data->getStatistics()->getStatistics()->count());
        $form->addItem($totalParticipants);

        $medianField = new ilNonEditableValueGUI($this->lng->txt('tst_stat_result_median'));
        $medianField->setValue($median);
        $form->addItem($medianField);

        return $form;
    }
}
