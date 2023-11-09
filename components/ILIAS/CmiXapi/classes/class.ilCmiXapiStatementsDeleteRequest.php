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
 * Class ilCmiXapiStatementsDeleteRequest
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Stefan Schneider
 */

class ilCmiXapiStatementsDeleteRequest
{
    public const DELETE_SCOPE_FILTERED = "filtered";
    public const DELETE_SCOPE_ALL = "all";
    public const DELETE_SCOPE_OWN = "own";

    private \GuzzleHttp\Client $client;

    private ?int $usrId;

    private string $activityId;

    protected string $scope;

    protected ?ilCmiXapiStatementsReportFilter $filter;

    protected int $objId;

    protected ilCmiXapiLrsType $lrsType;

    protected string $endpointDefault = '';

    protected string $endpointFallback = '';

    protected array $headers;

    protected array $defaultHeaders;

    protected ilLogger $log;

    public function __construct(
        int $obj_id,
        int $type_id,
        string $activity_id,
        int $usr_id = null,
        ?string $scope = self::DELETE_SCOPE_FILTERED,
        ?ilCmiXapiStatementsReportFilter $filter = null
    ) {
        if ((int)ILIAS_VERSION_NUMERIC < 6) { // only in plugin
            require_once __DIR__ . '/../XapiProxy/vendor/autoload.php';
        }
        $this->objId = $obj_id;
        $this->lrsType = new ilCmiXapiLrsType($type_id);
        $this->activityId = $activity_id;
        $this->usrId = $usr_id;
        $this->scope = $scope;
        $this->filter = $filter;

        $this->endpointDefault = $this->lrsType->getLrsEndpoint();
        $this->client = new GuzzleHttp\Client();
        $this->headers = [
            'X-Experience-API-Version' => '1.0.3'
        ];
        $this->defaultHeaders = $this->headers;
        $this->defaultHeaders['Authorization'] = $this->lrsType->getBasicAuth();

        $this->log = ilLoggerFactory::getLogger('cmix');
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        global $DIC; /** @var \ILIAS\DI\Container $DIC */
        $allResponses = $this->deleteData();
        $resStatements = $allResponses['statements'];
        $resStates = $allResponses['states'];
        $defaultRejected = isset($resStatements['default']) && isset($resStatements['default']['state']) && $resStatements['default']['state'] === 'rejected';
        $resArr = array();
        // ToDo: fullfilled and status code handling
        if (isset($resStatements['default']) && isset($resStatements['default']['value'])) {
            $res = $resStatements['default']['value'];
            $resBody = json_decode((string) $res->getBody(), true);
            $resArr[] = $resBody['_id'];
        }
        if (count($resArr) == 0) {
            $this->log->debug("No data deleted");
            return !$defaultRejected;
        }

        $maxtime = 240; // should be some minutes!
        $t = 0;
        $done = false;
        while ($t < $maxtime) {
            // get batch done
            sleep(1);
            $response = $this->queryBatch($resArr);
            if (isset($response['default']) && isset($response['default']['value'])) {
                $res = $response['default']['value'];
                $resBody = json_decode((string) $res->getBody(), true);
                if ($resBody && $resBody['edges'] && count($resBody['edges']) == 1) {
                    $doneDefault = $resBody['edges'][0]['node']['done'];
                    $this->log->debug("doneDefault: " . $doneDefault);
                }
            }
            if ($doneDefault) {
                $done = true;
                break;
            }
            $t++;
        }
        if ($done) {
            $this->checkDeleteUsersForObject();
        }
        return $done;
    }

    public function deleteData(): array
    {
        global $DIC;

        $deleteState = true;

        $f = null;
        if ($this->scope === self::DELETE_SCOPE_FILTERED) {
            $deleteState = $this->checkDeleteState();
            $f = $this->buildDeleteFiltered();
        }
        if ($this->scope === self::DELETE_SCOPE_ALL) {
            $f = $this->buildDeleteAll();
        }
        if ($this->scope === self::DELETE_SCOPE_OWN) {
            $f = $this->buildDeleteOwn();
        }
        if ($f === false) {
            $this->log->debug('error: could not build filter');
            return array();
        }
        $cf = array('filter' => $f);
        $body = json_encode($cf);
        $this->defaultHeaders['Content-Type'] = 'application/json; charset=utf-8';
        $defaultUrl = $this->lrsType->getLrsEndpointDeleteLink();
        $defaultRequest = new GuzzleHttp\Psr7\Request('POST', $defaultUrl, $this->defaultHeaders, $body);
        $promisesStatements = [
            'default' => $this->client->sendAsync($defaultRequest)
        ];
        $promisesStates = array();
        if ($deleteState) {
            $urls = $this->getDeleteStateUrls($this->lrsType->getLrsEndpointStateLink());
            foreach ($urls as $i => $v) {
                $r = new GuzzleHttp\Psr7\Request('DELETE', $v, $this->defaultHeaders);
                $promisesStates['default' . $i] = $this->client->sendAsync($r);
            }
        }
        $response = array();
        $response['statements'] = array();
        $response['states'] = array();

        try { // maybe everything into one promise?
            $response['statements'] = GuzzleHttp\Promise\Utils::settle($promisesStatements)->wait();
            if ($deleteState && count($promisesStates) > 0) {
                $response['states'] = GuzzleHttp\Promise\Utils::settle($promisesStates)->wait();
            }
        } catch (Exception $e) {
            $this->log->debug('error:' . $e->getMessage());
        }
        return $response;
    }

