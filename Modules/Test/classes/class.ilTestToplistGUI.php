<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/inc.AssessmentConstants.php';
require_once 'Modules/Test/classes/class.ilTestTopList.php';
require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * Scoring class for tests
 * @author     Maximilian Becker <mbecker@databay.de>
 * @version    $Id$
 * @ingroup    ModulesTest
 */
class ilTestToplistGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    
    /**
     * @var ilTabsGUI
     */
    protected $tabs;
    
    /**
     * @var ilTemplate
     */
    protected $tpl;
    
    /**
     * @var ilLanguage
     */
    protected $lng;
    
    /**
     * @var ilObjUser
     */
    protected $user;
    
    /** @var $object ilObjTest */
    protected $object;

    /**
     * @var ilTestTopList
     */
    protected $toplist;

    /**
     * @param ilObjTest $testOBJ
     */
    public function __construct(ilObjTest $testOBJ)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $this->ctrl = $DIC['ilCtrl'];
        $this->tpl = $DIC['tpl'];
        $this->lng = $DIC['lng'];
        $this->user = $DIC['ilUser'];
        
        $this->object = $testOBJ;
        $this->toplist = new ilTestTopList($testOBJ);
    }

    public function executeCommand()
    {
        if (!$this->object->getHighscoreEnabled()) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            $this->ctrl->redirectByClass('ilObjTestGUI');
        }
        
        $this->ctrl->saveParameter($this, 'active_id');
        
        $cmd = $this->ctrl->getCmd();
        
        switch ($cmd) {
            default:
                $this->showResultsToplistsCmd();
        }
    }
    
    protected function showResultsToplistsCmd()
    {
        $html = $this->renderMedianMarkPanel();
        $html .= $this->renderResultsToplistByScore();
        $html .= $this->renderResultsToplistByTime();
        
        $this->tpl->setVariable("ADM_CONTENT", $html);
    }
    
    protected function renderMedianMarkPanel()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $title = $DIC->language()->txt('tst_median_mark_panel');
        
        // BH: this really is the "mark of median" ??!
        $activeId = $this->object->getActiveIdOfUser($DIC->user()->getId());
        $data = $this->object->getCompleteEvaluationData();
        $median = $data->getStatistics()->getStatistics()->median();
        $pct    = $data->getParticipant($activeId)->getMaxpoints() ? ($median / $data->getParticipant($activeId)->getMaxpoints()) * 100.0 : 0;
        $mark   = $this->object->mark_schema->getMatchingMark($pct);
        $content = $mark->getShortName();
        
        $panel = $DIC->ui()->factory()->panel()->standard(
            $title,
            $DIC->ui()->factory()->legacy($content)
        );
        
        return $DIC->ui()->renderer()->render($panel);
    }
    
    protected function renderResultsToplistByScore()
    {
        $title = $this->lng->txt('toplist_by_score');
        $html = '';
        
        if ($this->isTopTenRankingTableRequired()) {
            $data = $this->toplist->getGeneralToplistByPercentage($_GET['ref_id'], $this->user->getId());
            
            $table_gui = $this->buildTableGUI();
            
            $table_gui->setData($data);
            $table_gui->setTitle($title);

            $html .= $table_gui->getHTML();
        }

        if ($this->isOwnRankingTableRequired()) {
            $table_gui = $this->buildTableGUI();
            
            $table_gui->setData(
                $this->toplist->getUserToplistByPercentage($_GET['ref_id'], $this->user->getID())
            );
            
            if (!$this->isTopTenRankingTableRequired()) {
                $table_gui->setTitle($title);
            }

            $html .= $table_gui->getHTML();
        }

        return $html;
    }
    
    protected function renderResultsToplistByTime()
    {
        $title = $this->lng->txt('toplist_by_time');
        $html = '';

        if ($this->isTopTenRankingTableRequired()) {
            $topData = $this->toplist->getGeneralToplistByWorkingtime($_GET['ref_id'], $this->user->getId());
            
            $table_gui = $this->buildTableGUI();
            $table_gui->setData($topData);
            $table_gui->setTitle($title);

            $html .= $table_gui->getHTML();
        }

        if ($this->isOwnRankingTableRequired()) {
            $ownData = $this->toplist->getUserToplistByWorkingtime($_GET['ref_id'], $this->user->getID());
            
            $table_gui = $this->buildTableGUI();
            
            $table_gui->setData($ownData);
            
            if (!$this->isTopTenRankingTableRequired()) {
                $table_gui->setTitle($title);
            }
            
            $html .= $table_gui->getHTML();
        }

        return $html;
    }

    /**
     * @param ilTable2GUI $table_gui
     */
    private function prepareTable(ilTable2GUI $table_gui)
    {
        $table_gui->addColumn($this->lng->txt('toplist_col_rank'));
        $table_gui->addColumn($this->lng->txt('toplist_col_participant'));
        if ($this->object->getHighscoreAchievedTS()) {
            $table_gui->addColumn($this->lng->txt('toplist_col_achieved'));
        }

        if ($this->object->getHighscoreScore()) {
            $table_gui->addColumn($this->lng->txt('toplist_col_score'));
        }

        if ($this->object->getHighscorePercentage()) {
            $table_gui->addColumn($this->lng->txt('toplist_col_percentage'));
        }

        if ($this->object->getHighscoreHints()) {
            $table_gui->addColumn($this->lng->txt('toplist_col_hints'));
        }

        if ($this->object->getHighscoreWTime()) {
            $table_gui->addColumn($this->lng->txt('toplist_col_wtime'));
        }
        $table_gui->setEnableNumInfo(false);
        $table_gui->setLimit(10);
    }
    
    /**
     * @return ilTable2GUI
     */
    protected function buildTableGUI()
    {
        $table_gui = new ilTable2GUI($this);
        $this->prepareTable($table_gui);
        $table_gui->setRowTemplate('tpl.toplist_tbl_rows.html', 'Modules/Test');
        return $table_gui;
    }
    
    /**
     * @return bool
     */
    protected function isTopTenRankingTableRequired()
    {
        if ($this->object->getHighscoreMode() == ilObjTest::HIGHSCORE_SHOW_TOP_TABLE) {
            return true;
        }
        
        if ($this->object->getHighscoreMode() == ilObjTest::HIGHSCORE_SHOW_ALL_TABLES) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @return bool
     */
    protected function isOwnRankingTableRequired()
    {
        if ($this->object->getHighscoreMode() == ilObjTest::HIGHSCORE_SHOW_OWN_TABLE) {
            return true;
        }
        
        if ($this->object->getHighscoreMode() == ilObjTest::HIGHSCORE_SHOW_ALL_TABLES) {
            return true;
        }
        
        return false;
    }
}
