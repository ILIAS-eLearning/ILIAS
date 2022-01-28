<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
    public ilObjCmiXapi $object;

    /**
     * @var ilCmiXapiAccess
     */
    protected ilCmiXapiAccess $access;

    private array $tableData;
    private string $tableHtml = '';
    private ?int $userRank;


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
    public function executeCommand() : void
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

    protected function resetFilterCmd() : void
    {
        $table = $this->buildTableGUI("");
        $table->resetFilter();
        $table->resetOffset();
        $this->showCmd();
    }

    protected function applyFilterCmd() : void
    {
        $table = $this->buildTableGUI("");
        $table->writeFilterToSession();
        $table->resetOffset();
        $this->showCmd();
    }

    protected function showCmd() : void
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
     * @return $this
     */
    protected function initTableData() : self
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

    /**
     * @param bool $scopeUserRank
     * @return array
     */
    private function getTableDataRange(bool $scopeUserRank = false) : array
    {
        if (false === $scopeUserRank) {
            return array_slice($this->tableData, 0, (int) $this->object->getHighscoreTopNum());
        } else {
            $offset = $this->userRank - 2 < 0 ? 0 : $this->userRank - 2;
            $length = 5;
            return array_slice($this->tableData, $offset, $length);
        }
    }

    protected function initHighScoreTable() : self
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

    protected function initUserRankTable() : self
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
    protected function buildTableGUI(string $tableId) : ilCmiXapiScoringTableGUI
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
