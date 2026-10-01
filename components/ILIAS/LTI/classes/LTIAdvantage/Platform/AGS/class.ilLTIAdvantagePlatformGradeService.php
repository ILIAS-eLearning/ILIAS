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
 * The Assignment and Grade Services of ILIAS as LTI Advantage platform (ltiservices.php): each LTI object
 * is one line item, which its tool reads and posts the scores of the users to. The tool authenticates with
 * an access token of ltitoken.php. A score updates the result and the learning progress of the user.
 *
 * The URLs are those of earlier releases, since tools keep the line item URL of a launch. Line items a tool
 * created itself in earlier releases are not served, and neither are the creation, change or removal of
 * line items.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTIAdvantagePlatformGradeService
{
    public const string SCOPE_LINEITEM = 'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem';
    public const string SCOPE_LINEITEM_READ = 'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem.readonly';
    public const string SCOPE_RESULT_READ = 'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly';
    public const string SCOPE_SCORE = 'https://purl.imsglobal.org/spec/lti-ags/scope/score';

    private const string MEDIA_TYPE_LINEITEM = 'application/vnd.ims.lis.v2.lineitem+json';
    private const string MEDIA_TYPE_LINEITEMS = 'application/vnd.ims.lis.v2.lineitemcontainer+json';
    private const string MEDIA_TYPE_RESULTS = 'application/vnd.ims.lis.v2.resultcontainer+json';
    private const string MEDIA_TYPE_SCORE = 'application/vnd.ims.lis.v1.score+json';

    private const string PATH_PATTERN = '@^/gradeservice/(\d+)/lineitems(?:/(\d+)/lineitem(/scores|/results)?)?/?$@';
    private const string TIMESTAMP_PATTERN = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d+)?(Z|[+-]\d{2}:?\d{2})$/';
    private const string GRADES_TABLE = 'lti_consumer_grades';
    private const string RESULTS_TABLE = 'lti_consumer_results';

    /**
     * The parameters of a launch that give the tool the line item of the object, which celtic/lti turns
     * into the claim of the service. None when the tool does not report grades or the object has no context.
     *
     * @return array
     */
    public static function getLaunchParameters(ilObjLTITool $object, int $context_ref_id): array
    {
        $tool = $object->getTool();
        if ($context_ref_id <= 0 || (!$tool->hasOutcome() && !$tool->isGradeSynchronization())) {
            return [];
        }

        return [
            'custom_lineitems_url' => self::getLineItemsUrl($context_ref_id),
            'custom_lineitem_url' => self::getLineItemUrl($context_ref_id, $object->getId()),
            'custom_ags_scopes' => implode(',', [self::SCOPE_LINEITEM_READ, self::SCOPE_RESULT_READ, self::SCOPE_SCORE]),
        ];
    }

    /**
     * The URLs are built from the configured HTTP path: under ltiservices.php, ILIAS_HTTP_PATH ends in the
     * script, and the id of a line item the tool reads back must be the URL of the launch.
     */
    private static function getLineItemsUrl(int $context_ref_id): string
    {
        global $DIC;

        $http_path = ilContext::modifyHttpPath((string) $DIC->iliasIni()->readVariable('server', 'http_path'));

        return rtrim($http_path, '/') . '/ltiservices.php/gradeservice/' . $context_ref_id . '/lineitems';
    }

    private static function getLineItemUrl(int $context_ref_id, int $obj_id): string
    {
        return self::getLineItemsUrl($context_ref_id) . '/' . $obj_id . '/lineitem';
    }

    /**
     * Answers one request to the service.
     *
     * @param string $path the path after ltiservices.php
     * @param array $query the query parameters
     * @return array the HTTP status, the headers and the body of the response
     */
    public function handle(
        string $method,
        string $path,
        string $authorization,
        string $content_type,
        array $query,
        string $body
    ): array {
        try {
            if (preg_match(self::PATH_PATTERN, $path, $matches) !== 1) {
                throw new DomainException('Unknown service path ' . $path, 404);
            }
            $context_ref_id = (int) $matches[1];
            $obj_id = (int) ($matches[2] ?? 0);
            $resource = $obj_id === 0 ? 'lineitems' : ltrim($matches[3] ?? '/lineitem', '/');
            $expected_method = $resource === 'scores' ? 'POST' : 'GET';
            if (strtoupper($method) !== $expected_method) {
                return [405, ['Allow' => $expected_method, 'Content-Type' => 'application/json; charset=utf-8'], json_encode([
                    'error' => 'The resource only accepts ' . $expected_method
                ])];
            }

            if ($resource === 'lineitems') {
                $tool = $this->authorize($authorization, [self::SCOPE_LINEITEM, self::SCOPE_LINEITEM_READ]);
                return $this->respond(self::MEDIA_TYPE_LINEITEMS, $this->getLineItems($context_ref_id, $tool, $query));
            }

            $tool = $this->authorize(
                $authorization,
                match ($resource) {
                    'scores' => [self::SCOPE_SCORE],
                    'results' => [self::SCOPE_RESULT_READ],
                    default => [self::SCOPE_LINEITEM, self::SCOPE_LINEITEM_READ],
                }
            );
            $object = $this->getObject($context_ref_id, $obj_id, $tool);

            return match ($resource) {
                'scores' => $this->postScore($object, $content_type, $body),
                'results' => $this->respond(self::MEDIA_TYPE_RESULTS, $this->getResults($context_ref_id, $object, $query)),
                default => $this->respond(self::MEDIA_TYPE_LINEITEM, $this->getLineItem($context_ref_id, $object)),
            };
        } catch (DomainException $e) {
            $this->log()->warning('LTI Advantage grade service request refused: ' . $e->getMessage());
            $headers = $e->getCode() === 401 ? ['WWW-Authenticate' => 'Bearer error="invalid_token"'] : [];

            return [$e->getCode(), $headers + ['Content-Type' => 'application/json; charset=utf-8'], json_encode([
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_SLASHES)];
        }
    }

    /**
     * The tool an access token of ILIAS was given to, when the token grants one of the scopes. Of the tokens
     * ILIAS signs, only access tokens carry a scope.
     *
     * @param array $scopes
     * @throws ilException when ILIAS has no key
     * @throws \Random\RandomException
     */
    private function authorize(string $authorization, array $scopes): ilLTITool
    {
        if (preg_match('/^Bearer\s+(\S+)$/i', trim($authorization), $matches) !== 1) {
            throw new DomainException('No access token', 401);
        }
        $payload = ilLTIAdvantageKeyPair::verify($matches[1]);
        $granted = $payload['imsglobal.org.security.scope'] ?? null;
        $client_id = $payload['sub'] ?? null;
        if (!is_string($granted) || !is_string($client_id)) {
            throw new DomainException('Invalid access token', 401);
        }

        $tool_id = ilLTITool::lookupIdByClientId($client_id);
        if ($tool_id === 0) {
            throw new DomainException('Unknown client ' . $client_id, 401);
        }
        if (array_intersect($scopes, explode(' ', $granted)) === []) {
            throw new DomainException('The access token does not grant ' . implode(' or ', $scopes), 403);
        }

        $tool = new ilLTITool($tool_id);
        if ($tool->getAvailability() === ilLTITool::AVAILABILITY_NONE) {
            throw new DomainException('The tool ' . $tool_id . ' is not available', 403);
        }
        if (!$tool->hasOutcome() && !$tool->isGradeSynchronization()) {
            throw new DomainException('The tool ' . $tool_id . ' does not report grades', 403);
        }

        return $tool;
    }

    /**
     * The object of the tool in the context, at a reference that is in the repository and inside the context.
     */
    private function getObject(int $context_ref_id, int $obj_id, ilLTITool $tool): ilObjLTITool
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        if (ilObject::_lookupType($obj_id) === 'lti') {
            foreach (ilObject::_getAllReferences($obj_id) as $ref_id) {
                if ($tree->isInTree($ref_id) && in_array($context_ref_id, $tree->getPathId($ref_id), true)) {
                    $object = new ilObjLTITool($ref_id);
                    if ($object->getToolId() === $tool->getId()) {
                        return $object;
                    }
                }
            }
        }

        throw new DomainException('No line item ' . $obj_id . ' of the tool ' . $tool->getId() . ' in ' . $context_ref_id, 404);
    }

    /**
     * @return array
     */
    private function getLineItem(int $context_ref_id, ilObjLTITool $object): array
    {
        return [
            'id' => self::getLineItemUrl($context_ref_id, $object->getId()),
            'label' => $object->getTitle(),
            'scoreMaximum' => $object->getScoreMaximum(),
            'resourceId' => (string) $object->getId(),
            'resourceLinkId' => ilLTIAdvantagePlatformLaunchParameterBuilder::getResourceLinkId($object),
        ];
    }

    /**
     * The line items of the tool in the context, filtered as the service defines. They have no tags.
     *
     * @param array $query
     * @return array
     */
    private function getLineItems(int $context_ref_id, ilLTITool $tool, array $query): array
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        if (!$tree->isInTree($context_ref_id) || (string) ($query['tag'] ?? '') !== '') {
            return [];
        }

        $line_items = [];
        foreach ($tree->getSubTree($tree->getNodeData($context_ref_id), true, ['lti']) as $node) {
            $obj_id = (int) $node['obj_id'];
            if (isset($line_items[$obj_id])) {
                continue;
            }
            $object = new ilObjLTITool((int) $node['child']);
            if ($object->getToolId() !== $tool->getId()) {
                continue;
            }
            $line_item = $this->getLineItem($context_ref_id, $object);
            if (
                (isset($query['resource_link_id']) && $query['resource_link_id'] !== $line_item['resourceLinkId'])
                || (isset($query['resource_id']) && $query['resource_id'] !== $line_item['resourceId'])
            ) {
                continue;
            }
            $line_items[$obj_id] = $line_item;
        }

        return array_values($line_items);
    }

    /**
     * The current result of each user with a score, on the scale of the line item.
     *
     * @param array $query
     * @return array
     */
    private function getResults(int $context_ref_id, ilObjLTITool $object, array $query): array
    {
        global $DIC;

        $db = $DIC->database();
        $line_item_url = self::getLineItemUrl($context_ref_id, $object->getId());
        $privacy_ident = $object->getTool()->getPrivacyIdent();
        $filter = isset($query['user_id']) ? (string) $query['user_id'] : null;

        $rows = $db->queryF(
            'SELECT usr_id, score_given, score_maximum FROM ' . self::GRADES_TABLE . ' WHERE obj_id = %s'
            . ' ORDER BY usr_id, lti_timestamp DESC, stored DESC, id DESC',
            ['integer'],
            [$object->getId()]
        );
        $results = [];
        $seen = [];
        while ($row = $db->fetchAssoc($rows)) {
            $usr_id = (int) $row['usr_id'];
            if (isset($seen[$usr_id])) {
                continue;
            }
            $seen[$usr_id] = true;

            $cmix_user = new ilCmiXapiUser($object->getId(), $usr_id, $privacy_ident);
            if ($row['score_given'] === null || (float) $row['score_maximum'] <= 0 || $cmix_user->getUsrIdent() === '' || !ilObjUser::_exists($usr_id)) {
                continue;
            }
            $user_id = ilLTIAdvantagePlatformLaunchParameterBuilder::getUserId(
                $privacy_ident,
                $cmix_user->getUsrIdent(),
                new ilObjUser($usr_id)
            );
            if ($filter !== null && $filter !== $user_id) {
                continue;
            }

            $results[] = [
                'id' => $line_item_url . '/results?user_id=' . rawurlencode($user_id),
                'scoreOf' => $line_item_url,
                'userId' => $user_id,
                'resultScore' => (float) $row['score_given'] / (float) $row['score_maximum'] * $object->getScoreMaximum(),
                'resultMaximum' => $object->getScoreMaximum(),
            ];
        }

        return $results;
    }

    /**
     * Keeps the score and, unless a later one is already kept, turns it into the result and the learning
     * progress of the user: a fully graded score is the result, a score without scoreGiven clears it.
     *
     * @return array
     */
    private function postScore(ilObjLTITool $object, string $content_type, string $body): array
    {
        if (strtolower(trim(explode(';', $content_type)[0])) !== self::MEDIA_TYPE_SCORE) {
            throw new DomainException('A score is posted as ' . self::MEDIA_TYPE_SCORE, 415);
        }

        $score = json_decode($body, true);
        if (!is_array($score)) {
            throw new DomainException('Invalid score', 400);
        }
        $user_id = $score['userId'] ?? null;
        $activity_progress = ilLTIToolActivityProgress::tryFrom((string) ($score['activityProgress'] ?? ''));
        $grading_progress = ilLTIToolGradingProgress::tryFrom((string) ($score['gradingProgress'] ?? ''));
        $timestamp = $this->parseTimestamp($score['timestamp'] ?? null);
        $given = $score['scoreGiven'] ?? null;
        $maximum = $score['scoreMaximum'] ?? null;
        if (
            !(is_string($user_id) || is_int($user_id)) || (string) $user_id === ''
            || $activity_progress === null
            || $grading_progress === null
            || $timestamp === null
            || ($given !== null && (!(is_int($given) || is_float($given)) || $given < 0 || $maximum === null))
            || ($maximum !== null && (!(is_int($maximum) || is_float($maximum)) || $maximum <= 0))
        ) {
            throw new DomainException('Invalid score', 400);
        }

        $usr_id = $this->findUser($object, (string) $user_id);
        if ($usr_id === 0) {
            throw new DomainException('The user ' . $user_id . ' has not launched the object ' . $object->getId(), 404);
        }

        global $DIC;

        $db = $DIC->database();
        $obj_id = $object->getId();
        $latest = $db->fetchAssoc($db->queryF(
            'SELECT MAX(lti_timestamp) latest FROM ' . self::GRADES_TABLE . ' WHERE obj_id = %s AND usr_id = %s',
            ['integer', 'integer'],
            [$obj_id, $usr_id]
        ));
        $timestamp = $timestamp->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('Y-m-d H:i:s');
        $is_current = ($latest['latest'] ?? null) === null || $timestamp >= $latest['latest'];

        $db->insert(self::GRADES_TABLE, [
            'id' => ['integer', $db->nextId(self::GRADES_TABLE)],
            'obj_id' => ['integer', $obj_id],
            'usr_id' => ['integer', $usr_id],
            'score_given' => ['float', $given === null ? null : (float) $given],
            'score_maximum' => ['float', $maximum === null ? null : (float) $maximum],
            'activity_progress' => ['text', $activity_progress->value],
            'grading_progress' => ['text', $grading_progress->value],
            'lti_timestamp' => ['timestamp', $timestamp],
            'stored' => ['timestamp', date('Y-m-d H:i:s')],
        ]);

        if ($is_current && ($given === null || $grading_progress === ilLTIToolGradingProgress::FULLY_GRADED)) {
            $this->writeResult($obj_id, $usr_id, $given === null ? null : min(1.0, (float) $given / (float) $maximum));
        }
        if ($is_current) {
            ilLPStatusWrapper::_updateStatus($obj_id, $usr_id);
        }

        return [204, [], ''];
    }

    private function parseTimestamp(mixed $timestamp): ?DateTimeImmutable
    {
        if (!is_string($timestamp) || preg_match(self::TIMESTAMP_PATTERN, $timestamp) !== 1) {
            return null;
        }
        try {
            return new DateTimeImmutable($timestamp);
        } catch (Exception) {
            return null;
        }
    }

    /**
     * The ILIAS user the tool knows by the user id of the launches of the object. The candidates are the
     * identities the launches stored for the object, and each is checked by computing the user id again.
     */
    private function findUser(ilObjLTITool $object, string $user_id): int
    {
        global $DIC;

        $db = $DIC->database();
        $privacy_ident = $object->getTool()->getPrivacyIdent();
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $user_id);
        $condition = 'usr_ident = ' . $db->quote($user_id, 'text')
            . ' OR ' . $db->like('usr_ident', 'text', $escaped . '@%', false);
        if (preg_match('/^realemail(\d+)$/', $user_id, $matches) === 1) {
            $condition .= ' OR usr_id = ' . $db->quote((int) $matches[1], 'integer');
        }

        // not queryF(): the pattern of the LIKE holds a %
        $rows = $db->query(
            'SELECT usr_id, usr_ident FROM cmix_users WHERE obj_id = ' . $db->quote($object->getId(), 'integer')
            . ' AND privacy_ident = ' . $db->quote($privacy_ident, 'integer') . ' AND (' . $condition . ')'
        );
        while ($row = $db->fetchAssoc($rows)) {
            $usr_id = (int) $row['usr_id'];
            if (
                ilObjUser::_exists($usr_id)
                && ilLTIAdvantagePlatformLaunchParameterBuilder::getUserId(
                    $privacy_ident,
                    (string) $row['usr_ident'],
                    new ilObjUser($usr_id)
                ) === $user_id
            ) {
                return $usr_id;
            }
        }

        return 0;
    }

    private function writeResult(int $obj_id, int $usr_id, ?float $result): void
    {
        global $DIC;

        $db = $DIC->database();
        $row = $db->fetchAssoc($db->queryF(
            'SELECT id FROM ' . self::RESULTS_TABLE . ' WHERE obj_id = %s AND usr_id = %s',
            ['integer', 'integer'],
            [$obj_id, $usr_id]
        ));
        if ($row !== null) {
            $db->update(
                self::RESULTS_TABLE,
                ['result' => ['float', $result], 'attended' => ['integer', 1]],
                ['id' => ['integer', (int) $row['id']]]
            );
        } elseif ($result !== null) {
            $db->insert(self::RESULTS_TABLE, [
                'id' => ['integer', $db->nextId(self::RESULTS_TABLE)],
                'obj_id' => ['integer', $obj_id],
                'usr_id' => ['integer', $usr_id],
                'result' => ['float', $result],
                'attended' => ['integer', 1],
            ]);
        }
    }

    /**
     * @param array $data
     * @return array
     */
    private function respond(string $media_type, array $data): array
    {
        return [200, ['Content-Type' => $media_type], json_encode($data, JSON_UNESCAPED_SLASHES)];
    }

    private function log(): ilLogger
    {
        global $DIC;

        return $DIC->logger()->forComponent('lti');
    }
}
