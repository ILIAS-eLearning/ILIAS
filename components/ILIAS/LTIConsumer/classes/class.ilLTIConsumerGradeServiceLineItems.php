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
 * Class ilLTIConsumerGradeServiceLineItems
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/LTIConsumer
 */

class ilLTIConsumerGradeServiceLineItems extends ilLTIConsumerResourceBase
{
    public function __construct(ilLTIConsumerServiceBase $service)
    {
        parent::__construct($service);
        $this->id = 'LineItem.collection';
        $this->template = '/{context_id}/lineitems';
        $this->variables[] = 'LineItems.url';
        $this->formats[] = 'application/vnd.ims.lis.v2.lineitemcontainer+json';
        $this->formats[] = 'application/vnd.ims.lis.v2.lineitem+json';
        $this->methods[] = self::HTTP_GET;
        $this->methods[] = self::HTTP_POST;
    }

    public function execute(ilLTIConsumerServiceResponse $response): void
    {
        $params = $this->parseTemplate();
        $contextId = (int) ($params['context_id'] ?? 0);

        try {
            if ($response->getRequestMethod() === self::HTTP_GET) {
                $this->handleGet($response, $contextId);
            } elseif ($response->getRequestMethod() === self::HTTP_POST) {
                $this->handlePost($response, $contextId);
            } else {
                $response->setCode(405);
            }
        } catch (Exception $e) {
            $code = $e->getCode();
            $response->setCode($code >= 400 && $code < 600 ? $code : 500);
            $response->setReason($e->getMessage());
        }
    }

    protected function handleGet(ilLTIConsumerServiceResponse $response, int $contextId): void
    {
        $token = $this->checkTool([
            ilLTIConsumerGradeService::SCOPE_GRADESERVICE_LINEITEM,
            ilLTIConsumerGradeService::SCOPE_GRADESERVICE_LINEITEM_READ
        ]);
        if (!$token) {
            throw new Exception('invalid request', 401);
        }
        $clientId = $this->getClientIdFromToken($token);

        global $DIC;
        $tree = $DIC->repositoryTree();
        $nodeData = $tree->getNodeData($contextId);
        if (!$nodeData) {
            throw new Exception('Context not found', 404);
        }

        $lineItems = [];
        $filters = $this->getFilters();

        $subtree = $tree->getSubTree($nodeData);
        foreach ($subtree as $node) {
            if ($node['type'] !== 'lti') {
                continue;
            }
            $objId = ilObject::_lookupObjId((int) $node['child']);
            if (!$objId) {
                continue;
            }
            $object = new ilObjLTIConsumer($objId, false);
            $provider = $object->getProvider();
            if (!$provider || (!$provider->isGradeSynchronization() && !$provider->getHasOutcome())) {
                continue;
            }
            if ($provider->getClientId() !== $clientId) {
                continue;
            }
            $lineItems[] = ilLTIConsumerGradeServiceLineItem::buildLineItemData($contextId, $objId, $object);
        }

        $storedRows = $this->loadStoredLineItems($contextId, $clientId);
        foreach ($storedRows as $row) {
            $lineItems[] = [
                'id' => ilLTIConsumerGradeServiceLineItem::buildLineItemUrl($contextId, (int) $row['obj_id_or_pseudo']),
                'label' => (string) ($row['label'] ?? ''),
                'scoreMaximum' => (float) ($row['score_maximum'] ?? 1),
                'resourceId' => (string) ($row['resource_id'] ?? ''),
                'resourceLinkId' => (string) ($row['resource_link_id'] ?? ''),
                'tag' => (string) ($row['tag'] ?? '')
            ];
        }

        $lineItems = $this->filterLineItems($lineItems, $filters);

        $response->setContentType('application/vnd.ims.lis.v2.lineitemcontainer+json');
        $response->setBody(json_encode($lineItems, JSON_UNESCAPED_SLASHES));
    }

