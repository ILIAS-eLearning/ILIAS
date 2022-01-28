<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCmiXapiScoringGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiScoringGUI
{
    const PART_FILTER_ACTIVE_ONLY = 1;
    const PART_FILTER_INACTIVE_ONLY = 2;
    const PART_FILTER_ALL_USERS = 3; // default
    const PART_FILTER_MANSCORING_DONE = 4;
    const PART_FILTER_MANSCORING_NONE = 5;
    //const PART_FILTER_MANSCORING_PENDING	= 6;


    /**
     * @var ilObjCmiXapi
     */
    public $object;

    /**
     * @var ilCmiXapiAccess
     */
    protected $access;

    private $tableData;
    private $tableHtml = '';
    private $userRank;


    /**
     * @param ilObjCmiXapi $object
     */
    public function __construct(ilObjCmiXapi $object)
    {
        $this->object = $object;

        $this->access = ilCmiXapiAccess::getInstance($this->object);
    }

    /**
     * @throws ilCmiXapiException
     */
    public function executeCommand()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if (!$this->access->hasHighscoreAccess()) {
            throw new ilCmiXapiException('access denied!');
        }

        switch ($DIC->ctrl()->getNextClass($this)) {
            default:
                $cmd = $DIC->ctrl()->getCmd('show') . 'Cmd';
                $this->{$cmd}();
        }
    }

//    protected function resetFilterCmd()
//    {
//        $table = $this->buildTableGUI("");
//        $table->resetFilter();
//        $table->resetOffset();
//        $this->showCmd();
//    }
//
//    protected function applyFilterCmd()
//    {
//        $table = $this->buildTableGUI("");
//        $table->writeFilterToSession();
//        $table->resetOffset();
//        $this->showCmd();
//    }

    protected function showCmd()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        try {
            $this->initTableData()
                ->initHighScoreTable()
                ->initUserRankTable()
            ;
            //$table->setData($this->tableData);
        } catch (Exception $e) {
            ilUtil::sendFailure($e->getMessage());
            $table = $this->buildTableGUI('fallback');
            $table->setData(array());
            $table->setMaxCount(0);
            $table->resetOffset();
            $this->tableHtml = $table->getHTML();
        }

        $DIC->ui()->mainTemplate()->setContent($this->tableHtml);
    }

    /**
     *
     */
    protected function initTableData()
    {
        $filter = new ilCmiXapiStatementsReportFilter();
        $filter->setActivityId($this->object->getActivityId());
    
        $linkBuilder = new ilCmiXapiHighscoreReportLinkBuilder(
            $this->object->getId(),
            $this->object->getLrsType()->getLrsEndpointStatementsAggregationLink(),
            $filter
        );

        $request = new ilCmiXapiHighscoreReportRequest(
            $this->object->getLrsType()->getBasicAuth(),
            $linkBuilder
        );

        $scoringReport = $request->queryReport($this->object->getId());
        if (true === $scoringReport->initTableData()) {
            $this->tableData = $scoringReport->getTableData();
            $this->userRank = $scoringReport->getUserRank();
        }
        return $this;
    }

    private function getTableDataRange($scopeUserRank = false)
    {
        if (false === $scopeUserRank) {
            return array_slice($this->tableData, 0, (int) $this->object->getHighscoreTopNum());
        } else {
            $offset = $this->userRank - 2 < 0 ? 0 : $this->userRank - 2;
            $length = 5;
            return array_slice($this->tableData, $offset, $length);
        }
    }

    /**
     *
     */
    protected function initHighScoreTable()
    {
        if (!$this->object->getHighscoreTopTable() || !$this->object->getHighscoreEnabled()) {
            $this->tableHtml .= '';
            return $this;
        }
        $table = $this->buildTableGUI('highscore');
        $table->setData($this->getTableDataRange());
        $this->tableHtml .= $table->getHTML();
        return $this;
    }

    /**
     *
     */
    protected function initUserRankTable()
    {
        if (!$this->object->getHighscoreOwnTable() || !$this->object->getHighscoreEnabled()) {
            $this->tableHtml .= '';
            return $this;
        }
        $table = $this->buildTableGUI('userRank');
        $table->setData($this->getTableDataRange(true));
        $this->tableHtml .= $table->getHTML();
        return $this;
    }

    /**
     * @param string $tableId
     * @return ilCmiXapiScoringTableGUI
     */
    protected function buildTableGUI($tableId) : ilCmiXapiScoringTableGUI
    {
        $isMultiActorReport = $this->access->hasOutcomesAccess();
        $table = new ilCmiXapiScoringTableGUI(
            $this,
            'show',
            $isMultiActorReport,
            $tableId,
            $this->access->hasOutcomesAccess()
        );
        return $table;
    }
}
