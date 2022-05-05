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
 * Class ilCmiXapiContentGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiStatementsGUI
{
    protected ilObjCmiXapi $object;
    protected ilCmiXapiAccess $access;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct(ilObjCmiXapi $object)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->object = $object;

        $this->access = ilCmiXapiAccess::getInstance($this->object);
    }

    /**
     * @throws ilCmiXapiException
     */
    public function executeCommand() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if (!$this->access->hasStatementsAccess()) {
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
        $table = $this->buildTableGUI();
        $table->resetFilter();
        $table->resetOffset();
        $this->showCmd();
    }

    protected function applyFilterCmd() : void
    {
        $table = $this->buildTableGUI();
        $table->writeFilterToSession();
        $table->resetOffset();
        $this->showCmd();
    }

    protected function showCmd() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        // TODO PHP8 Review: Move Global Access to Constructor
        $table = $this->buildTableGUI();

        try {
            $statementsFilter = new ilCmiXapiStatementsReportFilter();
            $statementsFilter->setActivityId($this->object->getActivityId());
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

    protected function initLimitingAndOrdering(ilCmiXapiStatementsReportFilter $filter, ilCmiXapiStatementsTableGUI $table) : void
    {
        $table->determineOffsetAndOrder();

        $filter->setLimit($table->getLimit());
        $filter->setOffset($table->getOffset());

        $filter->setOrderField($table->getOrderField());
        $filter->setOrderDirection($table->getOrderDirection());
    }

    protected function initActorFilter(
        ilCmiXapiStatementsReportFilter $filter,
        ilCmiXapiStatementsTableGUI $table
    ) : void {
        global $DIC;
        // TODO PHP8 Review: Move Global Access to Constructor
        if ($this->access->hasOutcomesAccess()) {
            $actor = $table->getFilterItemByPostVar('actor')->getValue();
            if ($actor && strlen($actor)) {
                $usrId = ilObjUser::getUserIdByLogin($actor);
                if ($usrId) {
                    $filter->setActor(new ilCmiXapiUser($this->object->getId(), $usrId, $this->object->getPrivacyIdent()));
                } else {
                    throw new ilCmiXapiInvalidStatementsFilterException(
                        "given actor ({$actor}) is not a valid actor for object ({$this->object->getId()})"
                    );
                }
            }
        } else {
            $filter->setActor(new ilCmiXapiUser($this->object->getId(), $DIC->user()->getId(), $this->object->getPrivacyIdent()));
        }
    }

    protected function initVerbFilter(
        ilCmiXapiStatementsReportFilter $filter,
        ilCmiXapiStatementsTableGUI $table
    ) : void {
        // TODO PHP8 Review: Check getFilterItemByPostVar which could return null as well
        if ($table->getFilterItemByPostVar('verb')->getValue()) {
            $verb = urldecode($table->getFilterItemByPostVar('verb')->getValue());

            if (ilCmiXapiVerbList::getInstance()->isValidVerb($verb)) {
                $filter->setVerb($verb);
            }
        }
    }

    protected function initPeriodFilter(
        ilCmiXapiStatementsReportFilter $filter,
        ilCmiXapiStatementsTableGUI $table
    ) : void {
        // TODO PHP8 Review: Check getFilterItemByPostVar which could return null as well
        $period = $table->getFilterItemByPostVar('period');

        if ($period->getStartXapiDateTime()) {
            $filter->setStartDate($period->getStartXapiDateTime());
        }

        if ($period->getEndXapiDateTime()) {
            $filter->setEndDate($period->getEndXapiDateTime());
        }
    }

    public function asyncUserAutocompleteCmd() : void
    {
        $auto = new ilCmiXapiUserAutocomplete($this->object->getId());
        $auto->setSearchFields(array('login','firstname','lastname','email'));
        $auto->setResultField('login');
        $auto->enableFieldSearchableCheck(true);
        $auto->setMoreLinkAvailable(true);

        //$auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        // TODO PHP8 Review: Remove/Replace SuperGlobals
        $result = json_decode($auto->getList(ilUtil::stripSlashes($_REQUEST['term'])), true);

        echo json_encode($result);
        exit();
    }

    protected function initTableData(
        ilCmiXapiStatementsTableGUI $table,
        ilCmiXapiStatementsReportFilter $filter
    ) : void {
        global $DIC;
        if ($this->access->hasOutcomesAccess()) {
            if (!ilCmiXapiUser::getUsersForObject($this->object->getId())) {
                $table->setData(array());
                $table->setMaxCount(0);
                $table->resetOffset();
                return;
            }
        } else {
            $usrId = $DIC->user()->getId();
//            if (!ilCmiXapiUser::getUsersForObject($this->object->getId(), $usrId)) {
            if (!ilCmiXapiUser::getUsersForObject($this->object->getId())) {
                $table->setData(array());
                $table->setMaxCount(0);
                $table->resetOffset();
                return;
            }
        }
        $linkBuilder = new ilCmiXapiStatementsReportLinkBuilder(
            $this->object->getId(),
            $this->object->getLrsType()->getLrsEndpointStatementsAggregationLink(),
            $filter
        );

        $request = new ilCmiXapiStatementsReportRequest(
            $this->object->getLrsType()->getBasicAuth(),
            $linkBuilder
        );
        $statementsReport = $request->queryReport($this->object->getId());
        $data = $statementsReport->getTableData();
        $table->setData($data);
        $table->setMaxCount($statementsReport->getMaxCount());
    }

    protected function buildTableGUI() : ilCmiXapiStatementsTableGUI
    {
        $isMultiActorReport = $this->access->hasOutcomesAccess();
        $table = new ilCmiXapiStatementsTableGUI($this, 'show', $isMultiActorReport);
        $table->setFilterCommand('applyFilter');
        $table->setResetCommand('resetFilter');
        
        return $table;
    }
}
