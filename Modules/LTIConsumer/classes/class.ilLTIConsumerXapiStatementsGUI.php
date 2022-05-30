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
 * @package     Module/LTIConsumer
 */
class ilLTIConsumerXapiStatementsGUI
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
    
    protected function initActorFilter(ilCmiXapiStatementsReportFilter $filter, ilCmiXapiStatementsTableGUI $table) : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        if ($this->access->hasOutcomesAccess()) {
            $actor = $table->getFilterItemByPostVar('actor')->getValue();
            
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
    
    protected function initVerbFilter(ilCmiXapiStatementsReportFilter $filter, ilCmiXapiStatementsTableGUI $table) : void
    {
        $verb = urldecode($table->getFilterItemByPostVar('verb')->getValue());
        
        if (ilCmiXapiVerbList::getInstance()->isValidVerb($verb)) {
            $filter->setVerb($verb);
        }
    }
    
    protected function initPeriodFilter(ilCmiXapiStatementsReportFilter $filter, ilCmiXapiStatementsTableGUI $table) : void
    {
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
    
    protected function initTableData(ilCmiXapiStatementsTableGUI $table, ilCmiXapiStatementsReportFilter $filter) : void
    {
        $aggregateEndPointUrl = str_replace(
            'data/xAPI',
            'api/statements/aggregate',
            $this->object->getProvider()->getXapiLaunchUrl() // should be named endpoint not launch url
        );
        
        $linkBuilder = new ilCmiXapiStatementsReportLinkBuilder(
            $this->object->getId(),
            $aggregateEndPointUrl,
            $filter
        );
        
        $basicAuth = ilCmiXapiLrsType::buildBasicAuth(
            $this->object->getProvider()->getXapiLaunchKey(),
            $this->object->getProvider()->getXapiLaunchSecret()
        );
        
        $request = new ilCmiXapiStatementsReportRequest(
            $basicAuth,
            $linkBuilder
        );
        
        $statementsReport = $request->queryReport($this->object->getId());
        $table->setData($statementsReport->getTableData());
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
