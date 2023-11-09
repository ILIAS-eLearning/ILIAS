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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

/**
 * @author  Maximilian Becker <mbecker@databay.de>
 * @ingroup components\ILIASTest
 */
class ilTestToplistGUI
{
    public function __construct(
        private ilObjTest $test_obj,
        private ilTestTopList $toplist,
        private ilCtrl $ctrl,
        private ilGlobalTemplateInterface $tpl,
        private ilLanguage $lng,
        private ilObjUser $user,
        private UIFactory $ui_factory,
        private UIRenderer $ui_renderer
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
        $this->tpl->setContent(implode('', [
            $this->renderMedianMarkPanel(),
            $this->renderResultsToplistByScore(),
            $this->renderResultsToplistByTime(),
        ]));
    }

    /**
     * @return string
     */
    protected function renderMedianMarkPanel(): string
    {
        $title = $this->lng->txt('tst_median_mark_panel');

        // BH: this really is the "mark of median" ??!
        $activeId = $this->test_obj->getActiveIdOfUser($this->user->getId());
        $data = $this->test_obj->getCompleteEvaluationData();
        $median = $data->getStatistics()->getStatistics()->median();
        $pct = $data->getParticipant($activeId)->getMaxpoints() ? ($median / $data->getParticipant($activeId)->getMaxpoints()) * 100.0 : 0;
        $mark = $this->test_obj->getMarkSchema()->getMatchingMark($pct);
        $content = $mark->getShortName();

        $panel = $this->ui_factory->panel()->standard(
            $title,
            $this->ui_factory->legacy($content)
        );

        return $this->ui_renderer->render($panel);
    }

    /**
     * @return string
     */
    protected function renderResultsToplistByScore(): string
    {
        $title = $this->lng->txt('toplist_by_score');
        $html = '';

        if ($this->isTopTenRankingTableRequired()) {
            $topData = $this->toplist->getGeneralToplistByPercentage(
                $this->test_obj->getRefId(),
                (int) $this->user->getId()
            );

            $table = $this->buildTableGUI();
            $table->setData($topData);
            $table->setTitle($title);

            $html .= $table->getHTML();
        }

        if ($this->isOwnRankingTableRequired()) {
            $ownData = $this->toplist->getUserToplistByPercentage(
                $this->test_obj->getRefId(),
                (int) $this->user->getId()
            );

            $table = $this->buildTableGUI();
            $table->setData($ownData);
            if (!$this->isTopTenRankingTableRequired()) {
                $table->setTitle($title);
            }

            $html .= $table->getHTML();
        }

        return $html;
    }

    /**
     * @return string
     */
    protected function renderResultsToplistByTime(): string
    {
        $title = $this->lng->txt('toplist_by_time');
        $html = '';

        if ($this->isTopTenRankingTableRequired()) {
            $topData = $this->toplist->getGeneralToplistByWorkingtime(
                $this->test_obj->getRefId(),
                $this->user->getId()
            );

            $table = $this->buildTableGUI();
            $table->setData($topData);
            $table->setTitle($title);

            $html .= $table->getHTML();
        }

        if ($this->isOwnRankingTableRequired()) {
            $ownData = $this->toplist->getUserToplistByWorkingtime(
                $this->test_obj->getRefId(),
                (int) $this->user->getId()
            );

            $table = $this->buildTableGUI();
            $table->setData($ownData);

            if (!$this->isTopTenRankingTableRequired()) {
                $table->setTitle($title);
            }

            $html .= $table->getHTML();
        }

        return $html;
    }

    /**
     * @return ilTestTopListTableGUI
     */
    protected function buildTableGUI(): ilTestTopListTableGUI
    {
        $table = new ilTestTopListTableGUI($this, $this->test_obj);

        return $table;
    }

    /**
     * @return bool
     */
    protected function isTopTenRankingTableRequired(): bool
    {
        return $this->test_obj->getHighscoreTopTable();
    }

    /**
     * @return bool
     */
    protected function isOwnRankingTableRequired(): bool
    {
        return $this->test_obj->getHighscoreOwnTable();
    }
}
