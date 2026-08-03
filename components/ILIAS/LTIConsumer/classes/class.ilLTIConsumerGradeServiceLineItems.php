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
    public function __construct(
        ilLTIConsumerServiceBase $service,
        private readonly ilLTIConsumerLineItemRepository $lineItemRepository
    )
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
        } catch (ilLTIConsumerHttpException $e) {
            $response->setCode($e->getCode());
            $response->setReason($e->getMessage());
        } catch (Throwable $e) {
            $response->setCode(500);
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
            throw ilLTIConsumerHttpException::unauthorized();
        }
        $clientId = $token->getClientId();

        global $DIC;
        $tree = $DIC->repositoryTree();
        $nodeData = $tree->getNodeData($contextId);
        if (!$nodeData) {
            throw ilLTIConsumerHttpException::notFound('Context not found');
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

        foreach ($this->lineItemRepository->getForContextAndClient($contextId, $clientId) as $storedLineItem) {
            $lineItems[] = [
                'id' => ilLTIConsumerGradeServiceLineItem::buildLineItemUrl($contextId, -$storedLineItem->id),
                'label' => $storedLineItem->label,
                'scoreMaximum' => $storedLineItem->scoreMaximum,
                'resourceId' => $storedLineItem->resourceId,
                'resourceLinkId' => $storedLineItem->resourceLinkId,
                'tag' => $storedLineItem->tag
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
            throw ilLTIConsumerHttpException::unauthorized();
        }
        $clientId = $token->getClientId();

        $body = json_decode($response->getRequestData());
        if (!is_object($body) || json_last_error() !== JSON_ERROR_NONE) {
            throw ilLTIConsumerHttpException::badRequest('Invalid request body');
        }

        if (!$this->contextContainsClientTool($contextId, $clientId)) {
            throw ilLTIConsumerHttpException::forbidden('Context not available for client');
        }

        $label = isset($body->label) ? (string) $body->label : null;
        $scoreMax = isset($body->scoreMaximum) && is_numeric($body->scoreMaximum) && (float) $body->scoreMaximum > 0
            ? (float) $body->scoreMaximum : 1;
        $resourceId = isset($body->resourceId) ? (string) $body->resourceId : '';
        $resourceLinkId = isset($body->resourceLinkId) ? (string) $body->resourceLinkId : '';
        $tag = isset($body->tag) ? (string) $body->tag : '';

        $lineItem = $this->lineItemRepository->create(
            $contextId,
            $clientId,
            $label,
            $scoreMax,
            $resourceId,
            $resourceLinkId,
            $tag
        );
        $lineItemData = [
            'id' => ilLTIConsumerGradeServiceLineItem::buildLineItemUrl($contextId, -$lineItem->id),
            'label' => $lineItem->label,
            'scoreMaximum' => $lineItem->scoreMaximum,
            'resourceId' => $lineItem->resourceId,
            'resourceLinkId' => $lineItem->resourceLinkId,
            'tag' => $lineItem->tag
        ];

        $response->setCode(201);
        $response->setContentType('application/vnd.ims.lis.v2.lineitem+json');
        $response->setBody(json_encode($lineItemData, JSON_UNESCAPED_SLASHES));
    }

    /**
     * @return array{tag: string, resourceId: string, resourceLinkId: string, limit: int}
     */
    protected function getFilters(): array
    {
        global $DIC;

        $query = $DIC->http()->wrapper()->query();
        $string = $DIC->refinery()->kindlyTo()->string();
        $limit = $query->has('limit') ? $query->retrieve('limit', $string) : '';

        return [
            'tag' => $query->has('tag') ? $query->retrieve('tag', $string) : '',
            'resourceId' => $query->has('resource_id') ? $query->retrieve('resource_id', $string) : '',
            'resourceLinkId' => $query->has('resource_link_id') ? $query->retrieve('resource_link_id', $string) : '',
            'limit' => is_numeric($limit) ? max(0, (int) $limit) : 0
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

}
