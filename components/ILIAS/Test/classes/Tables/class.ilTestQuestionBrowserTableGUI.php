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

use ILIAS\Data\Range;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Taxonomy\DomainService as TaxonomyService;
use ILIAS\Test\Questions\QuestionsBrowserFilter;
use ILIAS\Test\Questions\QuestionsBrowserTable;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component\Input\Container\Filter\Filter;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Test\RequestDataCollector;
use ILIAS\Test\Questions\QuestionPoolLinkedTitleBuilder;
use ILIAS\Test\Logging\TestLogger;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

/**
 * @ilCtrl_Calls ilTestQuestionBrowserTableGUI: ilFormPropertyDispatchGUI
 */
class ilTestQuestionBrowserTableGUI
{
    use QuestionPoolLinkedTitleBuilder;

    public const REPOSITORY_ROOT_NODE_ID = 1;
    public const MODE_PARAMETER = 'question_browse_mode';
    public const MODE_BROWSE_POOLS = 'modeBrowsePools';
    public const MODE_BROWSE_TESTS = 'modeBrowseTests';

    public const CMD_BROWSE_QUESTIONS = 'browseQuestions';
    public const CMD_INSERT_QUESTIONS = 'insertQuestions';

    public function __construct(
        private readonly ilTabsGUI $tabs,
        private readonly ilTree $tree,
        private readonly ilDBInterface $db,
        private readonly TestLogger $logger,
        private readonly ilComponentRepository $component_repository,
        private readonly ilObjTest $test_obj,
        private readonly ilAccessHandler $access,
        private readonly GlobalHttpState $http_state,
        private readonly Refinery $refinery,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly RequestDataCollector $testrequest,
        private readonly GeneralQuestionPropertiesRepository $questionrepository,
        private readonly ilLanguage $lng,
        private readonly ilCtrl $ctrl,
        private readonly ilGlobalTemplateInterface $main_tpl,
        private readonly ilUIService $ui_service,
        private readonly DataFactory $data_factory,
        private readonly TaxonomyService $taxonomy,
        private readonly \Closure $questionPoolLinkBuilder
    ) {
    }



    public function executeCommand(): bool
    {
        $this->handleWriteAccess();
        $this->handleTabs();

        switch (strtolower((string) $this->ctrl->getNextClass($this))) {
            case strtolower(self::class):
            case '':
                $cmd = $this->ctrl->getCmd() . 'Cmd';
                return $this->$cmd();

            default:
                return $this->browseQuestionsCmd();
        }
    }

    /**
     * @throws ilCtrlException
     */
    private function handleWriteAccess(): void
    {
        if (!$this->access->checkAccess('write', '', $this->test_obj->getRefId())) {
            $this->ctrl->redirectByClass(ilObjTestGUI::class, ilObjTestGUI::SHOW_QUESTIONS_CMD);
        }
    }

    private function browseQuestionsCmd(): bool
    {
        $this->ctrl->setParameter($this, self::MODE_PARAMETER, $this->testrequest->raw(self::MODE_PARAMETER));
        $action = $this->ctrl->getLinkTarget($this, self::CMD_BROWSE_QUESTIONS);

        $mode = $this->ctrl->getParameterArrayByClass(self::class)[self::MODE_PARAMETER];
        $parent_title = ($mode === self::MODE_BROWSE_TESTS ? 'test_title' : 'tst_source_question_pool');

        $filter = $this->getQuestionsBrowserFilterComponent($parent_title, $action);
        $question_browser_table = $this->getQuestionsBrowserTable($parent_title);

        $this->main_tpl->setContent(
            $this->ui_renderer->render([
                $filter,
                $question_browser_table->getComponent($this->http_state->request(), $this->ui_service->filter()->getData($filter))
            ])
        );

        return true;
    }

    private function getQuestionsBrowserFilterComponent(string $parent_title = '', string $action = ''): Filter
    {
        return (new QuestionsBrowserFilter(
            $this->ui_service,
            $this->lng,
            $this->ui_factory,
            'question_browser_filter',
            $parent_title
        ))->getComponent($action, $this->http_state->request());
    }

    private function getQuestionsBrowserTable(string $parent_title = ''): QuestionsBrowserTable
    {
        return new QuestionsBrowserTable(
            (string) $this->test_obj->getId(),
            $this->ui_factory,
            $this->ui_renderer,
            $this->lng,
            $this->ctrl,
            $this->data_factory,
            new ilAssQuestionList($this->db, $this->lng, $this->refinery, $this->component_repository),
            $this->test_obj,
            $this->tree,
            $this->testrequest,
            $this->taxonomy,
            $this->questionPoolLinkBuilder,
            $parent_title
        );
    }

    /**
     * @throws ilCtrlException
     */
    private function insertQuestionsCmd(): void
    {
        $selected_array = $this->http_state->wrapper()->query()->retrieve(
            'qlist_q_id',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int()),
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()),
                $this->refinery->always([])
            ])
        );

        if ($selected_array === []) {
            $this->main_tpl->setOnScreenMessage('info', $this->lng->txt('tst_insert_missing_question'), true);
            $this->ctrl->redirect($this, self::CMD_BROWSE_QUESTIONS);
        }

        if (in_array('ALL_OBJECTS', $selected_array, true)) {
            $selected_array = array_keys(
                $this->getQuestionsBrowserTable()->loadRecords(
                    $this->ui_service->filter()->getData($this->getQuestionsBrowserFilterComponent())
                )
            );
        }

        $manscoring = false;

        foreach ($selected_array as $value) {
            $this->test_obj->insertQuestion($value);

            if (!$manscoring) {
                $manscoring |= assQuestion::_needsManualScoring($value);
            }
        }

        $this->test_obj->saveCompleteStatus($this->buildTestQuestionSetConfig());

        if ($manscoring) {
            $this->main_tpl->setOnScreenMessage('info', $this->lng->txt('manscoring_hint'), true);
        } else {
            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('tst_questions_inserted'), true);
        }

        $this->ctrl->redirectByClass(ilObjTestGUI::class, ilObjTestGUI::SHOW_QUESTIONS_CMD);
    }

    /**
     * @throws ilCtrlException
     */
    private function handleTabs(): void
    {
        $this->tabs->clearTargets();
        $this->tabs->clearSubTabs();

        $this->tabs->setBackTarget(
            $this->lng->txt('backtocallingtest'),
            $this->ctrl->getLinkTargetByClass(ilObjTestGUI::class, ilObjTestGUI::SHOW_QUESTIONS_CMD)
        );

        $browseQuestionsTabLabel = match ($this->testrequest->raw(self::MODE_PARAMETER)) {
            self::MODE_BROWSE_POOLS => $this->lng->txt('tst_browse_for_qpl_questions'),
            self::MODE_BROWSE_TESTS => $this->lng->txt('tst_browse_for_tst_questions'),
            default => ''
        };

        $this->tabs->addTab(
            self::CMD_BROWSE_QUESTIONS,
            $browseQuestionsTabLabel,
            $this->ctrl->getLinkTarget($this, self::CMD_BROWSE_QUESTIONS)
        );
        $this->tabs->activateTab('browseQuestions');
    }

    private function buildTestQuestionSetConfig(): ilTestQuestionSetConfig
    {
        return (new ilTestQuestionSetConfigFactory(
            $this->tree,
            $this->db,
            $this->lng,
            $this->logger,
            $this->component_repository,
            $this->test_obj,
            $this->questionrepository
        ))->getQuestionSetConfig();
    }
}
