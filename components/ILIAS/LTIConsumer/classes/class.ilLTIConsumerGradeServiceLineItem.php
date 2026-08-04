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
 * Class ilLTIConsumerGradeServiceLineItem
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/LTIConsumer
 */

class ilLTIConsumerGradeServiceLineItem extends ilLTIConsumerResourceBase
{
    public function __construct(
        ilLTIConsumerServiceBase $service,
        private readonly ilLTIConsumerLineItemRepository $lineItemRepository
    )
    {
        parent::__construct($service);
        $this->id = 'LineItem.item';
        $this->template = '/{context_id}/lineitems/{item_id}/lineitem';
        $this->variables[] = 'LineItem.url';
        $this->formats[] = 'application/vnd.ims.lis.v2.lineitem+json';
        $this->methods[] = self::HTTP_GET;
        $this->methods[] = self::HTTP_PUT;
        $this->methods[] = self::HTTP_DELETE;
    }

    public function execute(ilLTIConsumerServiceResponse $response): void
    {
        $params = $this->parseTemplate();
        $contextId = (int) ($params['context_id'] ?? 0);
        $itemId = (int) ($params['item_id'] ?? 0);

        try {
            $isRealObject = ilObject::_lookupType($itemId) === 'lti';

            if ($response->getRequestMethod() === self::HTTP_GET) {
                if ($isRealObject) {
                    $this->handleGetReal($response, $contextId, $itemId);
                } else {
                    $this->handleGetStored($response, $contextId, $itemId);
                }
            } elseif ($response->getRequestMethod() === self::HTTP_PUT) {
                $this->handlePut($response, $contextId, $itemId, $isRealObject);
            } elseif ($response->getRequestMethod() === self::HTTP_DELETE) {
                $this->handleDelete($response, $contextId, $itemId, $isRealObject);
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

    protected function handleGetReal(ilLTIConsumerServiceResponse $response, int $contextId, int $itemId): void
    {
        $token = $this->checkTool([
            ilLTIConsumerGradeService::SCOPE_GRADESERVICE_LINEITEM,
            ilLTIConsumerGradeService::SCOPE_GRADESERVICE_LINEITEM_READ
        ]);
        if (!$token) {
            throw ilLTIConsumerHttpException::unauthorized();
        }

        $object = new ilObjLTIConsumer($itemId, false);
        $provider = $object->getProvider();
        if (!$provider || (!$provider->isGradeSynchronization() && !$provider->getHasOutcome())) {
            throw ilLTIConsumerHttpException::forbidden('grade synchronization not enabled');
        }
        $this->checkProviderMatchesToken($provider, $token);

        $lineItem = self::buildLineItemData($contextId, $itemId, $object);
        $response->setContentType('application/vnd.ims.lis.v2.lineitem+json');
        $response->setBody(json_encode($lineItem, JSON_UNESCAPED_SLASHES));
    }

    protected function handleGetStored(ilLTIConsumerServiceResponse $response, int $contextId, int $itemId): void
    {
        $token = $this->checkTool([
            ilLTIConsumerGradeService::SCOPE_GRADESERVICE_LINEITEM,
            ilLTIConsumerGradeService::SCOPE_GRADESERVICE_LINEITEM_READ
        ]);
        if (!$token) {
            throw ilLTIConsumerHttpException::unauthorized();
        }

        $lineItem = $this->findStoredLineItem($itemId, $contextId, $token->getClientId());
        if ($lineItem === null) {
            throw ilLTIConsumerHttpException::notFound('LineItem not found');
        }

        $lineItemData = [
            'id' => self::buildLineItemUrl($contextId, $itemId),
            'label' => $lineItem->label,
            'scoreMaximum' => $lineItem->scoreMaximum,
            'resourceId' => $lineItem->resourceId,
            'resourceLinkId' => $lineItem->resourceLinkId,
            'tag' => $lineItem->tag
        ];

        $response->setContentType('application/vnd.ims.lis.v2.lineitem+json');
        $response->setBody(json_encode($lineItemData, JSON_UNESCAPED_SLASHES));
    }

    protected function handlePut(ilLTIConsumerServiceResponse $response, int $contextId, int $itemId, bool $isRealObject): void
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
        if (property_exists($body, 'resourceLinkId')) {
            $resource_link_id = is_scalar($body->resourceLinkId) ? (string) $body->resourceLinkId : '';
            if (!$this->isClientResourceLink($contextId, $clientId, $resource_link_id)) {
                throw ilLTIConsumerHttpException::notFound('Resource link not found');
            }
        }

        if ($isRealObject) {
            $object = new ilObjLTIConsumer($itemId, false);
            $provider = $object->getProvider();
            if (!$provider || (!$provider->isGradeSynchronization() && !$provider->getHasOutcome())) {
                throw ilLTIConsumerHttpException::forbidden('grade synchronization not enabled');
            }
            $this->checkProviderMatchesToken($provider, $token);

            if (isset($body->label) && is_string($body->label) && $body->label !== '') {
                $object->setTitle($body->label);
            }
            if (isset($body->scoreMaximum) && is_numeric($body->scoreMaximum) && (float) $body->scoreMaximum > 0) {
                $object->setScoreMaximum((float) $body->scoreMaximum);
            }
            $object->update();

            $lineItem = self::buildLineItemData($contextId, $itemId, $object);
            $response->setContentType('application/vnd.ims.lis.v2.lineitem+json');
            $response->setBody(json_encode($lineItem, JSON_UNESCAPED_SLASHES));
        } else {
            $lineItem = $this->findStoredLineItem($itemId, $contextId, $clientId);
            if ($lineItem === null) {
                throw ilLTIConsumerHttpException::notFound('LineItem not found');
            }

            $label = isset($body->label) && is_string($body->label) && $body->label !== ''
                ? (string) $body->label : $lineItem->label;
            $scoreMax = isset($body->scoreMaximum) && is_numeric($body->scoreMaximum) && (float) $body->scoreMaximum > 0
                ? (float) $body->scoreMaximum : $lineItem->scoreMaximum;
            $resourceId = isset($body->resourceId) ? (string) $body->resourceId : $lineItem->resourceId;
            $resourceLinkId = isset($body->resourceLinkId) ? (string) $body->resourceLinkId : $lineItem->resourceLinkId;
            $tag = isset($body->tag) ? (string) $body->tag : $lineItem->tag;
            $lineItem = $lineItem->withValues($label, $scoreMax, $resourceId, $resourceLinkId, $tag);
            $this->lineItemRepository->update($lineItem);

            $lineItemData = [
                'id' => self::buildLineItemUrl($contextId, $itemId),
                'label' => $lineItem->label,
                'scoreMaximum' => $lineItem->scoreMaximum,
                'resourceId' => $lineItem->resourceId,
                'resourceLinkId' => $lineItem->resourceLinkId,
                'tag' => $lineItem->tag
            ];

            $response->setContentType('application/vnd.ims.lis.v2.lineitem+json');
            $response->setBody(json_encode($lineItemData, JSON_UNESCAPED_SLASHES));
        }
    }

    protected function handleDelete(ilLTIConsumerServiceResponse $response, int $contextId, int $itemId, bool $isRealObject): void
    {
        $token = $this->checkTool([
            ilLTIConsumerGradeService::SCOPE_GRADESERVICE_LINEITEM
        ]);
        if (!$token) {
            throw ilLTIConsumerHttpException::unauthorized();
        }
        $clientId = $token->getClientId();

        if ($isRealObject) {
            $response->setCode(405);
            return;
        } else {
            $lineItem = $this->findStoredLineItem($itemId, $contextId, $clientId);
            if ($lineItem === null) {
                throw ilLTIConsumerHttpException::notFound('LineItem not found');
            }
            $this->lineItemRepository->disable($lineItem);
        }

        $response->setCode(200);
    }

    protected function findStoredLineItem(int $itemId, int $contextId, string $clientId): ?ilLTIConsumerLineItem
    {
        $storedId = -$itemId;
        if ($storedId <= 0) {
            return null;
        }

        return $this->lineItemRepository->get($storedId, $contextId, $clientId);
    }

    protected function checkProviderMatchesToken(ilLTIConsumeProvider $provider, ilLTIConsumerAccessToken $token): void
    {
        if ($provider->getClientId() !== $token->getClientId()) {
            throw ilLTIConsumerHttpException::forbidden('invalid clientId');
        }
    }

    protected function isClientResourceLink(int $context_id, string $client_id, string $resource_link_id): bool
    {
        $resource_link_ref_id = (int) $resource_link_id;
        if ($resource_link_ref_id <= 0) {
            return false;
        }

        global $DIC;
        $tree = $DIC->repositoryTree();
        $node_data = $tree->getNodeData($resource_link_ref_id);
        if (!isset($node_data['type']) || $node_data['type'] !== 'lti' ||
            !in_array($context_id, $tree->getPathId($resource_link_ref_id), true)) {
            return false;
        }

        $object_id = ilObject::_lookupObjId($resource_link_ref_id);
        if (!$object_id) {
            return false;
        }

        $provider = (new ilObjLTIConsumer($object_id, false))->getProvider();
        return $provider !== null && $provider->getClientId() === $client_id;
    }

    /**
     * @return array{id: string, label: string, scoreMaximum: float, resourceId: string, resourceLinkId: string}
     */
    public static function buildLineItemData(int $contextId, int $itemId, ilObjLTIConsumer $object): array
    {
        return [
            'id' => self::buildLineItemUrl($contextId, $itemId),
            'label' => $object->getTitle(),
            'scoreMaximum' => $object->getScoreMaximum(),
            'resourceId' => (string) $itemId,
            'resourceLinkId' => self::getResourceLinkId($contextId, $itemId)
        ];
    }

    protected static function getResourceLinkId(int $contextId, int $itemId): string
    {
        global $DIC;

        $refs = ilObject::_getAllReferences($itemId);
        foreach ($refs as $refId) {
            $refId = (int) $refId;
            $path = $DIC->repositoryTree()->getPathId($refId);
            if (in_array($contextId, $path, true)) {
                return (string) $refId;
            }
        }

        $refId = (int) current($refs);
        return $refId > 0 ? (string) $refId : '';
    }

    public static function buildLineItemUrl(int $contextId, int $itemId): string
    {
        return ilObjLTIConsumer::getIliasHttpPath() . "/ltiservices.php/gradeservice/{$contextId}/lineitems/{$itemId}/lineitem";
    }
}
