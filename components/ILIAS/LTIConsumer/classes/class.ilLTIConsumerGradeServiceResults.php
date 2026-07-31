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
 * Class ilLTIConsumerGradeServiceResults
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/LTIConsumer
 */

class ilLTIConsumerGradeServiceResults extends ilLTIConsumerResourceBase
{
    public function __construct(ilLTIConsumerServiceBase $service)
    {
        parent::__construct($service);
        $this->id = 'Result.collection';
        $this->template = '/{context_id}/lineitems/{item_id}/lineitem/results';
        $this->variables[] = 'Results.url';
        $this->formats[] = 'application/vnd.ims.lis.v2.resultcontainer+json';
        $this->methods[] = 'GET';
    }

    public function execute(ilLTIConsumerServiceResponse $response): void
    {
        $params = $this->parseTemplate();
        $contextId = (int) ($params['context_id'] ?? 0);
        $itemId = (int) ($params['item_id'] ?? 0);

        try {
            $token = $this->checkTool([
                ilLTIConsumerGradeService::SCOPE_GRADESERVICE_RESULT_READ
            ]);
            if (!$token) {
                throw new Exception('invalid request', 401);
            }
            $clientId = $this->getClientIdFromToken($token);

            global $DIC;
            $ilDB = $DIC->database();
            $scoreMaximum = 1.0;
            $filters = $this->getFilters();
            $filterUserId = $filters['userId'] !== ''
                ? $this->resolveUserIdFromLtiIdent($itemId, $filters['userId'])
                : null;

            if (ilObject::_lookupType($itemId) === 'lti') {
                $object = new ilObjLTIConsumer($itemId, false);
                $provider = $object->getProvider();
                if (!$provider || (!$provider->isGradeSynchronization() && !$provider->getHasOutcome())) {
                    throw new Exception('grade synchronization not enabled', 403);
                }
                if ($provider->getClientId() !== $clientId) {
                    throw new Exception('invalid clientId', 403);
                }
                $scoreMaximum = $object->getScoreMaximum() > 0 ? $object->getScoreMaximum() : 1.0;
            } else {
                $storedId = -$itemId;
                if ($storedId <= 0 || !$ilDB->tableExists('lti_consumer_lineitems')) {
                    throw new Exception('LineItem not found', 404);
                }

                $lineItemQuery = 'SELECT score_maximum FROM lti_consumer_lineitems'
                    . ' WHERE id = ' . $ilDB->quote($storedId, 'integer')
                    . ' AND context_id = ' . $ilDB->quote($contextId, 'integer')
                    . ' AND client_id = ' . $ilDB->quote($clientId, 'text')
                    . ' AND enabled = ' . $ilDB->quote(1, 'integer');
                $lineItemRes = $ilDB->query($lineItemQuery);
                $lineItemRow = $ilDB->fetchAssoc($lineItemRes);
                if (!$lineItemRow) {
                    throw new Exception('LineItem not found', 404);
                }
                $scoreMaximum = (float) ($lineItemRow['score_maximum'] ?? 1);

                $response->setContentType('application/vnd.ims.lis.v2.resultcontainer+json');
                $response->setBody(json_encode([], JSON_UNESCAPED_SLASHES));
                return;
            }

            $privacyIdent = $provider->getPrivacyIdent();

            $query = 'SELECT * FROM lti_consumer_results'
                . ' WHERE obj_id = ' . $ilDB->quote($itemId, 'integer');
            $res = $ilDB->query($query);

            $resultsArr = [];
            $lineItemUrl = ilLTIConsumerGradeServiceLineItem::buildLineItemUrl($contextId, $itemId);
            while ($row = $ilDB->fetchAssoc($res)) {
                $userId = (int) $row['usr_id'];

                $identQuery = 'SELECT usr_ident FROM cmix_users'
                    . ' WHERE obj_id = ' . $ilDB->quote($itemId, 'integer')
                    . ' AND usr_id = ' . $ilDB->quote($userId, 'integer');
                $identRes = $ilDB->query($identQuery);
                $identRow = $ilDB->fetchAssoc($identRes);
                $userIdent = $identRow['usr_ident'] ?? (string) $userId;
                if ($filterUserId !== null && $filterUserId !== $userId) {
                    continue;
                }
                if ($filters['userId'] !== '' && $filterUserId === null && !$this->matchesLtiUserIdent($itemId, $userId, $userIdent, $filters['userId'])) {
                    continue;
                }

                $resultValue = $row['result'];
                if ($resultValue !== null) {
                    $ltiUserId = $this->getLtiUserId($privacyIdent, $userId, $userIdent);

                    $latestGradeQuery = 'SELECT score_given, score_maximum FROM lti_consumer_grades'
                        . ' WHERE obj_id = ' . $ilDB->quote($itemId, 'integer')
                        . ' AND usr_id = ' . $ilDB->quote($userId, 'integer')
                        . ' AND score_given IS NOT NULL'
                        . ' AND score_maximum IS NOT NULL'
                        . ' ORDER BY lti_timestamp DESC, stored DESC';
                    $latestGradeRes = $ilDB->query($latestGradeQuery);
                    $latestGradeRow = $ilDB->fetchAssoc($latestGradeRes);

                    $resultScore = $latestGradeRow !== null
                        ? (float) $latestGradeRow['score_given']
                        : (float) $resultValue * $scoreMaximum;
                    $resultMaximum = $latestGradeRow !== null && (float) $latestGradeRow['score_maximum'] > 0
                        ? (float) $latestGradeRow['score_maximum']
                        : $scoreMaximum;

                    $resultsArr[] = [
                        'id' => $lineItemUrl . '/results'
                            . '?user_id=' . rawurlencode($ltiUserId),
                        'scoreOf' => $lineItemUrl,
                        'userId' => $ltiUserId,
                        'resultScore' => $resultScore,
                        'resultMaximum' => $resultMaximum
                    ];
                }
            }

            if (empty($resultsArr)) {
                $gradeQuery = 'SELECT * FROM lti_consumer_grades'
                    . ' WHERE obj_id = ' . $ilDB->quote($itemId, 'integer')
                    . ' AND score_given IS NOT NULL'
                    . ' AND score_maximum IS NOT NULL';
                if ($filterUserId !== null) {
                    $gradeQuery .= ' AND usr_id = ' . $ilDB->quote($filterUserId, 'integer');
                }
                $gradeQuery .= ' ORDER BY lti_timestamp DESC, stored DESC';
                $gradeRes = $ilDB->query($gradeQuery);
                $seenUsers = [];
                while ($gradeRow = $ilDB->fetchAssoc($gradeRes)) {
                    $userId = (int) $gradeRow['usr_id'];
                    if (isset($seenUsers[$userId])) {
                        continue;
                    }
                    $seenUsers[$userId] = true;

                    $identQuery = 'SELECT usr_ident FROM cmix_users'
                        . ' WHERE obj_id = ' . $ilDB->quote($itemId, 'integer')
                        . ' AND usr_id = ' . $ilDB->quote($userId, 'integer');
                    $identRes = $ilDB->query($identQuery);
                    $identRow = $ilDB->fetchAssoc($identRes);
                    $userIdent = $identRow['usr_ident'] ?? (string) $userId;
                    if ($filterUserId === null && $filters['userId'] !== '' && !$this->matchesLtiUserIdent($itemId, $userId, $userIdent, $filters['userId'])) {
                        continue;
                    }

                    $ltiUserId = $this->getLtiUserId($privacyIdent, $userId, $userIdent);
                    $resultMaximum = (float) $gradeRow['score_maximum'];
                    $resultsArr[] = [
                        'id' => $lineItemUrl . '/results'
                            . '?user_id=' . rawurlencode($ltiUserId),
                        'scoreOf' => $lineItemUrl,
                        'userId' => $ltiUserId,
                        'resultScore' => (float) $gradeRow['score_given'],
                        'resultMaximum' => $resultMaximum > 0 ? $resultMaximum : $scoreMaximum
                    ];
                }
            }

            if ($filters['limit'] > 0) {
                $resultsArr = array_slice($resultsArr, 0, $filters['limit']);
            }

            $response->setContentType('application/vnd.ims.lis.v2.resultcontainer+json');
            $response->setBody(json_encode($resultsArr, JSON_UNESCAPED_SLASHES));
        } catch (Exception $e) {
            $code = $e->getCode();
            $response->setCode($code >= 400 && $code < 600 ? $code : 500);
            $response->setReason($e->getMessage());
        }
    }

