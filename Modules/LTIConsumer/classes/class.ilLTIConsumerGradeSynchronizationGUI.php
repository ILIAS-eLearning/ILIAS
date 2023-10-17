<?php

declare(strict_types=1);

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
 * Class ilLTIConsumerGradeSynchronizationGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/LTIConsumer
 */
class ilLTIConsumerGradeSynchronizationGUI
{
    /**
     * @var ilObjLTIConsumer
     */
    protected ilObjLTIConsumer $object;

    /**
     * @var ilLTIConsumerAccess
     */
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

            $statementsFilter->setActivityId($this->object->getProvider()->getContentItemUrl());

            $this->initLimitingAndOrdering($statementsFilter, $table);
            $this->initActorFilter($statementsFilter, $table);
            $this->initVerbFilter($statementsFilter, $table);
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
        $table->determineOffsetAndOrder();

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
                    $filter->setActor(new ilCmiXapiUser($this->object->getId(), $usrId, $this->object->getProvider()->getPrivacyIdent()));
                } else {
                    throw new ilCmiXapiInvalidStatementsFilterException(
                        "given actor ({$actor}) is not a valid actor for object ({$this->object->getId()})"
                    );
                }
            }
        } else {
            $filter->setActor(new ilCmiXapiUser($this->object->getId(), $DIC->user()->getId(), $this->object->getProvider()->getPrivacyIdent()));
        }
    }

    protected function initVerbFilter(ilLTIConsumerGradeSynchronizationFilter $filter, ilLTIConsumerGradeSynchronizationTableGUI $table): void
    {
        $verb = urldecode($table->getFilterItemByPostVar('verb')->getValue());

        $verbsallowed = ['completed', 'passed'];

        if (in_array($verb, $verbsallowed)) {
            $filter->setVerb($verb);
        }
    }

    protected function initPeriodFilter(ilLTIConsumerGradeSynchronizationFilter $filter, ilLTIConsumerGradeSynchronizationTableGUI $table): void
    {
        $period = $table->getFilterItemByPostVar('period');

        if ($period->getStartXapiDateTime()) {
            $filter->setStartDate($period->getStartXapiDateTime());
        }

        if ($period->getEndXapiDateTime()) {
            $filter->setEndDate($period->getEndXapiDateTime());
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
        $table->setData(ilLTIConsumerGradeSynchronization::getGradesForObject($this->object->getId()));
        //        $table->setMaxCount($statementsReport->getMaxCount());
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