    protected function handlePost(ilLTIConsumerServiceResponse $response, int $contextId): void
    {
        $token = $this->checkTool([
            ilLTIConsumerGradeService::SCOPE_GRADESERVICE_LINEITEM
        ]);
        if (!$token) {
            throw new Exception('invalid request', 401);
        }
        $clientId = $this->getClientIdFromToken($token);

        $body = json_decode($response->getRequestData());
        if (!is_object($body) || json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid request body', 400);
        }

        global $DIC;
        $ilDB = $DIC->database();
        if (!$this->contextContainsClientTool($contextId, $clientId)) {
            throw new Exception('Context not available for client', 403);
        }

        $tableExists = $ilDB->tableExists('lti_consumer_lineitems');
        if (!$tableExists) {
            throw new Exception('Line items storage not available', 500);
        }

        $id = $ilDB->nextId('lti_consumer_lineitems');
        $objIdOrPseudo = -$id;

        $label = isset($body->label) ? (string) $body->label : 'Line Item ' . $id;
        $scoreMax = isset($body->scoreMaximum) && is_numeric($body->scoreMaximum) && (float) $body->scoreMaximum > 0
            ? (float) $body->scoreMaximum : 1;
        $resourceId = isset($body->resourceId) ? (string) $body->resourceId : '';
        $resourceLinkId = isset($body->resourceLinkId) ? (string) $body->resourceLinkId : '';
        $tag = isset($body->tag) ? (string) $body->tag : '';

        $ilDB->insert('lti_consumer_lineitems', [
            'id' => ['integer', $id],
            'context_id' => ['integer', $contextId],
            'obj_id' => ['integer', null],
            'client_id' => ['text', $clientId],
            'label' => ['text', $label],
            'score_maximum' => ['float', $scoreMax],
            'resource_id' => ['text', $resourceId],
            'resource_link_id' => ['text', $resourceLinkId],
            'tag' => ['text', $tag],
            'enabled' => ['integer', 1]
        ]);

        $lineItem = [
            'id' => ilLTIConsumerGradeServiceLineItem::buildLineItemUrl($contextId, $objIdOrPseudo),
            'label' => $label,
            'scoreMaximum' => $scoreMax,
            'resourceId' => $resourceId,
            'resourceLinkId' => $resourceLinkId,
            'tag' => $tag
        ];

        $response->setCode(201);
        $response->setContentType('application/vnd.ims.lis.v2.lineitem+json');
        $response->setBody(json_encode($lineItem, JSON_UNESCAPED_SLASHES));
    }

    protected function loadStoredLineItems(int $contextId, string $clientId): array
    {
        global $DIC;
        $ilDB = $DIC->database();
        if (!$ilDB->tableExists('lti_consumer_lineitems')) {
            return [];
        }
        $query = 'SELECT * FROM lti_consumer_lineitems'
            . ' WHERE context_id = ' . $ilDB->quote($contextId, 'integer')
            . ' AND client_id = ' . $ilDB->quote($clientId, 'text')
            . ' AND enabled = ' . $ilDB->quote(1, 'integer');
        $res = $ilDB->query($query);
        $rows = [];
        while ($row = $ilDB->fetchAssoc($res)) {
            $row['obj_id_or_pseudo'] = -((int) $row['id']);
            $rows[] = $row;
        }
        return $rows;
    }

    protected function getFilters(): array
    {
        return [
            'tag' => isset($_GET['tag']) ? (string) $_GET['tag'] : '',
            'resourceId' => isset($_GET['resource_id']) ? (string) $_GET['resource_id'] : '',
            'resourceLinkId' => isset($_GET['resource_link_id']) ? (string) $_GET['resource_link_id'] : '',
            'limit' => isset($_GET['limit']) && is_numeric($_GET['limit']) ? max(0, (int) $_GET['limit']) : 0
        ];
    }

    protected function filterLineItems(array $lineItems, array $filters): array
    {
        $filtered = array_values(array_filter($lineItems, static function (array $item) use ($filters): bool {
            if ($filters['tag'] !== '' && ($item['tag'] ?? '') !== $filters['tag']) {
                return false;
            }
            if ($filters['resourceId'] !== '' && ($item['resourceId'] ?? '') !== $filters['resourceId']) {
                return false;
            }
            if ($filters['resourceLinkId'] !== '' && ($item['resourceLinkId'] ?? '') !== $filters['resourceLinkId']) {
                return false;
            }
            return true;
        }));

        if ($filters['limit'] > 0) {
            return array_slice($filtered, 0, $filters['limit']);
        }

        return $filtered;
    }

    protected function contextContainsClientTool(int $contextId, string $clientId): bool
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $nodeData = $tree->getNodeData($contextId);
        if (!$nodeData) {
            return false;
        }

        foreach ($tree->getSubTree($nodeData) as $node) {
            if ($node['type'] !== 'lti') {
                continue;
            }
            $objId = ilObject::_lookupObjId((int) $node['child']);
            if (!$objId) {
                continue;
            }
            $object = new ilObjLTIConsumer($objId, false);
            $provider = $object->getProvider();
            if ($provider && ($provider->isGradeSynchronization() || $provider->getHasOutcome()) &&
                $provider->getClientId() === $clientId) {
                return true;
            }
        }

        return false;
    }

    protected function getClientIdFromToken(object $token): string
    {
        $clientId = $token->sub ?? '';
        if (!is_string($clientId) || $clientId === '') {
            throw new Exception('invalid request', 401);
        }
        return $clientId;
    }
}