    public function _lookUpDataCount($scope = null)
    {
        global $DIC;
        $pipeline = array();
        if (is_null($scope)) {
            $scope = $this->scope;
        }
        if ($scope === self::DELETE_SCOPE_OWN) {
            $f = $this->buildDeleteOwn();
            if (count($f) == 0) {
                return 0;
            }
        }
        if ($scope === self::DELETE_SCOPE_FILTERED) {
            $f = $this->buildDeleteFiltered();
        }
        if ($scope === self::DELETE_SCOPE_ALL) {
            $f = $this->buildDeleteAll();
        }
        $pipeline[] = array('$match' => $f);
        $pipeline[] = array('$count' => 'count');
        $pquery = urlencode(json_encode($pipeline));
        $query = "pipeline={$pquery}";
        $purl = $this->lrsType->getLrsEndpointStatementsAggregationLink();
        $url = ilUtil::appendUrlParameterString($purl, $query);
        $request = new GuzzleHttp\Psr7\Request('GET', $url, $this->defaultHeaders);
        try {
            $response = $this->client->sendAsync($request)->wait();
            $cnt = json_decode($response->getBody());
            return (int) $cnt[0]->count;
        } catch(Exception $e) {
            throw new Exception("LRS Connection Problems");
            return 0;
        }
    }

    /**
     * @param string $batchId
     * @return array
     */
    public function queryBatch(array $batchId): array
    {
        global $DIC;
        $defaultUrl = $this->getBatchUrl($this->lrsType->getLrsEndpointBatchLink(), $batchId[0]);
        $defaultRequest = new GuzzleHttp\Psr7\Request('GET', $defaultUrl, $this->defaultHeaders);
        $promises = [
            'default' => $this->client->sendAsync($defaultRequest)
        ];
        $response = [];
        try {
            $response = GuzzleHttp\Promise\Utils::settle($promises)->wait();
        } catch (Exception $e) {
            $this->log->debug('error:' . $e->getMessage());
        }
        return $response;
    }

    private function getBatchUrl(string $url, string $batchId): string
    {
        $f = array();
        $f['_id'] = [
            '$oid' => $batchId
        ];
        $f = urlencode(json_encode($f));
        $f = "filter={$f}";
        return ilUtil::appendUrlParameterString($url, $f);
    }

    private function getDeleteStateUrls($url): array
    {
        $ret = array();
        $states = $this->buildDeleteStates();
        foreach($states as $i => $v) {
            $ret[] = ilUtil::appendUrlParameterString($url, $v);
        }
        return $ret;
    }

    private function buildDeleteAll(): array
    {
        global $DIC;
        $f = array();

        $f['statement.object.objectType'] = 'Activity';
        $f['statement.object.id'] = [
            '$regex' => '^' . preg_quote($this->activityId) . ''
        ];

        $f['statement.actor.objectType'] = 'Agent';

        $f['$or'] = [];
        // foreach (ilXapiCmi5User::getUsersForObjectPlugin($this->getObjId()) as $usr_id) {
        // $f['$or'][] = ['statement.actor.mbox' => "mailto:".ilXapiCmi5User::getUsrIdentPlugin($usr_id,$this->getObjId())];
        foreach (ilCmiXapiUser::getUsersForObject($this->objId) as $cmixUser) {
            $f['$or'][] = ['statement.actor.mbox' => "mailto:{$cmixUser->getUsrIdent()}"];
        }
        if (count($f['$or']) == 0) {
            // Exception Handling!
            return [];
        } else {
            return $f;
        }
    }

