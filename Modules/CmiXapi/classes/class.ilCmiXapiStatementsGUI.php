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
    private \ILIAS\DI\Container $dic;

    public function __construct(ilObjCmiXapi $object)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->object = $object;

        $this->access = ilCmiXapiAccess::getInstance($this->object);
    }

    /**
     * @throws ilCmiXapiException
     */
    public function executeCommand(): void
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

        $this->dic->ui()->mainTemplate()->setContent($table->getHTML());
    }

    protected function initLimitingAndOrdering(ilCmiXapiStatementsReportFilter $filter, ilCmiXapiStatementsTableGUI $table): void
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
    ): void {
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
            $filter->setActor(new ilCmiXapiUser($this->object->getId(), $this->dic->user()->getId(), $this->object->getPrivacyIdent()));
        }
    }

    protected function initVerbFilter(
        ilCmiXapiStatementsReportFilter $filter,
        ilCmiXapiStatementsTableGUI $table
    ): void {
        if ($table->getFilterItemByPostVar('verb') != null) {
            $verb = urldecode($table->getFilterItemByPostVar('verb')->getValue());

            if (ilCmiXapiVerbList::getInstance()->isValidVerb($verb)) {
                $filter->setVerb($verb);
            }
        }
    }

    protected function initPeriodFilter(
        ilCmiXapiStatementsReportFilter $filter,
        ilCmiXapiStatementsTableGUI $table
    ): void {
        if ($table->getFilterItemByPostVar('period') != null) {
            $period = $table->getFilterItemByPostVar('period');

            if ($period->getStartXapiDateTime()) {
                $filter->setStartDate($period->getStartXapiDateTime());
            }

            if ($period->getEndXapiDateTime()) {
                $filter->setEndDate($period->getEndXapiDateTime());
            }
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

    protected function initTableData(
        ilCmiXapiStatementsTableGUI $table,
        ilCmiXapiStatementsReportFilter $filter
    ): void {
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

    protected function buildTableGUI(): ilCmiXapiStatementsTableGUI
    {
        $isMultiActorReport = $this->access->hasOutcomesAccess();
        $table = new ilCmiXapiStatementsTableGUI($this, 'show', $isMultiActorReport);
        $table->setFilterCommand('applyFilter');
        $table->setResetCommand('resetFilter');

        return $table;
    }
    //dynamic verbs
    public function getVerbs(): ?array
    {
        $lrsType = $this->object->getLrsType();

        //$this->getLrsEndpoint())) . '/api/' . self::ENDPOINT_AGGREGATE_SUFFIX;
        $defaultLrs = $lrsType->getLrsEndpointStatementsAggregationLink();
        //$fallbackLrs = $lrsType->getLrsFallbackEndpoint();
        $defaultBasicAuth = $lrsType->getBasicAuth();
        //$fallbackBasicAuth = $lrsType->getFallbackBasicAuth();
        $defaultHeaders = [
            'X-Experience-API-Version' => '1.0.3',
            'Authorization' => $defaultBasicAuth,
            'Cache-Control' => 'no-cache, no-store, must-revalidate'
        ];
        //        $fallbackHeaders = [
        //            'X-Experience-API-Version' => '1.0.3',
        //            'Authorization' => $fallbackBasicAuth,
        //            'Content-Type' => 'application/json;charset=utf-8',
        //            'Cache-Control' => 'no-cache, no-store, must-revalidate'
        //        ];
        $pipeline = json_encode($this->getVerbsPipline());

        $defaultVerbsUrl = $defaultLrs . "?pipeline=" . urlencode($pipeline);

        $client = new GuzzleHttp\Client();
        $req_opts = array(
            GuzzleHttp\RequestOptions::VERIFY => true,
            GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => 10,
            GuzzleHttp\RequestOptions::HTTP_ERRORS => false
        );
        //new GuzzleHttp\Psr7\Request('POST', $defaultUrl, $this->defaultHeaders, $body);
        $defaultVerbsRequest = new GuzzleHttp\Psr7\Request(
            'GET',
            $defaultVerbsUrl,
            $defaultHeaders
        );
        $promises = array();
        $promises['defaultVerbs'] = $client->sendAsync($defaultVerbsRequest, $req_opts);
        try {
            $responses = GuzzleHttp\Promise\Utils::settle($promises)->wait();
            $body = '';
            ilCmiXapiAbstractRequest::checkResponse($responses['defaultVerbs'], $body, [200]);
            return json_decode($body, (bool) JSON_OBJECT_AS_ARRAY);
        } catch (Exception $e) {
            //$this->log()->error('error:' . $e->getMessage());
            return null;
        }
        return null;
    }

    public function getVerbsPipline(): array
    {
        $pipeline = array();

        // filter activityId
        $match = array();
        $match['statement.object.objectType'] = 'Activity';
        $match['statement.actor.objectType'] = 'Agent';

        $activityId = array();

        if ($this->object->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5 && !$this->object->isMixedContentType()) {
            // https://github.com/AICC/CMI-5_Spec_Current/blob/quartz/cmi5_spec.md#963-extensions
            $activityId['statement.context.extensions.https://ilias&46;de/cmi5/activityid'] = $this->object->getActivityId();
        } else {
            $activityQuery = [
                '$regex' => '^' . preg_quote($this->object->getActivityId()) . ''
            ];
            $activityId['$or'] = [];
            $activityId['$or'][] = ['statement.object.id' => $activityQuery];
            $activityId['$or'][] = ['statement.context.contextActivities.parent.id' => $activityQuery];
        }
        $match['$and'] = [];
        $match['$and'][] = $activityId;

        $sort = array();
        $sort['statement.verb.id'] = 1;

        // project distinct verbs
        $group = array('_id' => '$statement.verb.id');
        // $project = array('statement.verb.id' => 1);
        // project distinct verbs

        $pipeline[] = array('$match' => $match);
        $pipeline[] = array('$group' => $group);
        $pipeline[] = array('$sort' => $sort);
        //$pipeline[] = array('$project' => $project);

        return $pipeline;
    }
}
