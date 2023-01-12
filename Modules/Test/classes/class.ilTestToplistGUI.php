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

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * @author  Maximilian Becker <mbecker@databay.de>
 * @ingroup ModulesTest
 */
class ilTestToplistGUI
{
    /** @var ilCtrl */
    protected $ctrl;
    /** @var ilTabsGUI */
    protected $tabs;
    /** @var ilTemplate */
    protected $tpl;
    /** @var ilLanguage */
    protected $lng;
    /** @var ilObjUser */
    protected $user;
    /** @var ilObjTest */
    protected $object;
    /** @var ilTestTopList */
    protected $toplist;
    /** @var Factory */
    private $uiFactory;
    /** @var Renderer */
    private $uiRenderer;

    /**
     * @param ilObjTest $testOBJ
     */
    public function __construct(ilObjTest $testOBJ)
    {
        global $DIC;
        /* @var ILIAS\DI\Container $DIC */

        $this->ctrl = $DIC['ilCtrl'];
        $this->tpl = $DIC['tpl'];
        $this->lng = $DIC['lng'];
        $this->user = $DIC['ilUser'];
        $this->uiFactory = $DIC->ui()->factory();
        $this->uiRenderer = $DIC->ui()->renderer();

        $this->object = $testOBJ;
        $this->toplist = new ilTestTopList($testOBJ);
    }

    /**
     *
     */
    public function executeCommand(): void
    {
        if (!$this->object->getHighscoreEnabled()) {
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
        $activeId = $this->object->getActiveIdOfUser($this->user->getId());
        $data = $this->object->getCompleteEvaluationData();
        $median = $data->getStatistics()->getStatistics()->median();
        $pct = $data->getParticipant($activeId)->getMaxpoints() ? ($median / $data->getParticipant($activeId)->getMaxpoints()) * 100.0 : 0;
        $mark = $this->object->mark_schema->getMatchingMark($pct);
        $content = $mark->getShortName();

        $panel = $this->uiFactory->panel()->standard(
            $title,
            $this->uiFactory->legacy($content)
        );

        return $this->uiRenderer->render($panel);
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
                $this->object->getRefId(),
                (int) $this->user->getId()
            );

            $table = $this->buildTableGUI();
            $table->setData($topData);
            $table->setTitle($title);

            $html .= $table->getHTML();
        }

        if ($this->isOwnRankingTableRequired()) {
            $ownData = $this->toplist->getUserToplistByPercentage(
                $this->object->getRefId(),
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
                $this->object->getRefId(),
                $this->user->getId()
            );

            $table = $this->buildTableGUI();
            $table->setData($topData);
            $table->setTitle($title);

            $html .= $table->getHTML();
        }

        if ($this->isOwnRankingTableRequired()) {
            $ownData = $this->toplist->getUserToplistByWorkingtime(
                $this->object->getRefId(),
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
        $table = new ilTestTopListTableGUI($this, $this->object);

        return $table;
    }

    /**
     * @return bool
     */
    protected function isTopTenRankingTableRequired(): bool
    {
        return $this->object->getHighscoreTopTable();
    }

    /**
     * @return bool
     */
    protected function isOwnRankingTableRequired(): bool
    {
        return $this->object->getHighscoreOwnTable();
    }
}
