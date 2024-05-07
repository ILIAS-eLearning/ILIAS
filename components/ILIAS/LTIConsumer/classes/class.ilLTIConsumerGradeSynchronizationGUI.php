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

/**
 * Class ilLTIConsumerGradeSynchronizationGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/LTIConsumer
 */
class ilLTIConsumerGradeSynchronizationGUI
{
    protected ilObjLTIConsumer $object;

    protected ilLTIConsumerAccess $access;
    private \ilGlobalTemplateInterface $main_tpl;
    private \ILIAS\DI\Container $dic;

    public function __construct(ilObjLTIConsumer $object)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->object = $object;

        $this->access = ilLTIConsumerAccess::getInstance($this->object);
    }

    /**
     * @throws ilLtiConsumerException
     */
    public function executeCommand(): bool
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if (!$this->object->getProvider()->isGradeSynchronization()) {
            throw new ilLtiConsumerException('access denied!');
        }

        switch ($DIC->ctrl()->getNextClass($this)) {
            default:
                $cmd = $DIC->ctrl()->getCmd('show') . 'Cmd';
                $this->{$cmd}();
        }
        return true;
    }

    protected function resetFilterCmd(): void
    {
        $table = $this->buildTableGUI();
        $table->resetFilter();
        $table->resetOffset();
        $this->showCmd();
    }

    protected function applyFilterCmd(): void
    {
        $table = $this->buildTableGUI();
        $table->writeFilterToSession();
        $table->resetOffset();
        $this->showCmd();
    }

    protected function showCmd(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $table = $this->buildTableGUI();

        try {
            $statementsFilter = new ilLTIConsumerGradeSynchronizationFilter();

            $this->initLimitingAndOrdering($statementsFilter, $table);
            $this->initActorFilter($statementsFilter, $table);
            $this->initActivityProgressFilter($statementsFilter, $table);
            $this->initGradingProgressFilter($statementsFilter, $table);
            $this->initPeriodFilter($statementsFilter, $table);
            $this->initTableData($table, $statementsFilter);
        } catch (Exception $e) {
            $this->main_tpl->setOnScreenMessage('failure', $e->getMessage());
            $table->setData(array());
            $table->setMaxCount(0);
            $table->resetOffset();
        }

        $DIC->ui()->mainTemplate()->setContent($table->getHTML());
    }

    protected function initLimitingAndOrdering(ilLTIConsumerGradeSynchronizationFilter $filter, ilLTIConsumerGradeSynchronizationTableGUI $table): void
    {
        $table->determineOffsetAndOrder(true);

        $filter->setLimit($table->getLimit());
        $filter->setOffset($table->getOffset());

        $filter->setOrderField($table->getOrderField());
        $filter->setOrderDirection($table->getOrderDirection());
    }

    protected function initActorFilter(ilLTIConsumerGradeSynchronizationFilter $filter, ilLTIConsumerGradeSynchronizationTableGUI $table): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if ($this->object->getProvider()->isGradeSynchronization()) {
            if ($table->getFilterItemByPostVar('actor') !== null) {
                $actor = $table->getFilterItemByPostVar('actor')->getValue();
            } else {
                $actor = $DIC->user()->getLogin();
            }

            if (strlen($actor)) {
                $usrId = ilObjUser::getUserIdByLogin($actor);

                if ($usrId) {
                    $filter->setActor($usrId);
                }
            }
        } else {
            $filter->setActor($DIC->user()->getId());
        }
    }

    protected function initActivityProgressFilter(ilLTIConsumerGradeSynchronizationFilter $filter, ilLTIConsumerGradeSynchronizationTableGUI $table): void
    {
        $activityProgress = urldecode($table->getFilterItemByPostVar('activity_progress')->getValue());

        $allowed = ['Initialized', 'Started', 'InProgress', 'Submitted', 'Completed'];

        if (in_array($activityProgress, $allowed)) {
            $filter->setActivityProgress($activityProgress);
        }
    }

    protected function initGradingProgressFilter(ilLTIConsumerGradeSynchronizationFilter $filter, ilLTIConsumerGradeSynchronizationTableGUI $table): void
    {
        $gradingProgress = urldecode($table->getFilterItemByPostVar('grading_progress')->getValue());

        $allowed = ['FullyGraded', 'Pending', 'PendingManual', 'Failed', 'NotReady'];

        if (in_array($gradingProgress, $allowed)) {
            $filter->setGradingProgress($gradingProgress);
        }
    }

    protected function initPeriodFilter(ilLTIConsumerGradeSynchronizationFilter $filter, ilLTIConsumerGradeSynchronizationTableGUI $table): void
    {
        $period = $table->getFilterItemByPostVar('period');

        if ($period->getStart()) {
            $filter->setStartDate($period->getStart());
        }

        if ($period->getEnd()) {
            $filter->setEndDate($period->getEnd());
        }
    }

    public function asyncUserAutocompleteCmd(): void
    {
        $auto = new ilCmiXapiUserAutocomplete($this->object->getId());
        $auto->setSearchFields(array('login','firstname','lastname','email'));
        $auto->setResultField('login');
        $auto->enableFieldSearchableCheck(true);
        $auto->setMoreLinkAvailable(true);

        //$auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        $term = '';
        if ($this->dic->http()->wrapper()->query()->has('term')) {
            $term = $this->dic->http()->wrapper()->query()->retrieve('term', $this->dic->refinery()->kindlyTo()->string());
        } elseif ($this->dic->http()->wrapper()->post()->has('term')) {
            $term = $this->dic->http()->wrapper()->post()->retrieve('term', $this->dic->refinery()->kindlyTo()->string());
        }
        if ($term != '') {
            $result = json_decode($auto->getList(ilUtil::stripSlashes($term)), true);
            echo json_encode($result);
        }
        exit();
    }

    protected function initTableData(ilLTIConsumerGradeSynchronizationTableGUI $table, ilLTIConsumerGradeSynchronizationFilter $filter): void
    {
        $cUser = null;
        if (!$this->access->hasOutcomesAccess()) {
            $cUser = $this->dic->user()->getId();
        } else {
            $cUser = $filter->getActor();
        }
        $data = ilLTIConsumerGradeSynchronization::getGradesForObject(
            $this->object->getId(),
            $cUser,
            $filter->getActivityProgress(),
            $filter->getGradingProgress(),
            $filter->getStartDate(),
            $filter->getEndDate()
        );

        for ((int) $i = 0; $i < count($data); $i++) {
            $usr = new ilObjUser((int) $data[$i]['usr_id']);
            $data[$i]['actor'] = $usr->getFullname();
        }
        $sortNum = false;
        if ($table->getOrderField() == 'score_given') {
            $sortNum = true;
        }

        $data = ilArrayUtil::sortArray(
            $data,
            $table->getOrderField(),
            $table->getOrderDirection(),
            $sortNum
        );
        $table->setData($data);
    }

    protected function buildTableGUI(): ilLTIConsumerGradeSynchronizationTableGUI
    {
        $isMultiActorReport = $this->access->hasOutcomesAccess();

        $table = new ilLTIConsumerGradeSynchronizationTableGUI($this, 'show', $isMultiActorReport);
        $table->setFilterCommand('applyFilter');
        $table->setResetCommand('resetFilter');
        return $table;
    }
}