    private function buildDeleteFiltered(): array
    {
        global $DIC;
        $f = array();

        $f['statement.object.objectType'] = 'Activity';
        $f['statement.object.id'] = [
            '$regex' => '^' . preg_quote($this->activityId) . ''
        ];

        $f['statement.actor.objectType'] = 'Agent';
        $f['$or'] = [];
        if ($this->filter->getActor()) {
            foreach (ilCmiXapiUser::getUsersForObject($this->objId) as $cmixUser) {
                if ($cmixUser->getUsrId() == $this->filter->getActor()->getUsrId()) {
                    $f['$or'][] = ['statement.actor.mbox' => "mailto:{$cmixUser->getUsrIdent()}"];
                }
            }
        } else { // check hasOutcomes Access?
            foreach (ilCmiXapiUser::getUsersForObject($this->objId) as $cmixUser) {
                $f['$or'][] = ['statement.actor.mbox' => "mailto:{$cmixUser->getUsrIdent()}"];
            }
        }

        if ($this->filter->getVerb()) {
            $f['statement.verb.id'] = $this->filter->getVerb();
        }

        if ($this->filter->getStartDate() || $this->filter->getEndDate()) {
            $f['statement.timestamp'] = array();

            if ($this->filter->getStartDate()) {
                $f['statement.timestamp']['$gt'] = $this->filter->getStartDate()->toXapiTimestamp();
            }

            if ($this->filter->getEndDate()) {
                $f['statement.timestamp']['$lt'] = $this->filter->getEndDate()->toXapiTimestamp();
            }
        }

        if (count($f['$or']) == 0) {
            // Exception Handling!
            return [];
        } else {
            return $f;
        }
    }

    private function buildDeleteOwn(): array
    {
        global $DIC;
        $f = array();
        $f['statement.object.objectType'] = 'Activity';
        $f['statement.object.id'] = [
            '$regex' => '^' . preg_quote($this->activityId) . ''
        ];
        $f['statement.actor.objectType'] = 'Agent';

        $usrId = ($this->usrId !== null) ? $this->usrId : $DIC->user()->getId();
        $cmixUsers = ilCmiXapiUser::getInstancesByObjectIdAndUsrId($this->objId, $usrId);
        $f['$or'] = [];
        foreach ($cmixUsers as $cmixUser) {
            $f['$or'][] = ['statement.actor.mbox' => "mailto:{$cmixUser->getUsrIdent()}"];
        }
        if (count($f['$or']) == 0) {
            return [];
        } else {
            return $f;
        }
    }

    private function buildDeleteStates(): array
    {
        global $DIC;
        $ret = array();
        $user = "";
        if ($this->scope === self::DELETE_SCOPE_FILTERED && $this->filter->getActor()) {
            foreach (ilCmiXapiUser::getUsersForObject($this->objId) as $cmixUser) {
                if ($cmixUser->getUsrId() == $this->filter->getActor()->getUsrId()) {
                    $user = $cmixUser->getUsrIdent();
                    $ret[] = 'activityId=' . urlencode($this->activityId) . '&agent=' . urlencode('{"mbox":"mailto:' . $user . '"}');
                }
            }
        }

        if ($this->scope === self::DELETE_SCOPE_OWN) {
            $usrId = ($this->usrId !== null) ? $this->usrId : $DIC->user()->getId();
            foreach (ilCmiXapiUser::getUsersForObject($this->objId) as $cmixUser) {
                if ((int) $cmixUser->getUsrId() === $usrId) {
                    $user = $cmixUser->getUsrIdent();
                    $ret[] = 'activityId=' . urlencode($this->activityId) . '&agent=' . urlencode('{"mbox":"mailto:' . $user . '"}');
                }
            }
        }

        if ($this->scope === self::DELETE_SCOPE_ALL) {
            //todo check cmix_del_object
            foreach (ilCmiXapiUser::getUsersForObject($this->objId) as $cmixUser) {
                $user = $cmixUser->getUsrIdent();
                $ret[] = 'activityId=' . urlencode($this->activityId) . '&agent=' . urlencode('{"mbox":"mailto:' . $user . '"}');
            }
        }
        return $ret;
    }

    private function checkDeleteState(): bool
    {
        global $DIC;
        if ($this->scope === self::DELETE_SCOPE_ALL || $this->scope === self::DELETE_SCOPE_OWN) {
            return true;
        }
        if ($this->filter->getActor()) { // ToDo: only in Multicactor Mode?
            if ($this->filter->getVerb() || $this->filter->getStartDate() || $this->filter->getEndDate()) {
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

    private function checkDeleteUsersForObject()
    {
        global $DIC;
        if ($this->scope === self::DELETE_SCOPE_ALL) {
            ilCmiXapiUser::deleteUsersForObject($this->objId);
            //            $model = ilCmiXapiDelModel::init();
            //            $model->deleteXapiObjectEntry($this->objId);
        }
        if ($this->scope === self::DELETE_SCOPE_OWN) {
            $usrId = ($this->usrId !== null) ? [$this->usrId] : [$DIC->user()->getId()];
            ilCmiXapiUser::deleteUsersForObject($this->objId, $usrId);
        }
        if ($this->scope === self::DELETE_SCOPE_FILTERED) {
            if ($this->checkDeleteState() && $this->filter) {
                $usrId = [$this->filter->getActor()->getUsrId()];
                ilCmiXapiUser::deleteUsersForObject($this->objId, $usrId);
            }
        }
    }

}
