<?php declare(strict_types=1);

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
 * Class ilLTIConsumerScoringGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/LTIConsumer
 */
class ilLTIConsumerScoringGUI
{
    const PART_FILTER_ACTIVE_ONLY = 1;
    const PART_FILTER_INACTIVE_ONLY = 2;
    const PART_FILTER_ALL_USERS = 3; // default
    const PART_FILTER_MANSCORING_DONE = 4;
    const PART_FILTER_MANSCORING_NONE = 5;
    //const PART_FILTER_MANSCORING_PENDING	= 6;


    /**
     * @var ilObjLTIConsumer
     */
    protected ilObjLTIConsumer $object;

    /**
     * @var ilLTIConsumerAccess
     */
    protected ilLTIConsumerAccess $access;

    private array $tableData;
    private string $tableHtml = '';
    private int $userRank;
    private \ilGlobalTemplateInterface $main_tpl;


    public function __construct(ilObjLTIConsumer $object)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->object = $object;

        $this->access = ilLTIConsumerAccess::getInstance($this->object);
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

    protected function showCmd() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        try {
            $this->initTableData()
                ->initHighScoreTable()
                ->initUserRankTable()
            ;
        } catch (Exception $e) {
            $this->main_tpl->setOnScreenMessage('failure', $e->getMessage());
            //$DIC->ui()->mainTemplate()->
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
        $aggregateEndPointUrl = str_replace(
            'data/xAPI',
            'api/statements/aggregate',
            $this->object->getProvider()->getXapiLaunchUrl() // should be named endpoint not launch url
        );

        $basicAuth = ilCmiXapiLrsType::buildBasicAuth(
            $this->object->getProvider()->getXapiLaunchKey(),
            $this->object->getProvider()->getXapiLaunchSecret()
        );

        $filter = new ilCmiXapiStatementsReportFilter();
        $filter->setActivityId($this->object->getActivityId());
        
        $linkBuilder = new ilCmiXapiHighscoreReportLinkBuilder(
            $this->object->getId(),
            $aggregateEndPointUrl,
            $filter
        );

        $request = new ilCmiXapiHighscoreReportRequest(
            $basicAuth,
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
     * @return mixed[]
     */
    private function getTableDataRange(?bool $scopeUserRank = false) : array
    {
        if (false === $scopeUserRank) {
            return array_slice($this->tableData, 0, $this->object->getHighscoreTopNum());
        } else {
            $offset = $this->userRank - 2 < 0 ? 0 : $this->userRank - 2;
            $length = 5;
            return array_slice($this->tableData, $offset, $length);
        }
        return [];
    }

    /**
     * @return $this
     */
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

    /**
     * @return $this
     */
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

    protected function buildTableGUI(string $tableId) : ilLTIConsumerScoringTableGUI
    {
        $isMultiActorReport = $this->access->hasOutcomesAccess();
        return new ilLTIConsumerScoringTableGUI(
            $this,
            'show',
            $isMultiActorReport,
            $tableId,
            $this->access->hasOutcomesAccess()
        );
    }

    public function getObject() : \ilObjLTIConsumer
    {
        return $this->object;
    }
}
