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

use ceLTIc\LTI\Service\LineItem;
use ceLTIc\LTI\Service\Result;
use ceLTIc\LTI\Service\Score;

/**
 * The Assignment and Grade Services of ILIAS as LTI Advantage platform (ltiservices.php). Each LTI object is
 * a line item, which its tool reads, changes and posts the scores of the users to: a score updates the
 * result and the learning progress of the user. A tool may also create line items of its own in the context
 * of its objects, which only keep the scores (see ilLTIAdvantagePlatformLineItemRepository). The tool
 * authenticates with an access token of ltitoken.php.
 *
 * The URLs are those of earlier releases, since tools keep the line item URL of a launch: the line item of an
 * object is addressed by the object id, one the tool created by its id with a minus sign. Requests of tools
 * set up for earlier releases are taken as those releases took them: a score may give its numbers as
 * strings and its timestamp without time zone, an object may have moved out of the context of its URL and a
 * user is found under the privacy settings of any of their launches.
 *
 * Lists are answered whole, or by pages when the tool gives a limit.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTIAdvantagePlatformGradeService
{
    private const string PATH_PATTERN = '@^/gradeservice/(\d+)/lineitems(?:/(-?\d+)/lineitem(/scores|/results)?)?/?$@';
    private const string TIMESTAMP_PATTERN = '/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|[+-]\d{2}(:?\d{2})?)?$/i';
    private const string GRADES_TABLE = 'lti_consumer_grades';
    private const string RESULTS_TABLE = 'lti_consumer_results';
    private const array METHODS = [
        'lineitems' => ['GET', 'POST'],
        'lineitem' => ['GET', 'PUT', 'DELETE'],
        'scores' => ['POST'],
        'results' => ['GET'],
    ];

    private readonly ilDBInterface $db;
    private readonly ilLTIAdvantagePlatformLineItemRepository $line_items;

    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->line_items = new ilLTIAdvantagePlatformLineItemRepository($this->db);
    }

    /**
     * The parameters of a launch that give the tool the line items of the context and the one of the object,
     * which celtic/lti turns into the claim of the service. None when the tool does not report grades or the
     * object has no context.
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
            'custom_ags_scopes' => implode(',', [LineItem::$SCOPE, LineItem::$SCOPE_READONLY, Result::$SCOPE, Score::$SCOPE]),
        ];
    }

    private static function getLineItemsUrl(int $context_ref_id): string
    {
        return ilLTIEndpoint::getServiceUrl('/gradeservice/' . $context_ref_id . '/lineitems');
    }

    /**
     * @param int $item_id the id of the object, or the id of a line item the tool created with a minus sign
     */
    private static function getLineItemUrl(int $context_ref_id, int $item_id): string
    {
        return self::getLineItemsUrl($context_ref_id) . '/' . $item_id . '/lineitem';
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
            if (preg_match(self::PATH_PATTERN, $path, $matches) !== 1 || ($matches[2] ?? '') === '0') {
                throw new DomainException('Unknown service path ' . $path, 404);
            }
            $context_ref_id = (int) $matches[1];
            $item_id = (int) ($matches[2] ?? 0);
            $resource = $item_id === 0 ? 'lineitems' : (ltrim($matches[3] ?? '', '/') ?: 'lineitem');
            $method = strtoupper($method);
            if (!in_array($method, self::METHODS[$resource], true)) {
                return ilLTIAdvantagePlatformServiceRequest::refuseMethod(implode(', ', self::METHODS[$resource]));
            }

            $tool = $this->authorize($authorization, match (true) {
                $resource === 'scores' => [Score::$SCOPE],
                $resource === 'results' => [Result::$SCOPE],
                $method === 'GET' => [LineItem::$SCOPE, LineItem::$SCOPE_READONLY],
                default => [LineItem::$SCOPE],
            });

            if ($resource === 'lineitems') {
                return $method === 'POST'
                    ? $this->createLineItem($context_ref_id, $tool, $body)
                    : $this->listLineItems($context_ref_id, $tool, $query);
            }

            if ($item_id > 0) {
                $object = ilLTIAdvantagePlatformServiceRequest::getObject($context_ref_id, $item_id, $tool, true);
                return match ($resource) {
                    'scores' => $this->postObjectScore($object, $body),
                    'results' => $this->listResults(
                        self::getLineItemUrl($context_ref_id, $item_id),
                        $object->getId(),
                        $object->getScoreMaximum(),
                        [$object],
                        $query
                    ),
                    default => match ($method) {
                        'PUT' => $this->changeObjectLineItem($context_ref_id, $object, $body),
                        'DELETE' => ilLTIAdvantagePlatformServiceRequest::refuseMethod('GET, PUT'),
                        default => $this->respondLineItem($this->describeObject($context_ref_id, $object)),
                    },
                };
            }

            $line_item = $this->line_items->get(-$item_id, $context_ref_id, $tool->getClientId())
                ?? throw new DomainException('No line item ' . $item_id . ' of the tool ' . $tool->getId() . ' in ' . $context_ref_id, 404);
            return match ($resource) {
                'scores' => $this->postLineItemScore($line_item, $tool, $body),
                'results' => $this->listResults(
                    self::getLineItemUrl($context_ref_id, $item_id),
                    $item_id,
                    $line_item['score_maximum'],
                    $this->getLineItemObjects($line_item, $tool),
                    $query
                ),
                default => match ($method) {
                    'PUT' => $this->changeLineItem($line_item, $tool, $body),
                    'DELETE' => $this->removeLineItem($line_item),
                    default => $this->respondLineItem($this->describeLineItem($line_item)),
                },
            };
        } catch (DomainException $e) {
            return ilLTIAdvantagePlatformServiceRequest::refuse('grade service', $e);
        }
    }

    /**
     * The tool of the access token, which has to report grades.
     *
     * @param array $scopes
     * @throws ilException when ILIAS has no key
     * @throws \Random\RandomException
     */
    private function authorize(string $authorization, array $scopes): ilLTITool
    {
        $tool = ilLTIAdvantagePlatformServiceRequest::authorize($authorization, $scopes);
        if (!$tool->hasOutcome() && !$tool->isGradeSynchronization()) {
            throw new DomainException('The tool ' . $tool->getId() . ' does not report grades', 403);
        }

        return $tool;
    }

    /**
     * @param array $line_item
     * @return array the HTTP status, the headers and the body of the response
     */
    private function respondLineItem(array $line_item, int $status = 200): array
    {
        return ilLTIAdvantagePlatformServiceRequest::respond(LineItem::MEDIA_TYPE_LINE_ITEM, $line_item, [], $status);
    }

    /**
     * @return array
     */
    private function describeObject(int $context_ref_id, ilObjLTITool $object): array
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
     * @param array $line_item
     * @return array
     */
    private function describeLineItem(array $line_item): array
    {
        return array_filter([
            'id' => self::getLineItemUrl($line_item['context_id'], -$line_item['id']),
            'label' => $line_item['label'],
            'scoreMaximum' => $line_item['score_maximum'],
            'resourceId' => $line_item['resource_id'],
            'resourceLinkId' => $line_item['resource_link_id'],
            'tag' => $line_item['tag'],
            'startDateTime' => $line_item['start_date_time'],
            'endDateTime' => $line_item['end_date_time'],
            'gradesReleased' => $line_item['grades_released'],
        ], static fn($value): bool => $value !== null && $value !== '');
    }

    /**
     * The objects of the tool in the context, by object id.
     *
     * @return ilObjLTITool[]
     */
    private function getObjects(int $context_ref_id, ilLTITool $tool): array
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        if (!$tree->isInTree($context_ref_id)) {
            return [];
        }

        $objects = [];
        foreach ($tree->getSubTree($tree->getNodeData($context_ref_id), true, ['lti']) as $node) {
            $obj_id = (int) $node['obj_id'];
            if (isset($objects[$obj_id])) {
                continue;
            }
            $object = new ilObjLTITool((int) $node['child']);
            if ($object->getToolId() === $tool->getId()) {
                $objects[$obj_id] = $object;
            }
        }

        return $objects;
    }

    /**
     * The objects whose launches tell the users of a line item the tool created: the one of its resource
     * link, or all objects of the tool in the context when it has none.
     *
     * @param array $line_item
     * @return ilObjLTITool[]
     */
    private function getLineItemObjects(array $line_item, ilLTITool $tool): array
    {
        $objects = $this->getObjects($line_item['context_id'], $tool);
        if ($line_item['resource_link_id'] === '') {
            return $objects;
        }
        $object = $this->findResourceLink($objects, $line_item['resource_link_id']);

        return $object === null ? [] : [$object];
    }

    /**
     * @param ilObjLTITool[] $objects
     */
    private function findResourceLink(array $objects, string $resource_link_id): ?ilObjLTITool
    {
        foreach ($objects as $object) {
            if (ilLTIAdvantagePlatformLaunchParameterBuilder::getResourceLinkId($object) === $resource_link_id) {
                return $object;
            }
        }

        return null;
    }

    /**
     * The line items of the tool in the context, its objects first, filtered as the service defines.
     *
     * @param array $query
     * @return array the HTTP status, the headers and the body of the response
     */
    private function listLineItems(int $context_ref_id, ilLTITool $tool, array $query): array
    {
        $line_items = array_map(
            fn(ilObjLTITool $object): array => $this->describeObject($context_ref_id, $object),
            array_values($this->getObjects($context_ref_id, $tool))
        );
        foreach ($this->line_items->getAll($context_ref_id, $tool->getClientId()) as $line_item) {
            $line_items[] = $this->describeLineItem($line_item);
        }
        $filters = ['tag' => 'tag', 'resource_id' => 'resourceId', 'resource_link_id' => 'resourceLinkId'];
        foreach ($filters as $parameter => $field) {
            if ((string) ($query[$parameter] ?? '') !== '') {
                $line_items = array_filter(
                    $line_items,
                    static fn(array $line_item): bool => ($line_item[$field] ?? '') === (string) $query[$parameter]
                );
            }
        }

        [$line_items, $headers] = ilLTIAdvantagePlatformServiceRequest::paginate(
            array_values($line_items),
            $query,
            self::getLineItemsUrl($context_ref_id)
        );

        return ilLTIAdvantagePlatformServiceRequest::respond(LineItem::MEDIA_TYPE_LINE_ITEMS, $line_items, $headers);
    }

    /**
     * A line item of the tool itself, in a context where it has an object.
     *
     * @return array the HTTP status, the headers and the body of the response
     */
    private function createLineItem(int $context_ref_id, ilLTITool $tool, string $body): array
    {
        $objects = $this->getObjects($context_ref_id, $tool);
        if ($objects === []) {
            throw new DomainException('The tool ' . $tool->getId() . ' has no object in ' . $context_ref_id, 403);
        }
        $data = $this->decode($body, 'line item');
        if (!isset($data['label'], $data['scoreMaximum'])) {
            throw new DomainException('A line item needs a label and a scoreMaximum', 400);
        }

        $line_item = $this->line_items->create($this->withChanges([
            'context_id' => $context_ref_id,
            'client_id' => $tool->getClientId(),
            'label' => '',
            'score_maximum' => 1.0,
            'resource_id' => '',
            'resource_link_id' => '',
            'tag' => '',
            'start_date_time' => null,
            'end_date_time' => null,
            'grades_released' => null,
        ], $data, $objects));

        return $this->respondLineItem($this->describeLineItem($line_item), 201);
    }

    /**
     * @param array $line_item
     * @return array the HTTP status, the headers and the body of the response
     */
    private function changeLineItem(array $line_item, ilLTITool $tool, string $body): array
    {
        $line_item = $this->withChanges(
            $line_item,
            $this->decode($body, 'line item'),
            $this->getObjects($line_item['context_id'], $tool)
        );
        $this->line_items->update($line_item);

        return $this->respondLineItem($this->describeLineItem($line_item));
    }

    /**
     * The line item with the fields the tool sent. A resource link has to be one of the objects of the tool.
     *
     * @param array $line_item
     * @param array $data
     * @param ilObjLTITool[] $objects
     * @return array
     */
    private function withChanges(array $line_item, array $data, array $objects): array
    {
        if (array_key_exists('label', $data)) {
            if (!is_string($data['label']) || trim($data['label']) === '') {
                throw new DomainException('Invalid label', 400);
            }
            $line_item['label'] = $data['label'];
        }
        if (array_key_exists('scoreMaximum', $data)) {
            $maximum = $this->toNumber($data['scoreMaximum']);
            if ($maximum === null || $maximum <= 0) {
                throw new DomainException('Invalid scoreMaximum', 400);
            }
            $line_item['score_maximum'] = $maximum;
        }
        foreach (['resourceId' => 'resource_id', 'resourceLinkId' => 'resource_link_id', 'tag' => 'tag'] as $field => $key) {
            if (array_key_exists($field, $data)) {
                if (!is_scalar($data[$field]) && $data[$field] !== null) {
                    throw new DomainException('Invalid ' . $field, 400);
                }
                $line_item[$key] = (string) $data[$field];
            }
        }
        if ($line_item['resource_link_id'] !== '' && $this->findResourceLink($objects, $line_item['resource_link_id']) === null) {
            throw new DomainException('No resource link ' . $line_item['resource_link_id'] . ' of the tool', 404);
        }
        foreach (['startDateTime' => 'start_date_time', 'endDateTime' => 'end_date_time'] as $field => $key) {
            if (array_key_exists($field, $data)) {
                if ($data[$field] !== null && $this->parseTimestamp($data[$field]) === null) {
                    throw new DomainException('Invalid ' . $field, 400);
                }
                $line_item[$key] = $data[$field];
            }
        }
        if (array_key_exists('gradesReleased', $data)) {
            if (!is_bool($data['gradesReleased']) && $data['gradesReleased'] !== null) {
                throw new DomainException('Invalid gradesReleased', 400);
            }
            $line_item['grades_released'] = $data['gradesReleased'];
        }

        return $line_item;
    }

    /**
     * The line item of an object takes the label as title and the maximum score, as in earlier releases.
     *
     * @return array the HTTP status, the headers and the body of the response
     */
    private function changeObjectLineItem(int $context_ref_id, ilObjLTITool $object, string $body): array
    {
        $data = $this->decode($body, 'line item');
        $line_item = $this->withChanges(
            ['label' => $object->getTitle(), 'score_maximum' => $object->getScoreMaximum(), 'resource_link_id' => ''],
            array_intersect_key($data, ['label' => true, 'scoreMaximum' => true]),
            []
        );
        $object->setTitle($line_item['label']);
        $object->setScoreMaximum($line_item['score_maximum']);
        $object->update();

        return $this->respondLineItem($this->describeObject($context_ref_id, $object));
    }

    /**
     * @param array $line_item
     * @return array the HTTP status, the headers and the body of the response
     */
    private function removeLineItem(array $line_item): array
    {
        $this->line_items->disable($line_item['id']);

        return [204, [], ''];
    }

    /**
     * @return array
     */
    private function decode(string $body, string $what): array
    {
        $data = json_decode($body, true);
        if (!is_array($data) || ($data !== [] && array_is_list($data))) {
            throw new DomainException('Invalid ' . $what, 400);
        }

        return $data;
    }

    /**
     * A number, also given as a numeric string as tools of earlier releases may do. Null when it is none.
     */
    private function toNumber(mixed $value): ?float
    {
        return is_int($value) || is_float($value) || (is_string($value) && is_numeric($value)) ? (float) $value : null;
    }

    /**
     * The current result of each user with a score on the line item, on its scale.
     *
     * @param int $grade_obj_id the id the scores of the line item are kept under
     * @param ilObjLTITool[] $objects the objects whose launches tell the users
     * @param array $query
     * @return array the HTTP status, the headers and the body of the response
     */
    private function listResults(string $line_item_url, int $grade_obj_id, float $score_maximum, array $objects, array $query): array
    {
        $filter = isset($query['user_id']) ? (string) $query['user_id'] : null;
        $rows = $this->db->queryF(
            'SELECT usr_id, score_given, score_maximum FROM ' . self::GRADES_TABLE . ' WHERE obj_id = %s'
            . ' ORDER BY usr_id, lti_timestamp DESC, stored DESC, id DESC',
            ['integer'],
            [$grade_obj_id]
        );
        $results = [];
        $seen = [];
        while ($row = $this->db->fetchAssoc($rows)) {
            $usr_id = (int) $row['usr_id'];
            if (isset($seen[$usr_id])) {
                continue;
            }
            $seen[$usr_id] = true;
            // the latest score without scoreGiven cleared the result
            if ($row['score_given'] === null || (float) $row['score_maximum'] <= 0) {
                continue;
            }
            $user_id = $this->getToolUserId($objects, $usr_id);
            if ($user_id === null || ($filter !== null && $filter !== $user_id)) {
                continue;
            }

            $results[] = [
                'id' => $line_item_url . '/results?user_id=' . rawurlencode($user_id),
                'scoreOf' => $line_item_url,
                'userId' => $user_id,
                'resultScore' => (float) $row['score_given'] / (float) $row['score_maximum'] * $score_maximum,
                'resultMaximum' => $score_maximum,
            ];
        }

        [$results, $headers] = ilLTIAdvantagePlatformServiceRequest::paginate($results, $query, $line_item_url . '/results');

        return ilLTIAdvantagePlatformServiceRequest::respond(Result::MEDIA_TYPE_RESULT, $results, $headers);
    }

    /**
     * Keeps the score and, unless a later one is already kept, turns it into the result and the learning
     * progress of the user: a fully graded score is the result, a score without scoreGiven clears it.
     *
     * @return array the HTTP status, the headers and the body of the response
     */
    private function postObjectScore(ilObjLTITool $object, string $body): array
    {
        $score = $this->parseScore($body);
        $usr_id = $this->findUser([$object], $score['user_id']);
        $obj_id = $object->getId();

        if ($this->storeScore($obj_id, $usr_id, $score)) {
            $given = $score['given'];
            if ($given === null || $score['grading_progress'] === ilLTIToolGradingProgress::FULLY_GRADED) {
                $this->writeResult($obj_id, $usr_id, $given === null ? null : min(1.0, $given / $score['maximum']));
            }
            ilLPStatusWrapper::_updateStatus($obj_id, $usr_id);
        }

        return [204, [], ''];
    }

    /**
     * The scores of a line item the tool created are only kept.
     *
     * @param array $line_item
     * @return array the HTTP status, the headers and the body of the response
     */
    private function postLineItemScore(array $line_item, ilLTITool $tool, string $body): array
    {
        $score = $this->parseScore($body);
        $this->storeScore(-$line_item['id'], $this->findUser($this->getLineItemObjects($line_item, $tool), $score['user_id']), $score);

        return [204, [], ''];
    }

    /**
     * @return array the user id, the progress, the timestamp and the numbers of a score
     */
    private function parseScore(string $body): array
    {
        $score = $this->decode($body, 'score');
        $user_id = $score['userId'] ?? null;
        $activity_progress = ilLTIToolActivityProgress::tryFrom((string) ($score['activityProgress'] ?? ''));
        $grading_progress = ilLTIToolGradingProgress::tryFrom((string) ($score['gradingProgress'] ?? ''));
        $timestamp = $this->parseTimestamp($score['timestamp'] ?? null);
        $given = $this->toNumber($score['scoreGiven'] ?? null);
        $maximum = $this->toNumber($score['scoreMaximum'] ?? null);
        if (
            !(is_string($user_id) || is_int($user_id)) || (string) $user_id === ''
            || $activity_progress === null
            || $grading_progress === null
            || $timestamp === null
            || (isset($score['scoreGiven']) && ($given === null || $given < 0 || $maximum === null))
            || (isset($score['scoreMaximum']) && ($maximum === null || $maximum <= 0))
        ) {
            throw new DomainException('Invalid score', 400);
        }

        return [
            'user_id' => (string) $user_id,
            'activity_progress' => $activity_progress,
            'grading_progress' => $grading_progress,
            'timestamp' => $timestamp->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('Y-m-d H:i:s'),
            'given' => $given,
            'maximum' => $maximum,
        ];
    }

    /**
     * A timestamp of ISO 8601. Without time zone, it is taken in the one of ILIAS.
     */
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
     * Keeps the score of the user.
     *
     * @param array $score see parseScore()
     * @return bool true when no later score of the user is kept, so that this one is the current one
     */
    private function storeScore(int $grade_obj_id, int $usr_id, array $score): bool
    {
        $latest = $this->db->fetchAssoc($this->db->queryF(
            'SELECT MAX(lti_timestamp) latest FROM ' . self::GRADES_TABLE . ' WHERE obj_id = %s AND usr_id = %s',
            ['integer', 'integer'],
            [$grade_obj_id, $usr_id]
        ));

        $this->db->insert(self::GRADES_TABLE, [
            'id' => ['integer', $this->db->nextId(self::GRADES_TABLE)],
            'obj_id' => ['integer', $grade_obj_id],
            'usr_id' => ['integer', $usr_id],
            'score_given' => ['float', $score['given']],
            'score_maximum' => ['float', $score['maximum']],
            'activity_progress' => ['text', $score['activity_progress']->value],
            'grading_progress' => ['text', $score['grading_progress']->value],
            'lti_timestamp' => ['timestamp', $score['timestamp']],
            'stored' => ['timestamp', date('Y-m-d H:i:s')],
        ]);

        return ($latest['latest'] ?? null) === null || $score['timestamp'] >= $latest['latest'];
    }

    /**
     * The identities the launches of the objects stored for users. With a user id, only those that may give
     * it: the user id itself, or its part before the @ of a random identity, or the id of the user for the
     * real email.
     *
     * @param ilObjLTITool[] $objects
     * @return array[] rows with obj_id, usr_id, usr_ident and privacy_ident
     */
    private function getIdentities(array $objects, ?int $usr_id = null, ?string $user_id = null): array
    {
        if ($objects === []) {
            return [];
        }
        $obj_ids = array_map(static fn(ilObjLTITool $object): int => $object->getId(), $objects);
        $condition = $this->db->in('obj_id', $obj_ids, false, 'integer');
        if ($usr_id !== null) {
            $condition .= ' AND usr_id = ' . $this->db->quote($usr_id, 'integer');
        }
        if ($user_id !== null) {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $user_id);
            $candidates = 'usr_ident = ' . $this->db->quote($user_id, 'text')
                . ' OR ' . $this->db->like('usr_ident', 'text', $escaped . '@%', false);
            if (preg_match('/^realemail(\d+)$/', $user_id, $matches) === 1) {
                $candidates .= ' OR usr_id = ' . $this->db->quote((int) $matches[1], 'integer');
            }
            $condition .= ' AND (' . $candidates . ')';
        }

        // not queryF(): the pattern of the LIKE holds a %
        $rows = $this->db->query('SELECT obj_id, usr_id, usr_ident, privacy_ident FROM cmix_users WHERE ' . $condition);
        $identities = [];
        while ($row = $this->db->fetchAssoc($rows)) {
            $identities[] = $row;
        }

        return $identities;
    }

    /**
     * The ILIAS user the tool knows by the user id of the launches of the objects. Each candidate identity is
     * checked by computing the user id again, under the privacy setting it was stored with.
     *
     * @param ilObjLTITool[] $objects
     */
    private function findUser(array $objects, string $user_id): int
    {
        foreach ($this->getIdentities($objects, null, $user_id) as $row) {
            $usr_id = (int) $row['usr_id'];
            if (
                ilObjUser::_exists($usr_id)
                && ilLTIAdvantagePlatformLaunchParameterBuilder::getUserId(
                    (int) $row['privacy_ident'],
                    (string) $row['usr_ident'],
                    new ilObjUser($usr_id)
                ) === $user_id
            ) {
                return $usr_id;
            }
        }

        throw new DomainException('The user ' . $user_id . ' has not launched the line item', 404);
    }

    /**
     * The user id the tool knows the user by, from a launch under the current privacy setting of the tool
     * if there is one. Null when the user never launched the objects.
     *
     * @param ilObjLTITool[] $objects
     */
    private function getToolUserId(array $objects, int $usr_id): ?string
    {
        if ($objects === [] || !ilObjUser::_exists($usr_id)) {
            return null;
        }
        $privacy_ident = reset($objects)->getTool()->getPrivacyIdent();
        $identities = $this->getIdentities($objects, $usr_id);
        usort($identities, static fn(array $a, array $b): int => ((int) $b['privacy_ident'] === $privacy_ident) <=> ((int) $a['privacy_ident'] === $privacy_ident));
        foreach ($identities as $row) {
            if ((string) $row['usr_ident'] !== '') {
                return ilLTIAdvantagePlatformLaunchParameterBuilder::getUserId(
                    (int) $row['privacy_ident'],
                    (string) $row['usr_ident'],
                    new ilObjUser($usr_id)
                );
            }
        }

        return null;
    }

    private function writeResult(int $obj_id, int $usr_id, ?float $result): void
    {
        $row = $this->db->fetchAssoc($this->db->queryF(
            'SELECT id FROM ' . self::RESULTS_TABLE . ' WHERE obj_id = %s AND usr_id = %s',
            ['integer', 'integer'],
            [$obj_id, $usr_id]
        ));
        if ($row !== null) {
            $this->db->update(
                self::RESULTS_TABLE,
                ['result' => ['float', $result], 'attended' => ['integer', 1]],
                ['id' => ['integer', (int) $row['id']]]
            );
        } elseif ($result !== null) {
            $this->db->insert(self::RESULTS_TABLE, [
                'id' => ['integer', $this->db->nextId(self::RESULTS_TABLE)],
                'obj_id' => ['integer', $obj_id],
                'usr_id' => ['integer', $usr_id],
                'result' => ['float', $result],
                'attended' => ['integer', 1],
            ]);
        }
    }
}
