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

use ILIAS\Test\Results\Toplist\TestTopListRepository;
use ILIAS\Test\Results\Toplist\DataRetrieval;
use ILIAS\Test\Results\Toplist\TopListOrder;
use ILIAS\Test\Results\Toplist\TopListType;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component\Panel\Panel;
use ILIAS\UI\Component\Table\Data;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\HTTP\GlobalHttpState as GlobalHttpState;

/**
 * @author  Maximilian Becker <mbecker@databay.de>
 * @ingroup components\ILIASTest
 */
class ilTestToplistGUI
{
    public function __construct(
        protected readonly ilObjTest $test_obj,
        protected readonly TestTopListRepository $repository,
        protected readonly ilCtrlInterface $ctrl,
        protected readonly ilGlobalTemplateInterface $tpl,
        protected readonly ilLanguage $lng,
        protected readonly ilObjUser $user,
        protected readonly UIFactory $ui_factory,
        protected readonly UIRenderer $ui_renderer,
        protected readonly DataFactory $data_factory,
        protected readonly GlobalHttpState $http_state
    ) {
    }

    /**
     *
     */
    public function executeCommand(): void
    {
        if (!$this->test_obj->getHighscoreEnabled()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->ctrl->redirectByClass(ilObjTestGUI::class);
        }

        $this->ctrl->saveParameter($this, 'active_id');

        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            default:
                $this->showResultsToplistsCmd();
        }
    }

    protected function showResultsToplistsCmd(): void
    {
        $this->tpl->setContent($this->ui_renderer->render([
            $this->buildMedianMarkPanel(),
            ...$this->buildResultsToplists(TopListOrder::BY_SCORE),
            ...$this->buildResultsToplists(TopListOrder::BY_TIME),
        ]));
    }

    protected function buildMedianMarkPanel(): Panel
    {
        $title = $this->lng->txt('tst_median_mark_panel');

        // BH: this really is the "mark of median" ??!
        $activeId = $this->test_obj->getActiveIdOfUser($this->user->getId());
        $data = $this->test_obj->getCompleteEvaluationData();
        $median = $data->getStatistics()->median();
        $pct = $data->getParticipant($activeId)->getMaxpoints() ? ($median / $data->getParticipant($activeId)->getMaxpoints()) * 100.0 : 0;
        $mark = $this->test_obj->getMarkSchema()->getMatchingMark($pct);
        $content = $mark->getShortName();

        return $this->ui_factory->panel()->standard(
            $title,
            $this->ui_factory->legacy($content)
        );
    }

    /**
     * @return array<Data>
     */
    protected function buildResultsToplists(TopListOrder $order_by): array
    {
        $tables = [];

        if ($this->isTopTenRankingTableRequired()) {
            $tables[] = $this->buildTable(
                $this->lng->txt('toplist_by_' . $order_by->getLabel()),
                TopListType::GENERAL,
                $order_by
            )->withId('tst_top_list' . $this->test_obj->getRefId());
        }

        if ($this->isOwnRankingTableRequired()) {
            $tables[] = $this->buildTable(
                count($tables) == 0 ? $this->lng->txt('toplist_by_score' . $order_by->getLabel()) : '',
                TopListType::USER,
                $order_by
            )->withId('tst_own_list' . $this->test_obj->getRefId());
        }

        return $tables;
    }

    protected function buildTable(string $title, TopListType $list_type, TopListOrder $order_by): Data
    {
        $table = new DataRetrieval(
            $this->test_obj,
            $this->repository,
            $this->lng,
            $this->user,
            $this->ui_factory,
            $this->ui_renderer,
            $this->data_factory,
            $list_type,
            $order_by
        );
        return $this->ui_factory->table()
            ->data($title, $table->getColumns(), $table)
            ->withRequest($this->http_state->request());
    }

    protected function isTopTenRankingTableRequired(): bool
    {
        return $this->test_obj->getHighscoreTopTable();
    }

    protected function isOwnRankingTableRequired(): bool
    {
        return $this->test_obj->getHighscoreOwnTable();
    }
}