    protected function getFilters(): array
    {
        global $DIC;

        $query = $DIC->http()->wrapper()->query();
        $string = $DIC->refinery()->kindlyTo()->string();
        $limit = $query->has('limit') ? $query->retrieve('limit', $string) : '';

        return [
            'userId' => $query->has('user_id') ? $query->retrieve('user_id', $string) : '',
            'limit' => is_numeric($limit) ? max(0, (int) $limit) : 0
        ];
    }

    protected function getClientIdFromToken(object $token): string
    {
        $clientId = $token->sub ?? '';
        if (!is_string($clientId) || $clientId === '') {
            throw new Exception('invalid request', 401);
        }
        return $clientId;
    }

    protected function getLtiUserId(int $privacyIdent, int $userId, string $userIdent): string
    {
        $userObj = new ilObjUser($userId);
        $ltiUserId = ilCmiXapiUser::getIdentAsId($privacyIdent, $userObj);
        if ($privacyIdent === ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_RANDOM) {
            $randomPart = strstr($userIdent, '@' . ilCmiXapiUser::getIliasUuid(), true);
            if ($randomPart !== false && $randomPart !== '') {
                $ltiUserId = $randomPart;
            }
        }
        return $ltiUserId !== '' ? $ltiUserId : $userIdent;
    }
}
