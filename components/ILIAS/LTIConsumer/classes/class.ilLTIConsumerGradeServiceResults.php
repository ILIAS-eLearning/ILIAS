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
    public function __construct(
        ilLTIConsumerServiceBase $service,
        private readonly ilLTIConsumerLineItemRepository $lineItemRepository
    )
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
                throw ilLTIConsumerHttpException::unauthorized();
            }
            $clientId = $token->getClientId();

            global $DIC;
            $ilDB = $DIC->database();
            $scoreMaximum = 1.0;
            $filters = $this->getFilters();
            $filterUserId = null;

            if (ilObject::_lookupType($itemId) === 'lti') {
                $object = new ilObjLTIConsumer($itemId, false);
                $provider = $object->getProvider();
                if (!$provider || (!$provider->isGradeSynchronization() && !$provider->getHasOutcome())) {
                    throw ilLTIConsumerHttpException::forbidden('grade synchronization not enabled');
                }
                if ($provider->getClientId() !== $clientId) {
                    throw ilLTIConsumerHttpException::forbidden('invalid clientId');
                }
                $scoreMaximum = $object->getScoreMaximum() > 0 ? $object->getScoreMaximum() : 1.0;
                $filterUserId = $filters['userId'] !== ''
                    ? $this->resolveUserIdFromLtiIdent($itemId, $filters['userId'])
                    : null;
            } else {
                $storedId = -$itemId;
                if ($storedId <= 0) {
                    throw ilLTIConsumerHttpException::notFound('LineItem not found');
                }

                $stored_line_item = $this->lineItemRepository->get($storedId, $contextId, $clientId);
                if ($stored_line_item === null) {
                    throw ilLTIConsumerHttpException::notFound('LineItem not found');
                }

                $lti_object = $this->getStoredLineItemLtiObject($contextId, $stored_line_item, $clientId);
                $filterUserId = $filters['userId'] !== ''
                    ? $this->resolveUserIdFromLtiIdent($lti_object->getId(), $filters['userId'])
                    : null;
                $results = $this->getStoredResults(
                    $ilDB,
                    $contextId,
                    $itemId,
                    $stored_line_item,
                    $lti_object->getId(),
                    $lti_object->getProvider()->getPrivacyIdent(),
                    $filterUserId,
                    $filters
                );
                $response->setContentType('application/vnd.ims.lis.v2.resultcontainer+json');
                $this->addNextPageHeader($response, $contextId, $itemId, $filters, $results['has_next_page']);
                $response->setBody(json_encode($results['items'], JSON_UNESCAPED_SLASHES));
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

            $offset = ($filters['page'] - 1) * $filters['limit'];
            $has_next_page = $filters['limit'] > 0 && count($resultsArr) > $offset + $filters['limit'];
            if ($filters['limit'] > 0) {
                $resultsArr = array_slice($resultsArr, $offset, $filters['limit']);
            }

            $response->setContentType('application/vnd.ims.lis.v2.resultcontainer+json');
            $this->addNextPageHeader($response, $contextId, $itemId, $filters, $has_next_page);
            $response->setBody(json_encode($resultsArr, JSON_UNESCAPED_SLASHES));
        } catch (ilLTIConsumerHttpException $e) {
            $response->setCode($e->getCode());
            $response->setReason($e->getMessage());
        } catch (Throwable $e) {
            $response->setCode(500);
            $response->setReason($e->getMessage());
        }
    }

    /**
     * @return array{userId: string, limit: int, page: int}
     */
    protected function getFilters(): array
    {
        global $DIC;

        $query = $DIC->http()->wrapper()->query();
        $string = $DIC->refinery()->kindlyTo()->string();
        $limit = $query->has('limit') ? $query->retrieve('limit', $string) : '';

        return [
            'userId' => $query->has('user_id') ? $query->retrieve('user_id', $string) : '',
            'limit' => is_numeric($limit) ? max(0, (int) $limit) : 0,
            'page' => $query->has('page') && is_numeric($query->retrieve('page', $string))
                ? max(1, (int) $query->retrieve('page', $string)) : 1
        ];
    }

    protected function getStoredLineItemLtiObject(
        int $context_id,
        ilLTIConsumerLineItem $line_item,
        string $client_id
    ): ilObjLTIConsumer {
        $resource_link_ref_id = (int) $line_item->resourceLinkId;
        if ($resource_link_ref_id <= 0) {
            throw ilLTIConsumerHttpException::notFound('Resource link not found');
        }

        global $DIC;
        $tree = $DIC->repositoryTree();
        $node_data = $tree->getNodeData($resource_link_ref_id);
        if (!$node_data || $node_data['type'] !== 'lti' ||
            !in_array($context_id, $tree->getPathId($resource_link_ref_id), true)) {
            throw ilLTIConsumerHttpException::notFound('Resource link not found');
        }

        $lti_object_id = ilObject::_lookupObjId($resource_link_ref_id);
        if (!$lti_object_id) {
            throw ilLTIConsumerHttpException::notFound('Resource link not found');
        }

        $lti_object = new ilObjLTIConsumer($lti_object_id, false);
        $provider = $lti_object->getProvider();
        if (!$provider || $provider->getClientId() !== $client_id) {
            throw ilLTIConsumerHttpException::forbidden('invalid clientId');
        }

        return $lti_object;
    }

    /**
     * @param array{userId: string, limit: int, page: int} $filters
     * @return array{items: list<array<string, float|string>>, has_next_page: bool}
     */
    protected function getStoredResults(
        ilDBInterface $db,
        int $context_id,
        int $item_id,
        ilLTIConsumerLineItem $line_item,
        int $lti_object_id,
        int $privacy_ident,
        ?int $filter_user_id,
        array $filters
    ): array {
        $query = 'SELECT score_given, score_maximum, usr_id FROM lti_consumer_grades'
            . ' WHERE obj_id = ' . $db->quote($item_id, 'integer')
            . ' AND score_given IS NOT NULL'
            . ' AND score_maximum IS NOT NULL'
            . ' ORDER BY lti_timestamp DESC, stored DESC';
        $result = $db->query($query);
        $line_item_url = ilLTIConsumerGradeServiceLineItem::buildLineItemUrl($context_id, $item_id);
        $results = [];
        $seen_user_ids = [];
        while ($row = $db->fetchAssoc($result)) {
            $user_id = (int) $row['usr_id'];
            if (isset($seen_user_ids[$user_id]) ||
                ($filter_user_id !== null && $filter_user_id !== $user_id)) {
                continue;
            }

            $seen_user_ids[$user_id] = true;
            $ident_query = 'SELECT usr_ident FROM cmix_users'
                . ' WHERE obj_id = ' . $db->quote($lti_object_id, 'integer')
                . ' AND usr_id = ' . $db->quote($user_id, 'integer');
            $ident_result = $db->query($ident_query);
            $ident_row = $db->fetchAssoc($ident_result);
            $user_ident = $ident_row['usr_ident'] ?? (string) $user_id;
            if ($filter_user_id === null && $filters['userId'] !== '' &&
                !$this->matchesLtiUserIdent($lti_object_id, $user_id, $user_ident, $filters['userId'])) {
                continue;
            }

            $score_maximum = (float) $row['score_maximum'];
            $lti_user_id = $this->getLtiUserId($privacy_ident, $user_id, $user_ident);
            $results[] = [
                'id' => $line_item_url . '/results?user_id=' . rawurlencode($lti_user_id),
                'scoreOf' => $line_item_url,
                'userId' => $lti_user_id,
                'resultScore' => (float) $row['score_given'],
                'resultMaximum' => $score_maximum > 0 ? $score_maximum : $line_item->scoreMaximum
            ];
        }

        $offset = ($filters['page'] - 1) * $filters['limit'];
        $has_next_page = $filters['limit'] > 0 && count($results) > $offset + $filters['limit'];
        if ($filters['limit'] > 0) {
            $results = array_slice($results, $offset, $filters['limit']);
        }

        return ['items' => $results, 'has_next_page' => $has_next_page];
    }

    /** @param array{userId: string, limit: int, page: int} $filters */
    protected function addNextPageHeader(
        ilLTIConsumerServiceResponse $response,
        int $context_id,
        int $item_id,
        array $filters,
        bool $has_next_page
    ): void {
        if (!$has_next_page) {
            return;
        }

        $query = ['limit' => $filters['limit'], 'page' => $filters['page'] + 1];
        if ($filters['userId'] !== '') {
            $query['user_id'] = $filters['userId'];
        }
        $url = ilLTIConsumerGradeServiceLineItem::buildLineItemUrl($context_id, $item_id) . '/results';
        $response->addAdditionalHeader('Link: <' . $url . '?' . http_build_query($query) . '>; rel="next"');
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
