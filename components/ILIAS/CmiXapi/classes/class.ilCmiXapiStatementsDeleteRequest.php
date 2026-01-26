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
        ?int $usr_id = null,
        ?string $scope = self::DELETE_SCOPE_FILTERED,
        ?ilCmiXapiStatementsReportFilter $filter = null
    ) {
        $this->objId = $obj_id;
        $this->lrsType = new ilCmiXapiLrsType($type_id);
        $this->activityId = $activity_id;
        $this->usrId = $usr_id;
        $this->scope = $scope;
        $this->filter = $filter;

        $this->endpointDefault = $this->lrsType->getLrsEndpoint();
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
        $promisesStatements = [
            'default' => $this->sendCurlRequest('POST', $defaultUrl, $this->defaultHeaders, $body),
        ];
        $promisesStates = array();
        if ($deleteState) {
            $urls = $this->getDeleteStateUrls($this->lrsType->getLrsEndpointStateLink());
            foreach ($urls as $i => $v) {
                $promisesStates['default' . $i] = $this->sendCurlRequest('DELETE', $v, $this->defaultHeaders, $body);
            }
        }
        $response = array();
        $response['statements'] = array();
        $response['states'] = array();

        try { // maybe everything into one promise?
            $response['statements'] = $this->executeMultiCurl($promisesStatements);
            if ($deleteState && count($promisesStates) > 0) {
                $response['states'] = $this->executeMultiCurl($promisesStates);
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
        try {
            $response = $this->sendCurlRequest('GET', $url, $this->defaultHeaders);
            if ($response['status'] === 200) {
                $cnt = json_decode($response['body'], true);
            }
            return (int) $cnt[0]->count;
        } catch (Exception $e) {
            throw new Exception("LRS Connection Problems");
            return 0;
        }
    }

    public function queryBatch(array $batchId): array
    {
        global $DIC;
        $defaultUrl = $this->getBatchUrl($this->lrsType->getLrsEndpointBatchLink(), $batchId[0]);

        // Header formatieren
        $headers = [];
        foreach ($this->defaultHeaders as $key => $value) {
            $headers[] = "$key: $value";
        }

        $ch = curl_init($defaultUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $response = [];
        try {
            $body = curl_exec($ch);
            $error = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $ch = null;

            if ($error) {
                throw new Exception("cURL error: $error");
            }

            $response['default'] = [
                'state' => ($httpCode >= 200 && $httpCode < 300) ? 'fulfilled' : 'rejected',
                'value' => (object) [
                    'status' => $httpCode,
                    'body' => $body
                ]
            ];

        } catch (Exception $e) {
            $this->log->debug('error:' . $e->getMessage());
            $response['default'] = [
                'state' => 'rejected',
                'reason' => $e->getMessage()
            ];
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
        foreach ($states as $i => $v) {
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
    private function sendCurlRequest(string $method, string $url, array $headers = [], ?string $body = null): array
    {
        $ch = curl_init($url);

        $formattedHeaders = [];
        foreach ($headers as $key => $value) {
            $formattedHeaders[] = "$key: $value";
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $formattedHeaders,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        if ($body !== null && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $responseBody = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $ch = null;

        return [
            'status' => $statusCode,
            'body' => $responseBody,
            'error' => $error ?: null,
        ];
    }
    private function executeMultiCurl(array $requests): array
    {
        $mh = curl_multi_init();
        $handles = [];

        // cURL Handles vorbereiten
        foreach ($requests as $key => $req) {
            if (!is_array($req)) {
                // Falls $req direkt URL + Method enthält (z. B. ['method' => 'DELETE', 'url' => '...'])
                $method = $req['method'] ?? 'GET';
                $url = $req['url'] ?? '';
                $headers = $req['headers'] ?? $this->defaultHeaders;
                $body = $req['body'] ?? null;
            } else {
                // wenn wir direkt die Parameter übergeben
                $method = $req['method'] ?? 'GET';
                $url = $req['url'] ?? '';
                $headers = $req['headers'] ?? $this->defaultHeaders;
                $body = $req['body'] ?? null;
            }

            $ch = curl_init($url);
            $formattedHeaders = [];
            foreach ($headers as $k => $v) {
                $formattedHeaders[] = "$k: $v";
            }

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => $formattedHeaders,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_SSL_VERIFYPEER => true
            ]);

            if ($body !== null && in_array($method, ['POST', 'PUT', 'PATCH'])) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }

            $handles[$key] = $ch;
            curl_multi_add_handle($mh, $ch);
        }

        // Alle gleichzeitig ausführen
        $running = 0;
        do {
            curl_multi_exec($mh, $running);
            curl_multi_select($mh);
        } while ($running > 0);

        // Ergebnisse einsammeln
        $responses = [];
        foreach ($handles as $key => $ch) {
            $responses[$key] = [
                'state' => 'fulfilled',
                'value' => (object) [
                    'status' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
                    'body' => curl_multi_getcontent($ch),
                    'error' => curl_error($ch) ?: null
                ]
            ];

            curl_multi_remove_handle($mh, $ch);
            $ch = null;
        }

        curl_multi_close($mh);

        return $responses;
    }

}
