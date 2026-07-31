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
    public function __construct(ilLTIConsumerServiceBase $service)
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
        } catch (Exception $e) {
            $code = $e->getCode();
            $response->setCode($code >= 400 && $code < 600 ? $code : 500);
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
            throw new Exception('invalid request', 401);
        }

        $object = new ilObjLTIConsumer($itemId, false);
        $provider = $object->getProvider();
        if (!$provider || (!$provider->isGradeSynchronization() && !$provider->getHasOutcome())) {
            throw new Exception('grade synchronization not enabled', 403);
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
            throw new Exception('invalid request', 401);
        }

        $row = $this->findStoredLineItem($itemId, $contextId, $this->getClientIdFromToken($token));
        if (!$row) {
            throw new Exception('LineItem not found', 404);
        }

        $lineItem = [
            'id' => self::buildLineItemUrl($contextId, $itemId),
            'label' => (string) ($row['label'] ?? ''),
            'scoreMaximum' => (float) ($row['score_maximum'] ?? 1),
            'resourceId' => (string) ($row['resource_id'] ?? ''),
            'resourceLinkId' => (string) ($row['resource_link_id'] ?? ''),
            'tag' => (string) ($row['tag'] ?? '')
        ];

        $response->setContentType('application/vnd.ims.lis.v2.lineitem+json');
        $response->setBody(json_encode($lineItem, JSON_UNESCAPED_SLASHES));
    }

    protected function handlePut(ilLTIConsumerServiceResponse $response, int $contextId, int $itemId, bool $isRealObject): void
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

        if ($isRealObject) {
            $object = new ilObjLTIConsumer($itemId, false);
            $provider = $object->getProvider();
            if (!$provider || (!$provider->isGradeSynchronization() && !$provider->getHasOutcome())) {
                throw new Exception('grade synchronization not enabled', 403);
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
            $row = $this->findStoredLineItem($itemId, $contextId, $clientId);
            if (!$row) {
                throw new Exception('LineItem not found', 404);
            }

            global $DIC;
            $ilDB = $DIC->database();
            $storageId = (int) $row['id'];

            $label = isset($body->label) && is_string($body->label) && $body->label !== ''
                ? (string) $body->label : $row['label'];
            $scoreMax = isset($body->scoreMaximum) && is_numeric($body->scoreMaximum) && (float) $body->scoreMaximum > 0
                ? (float) $body->scoreMaximum : (float) ($row['score_maximum'] ?? 1);
            $resourceId = isset($body->resourceId) ? (string) $body->resourceId : (string) ($row['resource_id'] ?? '');
            $resourceLinkId = isset($body->resourceLinkId) ? (string) $body->resourceLinkId : (string) ($row['resource_link_id'] ?? '');
            $tag = isset($body->tag) ? (string) $body->tag : (string) ($row['tag'] ?? '');

            $ilDB->update('lti_consumer_lineitems', [
                'label' => ['text', $label],
                'score_maximum' => ['float', $scoreMax],
                'resource_id' => ['text', $resourceId],
                'resource_link_id' => ['text', $resourceLinkId],
                'tag' => ['text', $tag]
            ], [
                'id' => ['integer', $storageId]
            ]);

            $lineItem = [
                'id' => self::buildLineItemUrl($contextId, $itemId),
                'label' => $label,
                'scoreMaximum' => $scoreMax,
                'resourceId' => $resourceId,
                'resourceLinkId' => $resourceLinkId,
                'tag' => $tag
            ];

            $response->setContentType('application/vnd.ims.lis.v2.lineitem+json');
            $response->setBody(json_encode($lineItem, JSON_UNESCAPED_SLASHES));
        }
    }

    protected function handleDelete(ilLTIConsumerServiceResponse $response, int $contextId, int $itemId, bool $isRealObject): void
    {
        $token = $this->checkTool([
            ilLTIConsumerGradeService::SCOPE_GRADESERVICE_LINEITEM
        ]);
        if (!$token) {
            throw new Exception('invalid request', 401);
        }
        $clientId = $this->getClientIdFromToken($token);

        if ($isRealObject) {
            $object = new ilObjLTIConsumer($itemId, false);
            $provider = $object->getProvider();
            if (!$provider || (!$provider->isGradeSynchronization() && !$provider->getHasOutcome())) {
                throw new Exception('grade synchronization not enabled', 403);
            }
            $this->checkProviderMatchesToken($provider, $token);
            $object->setScoreMaximum(1);
            if ($object->getTitle() !== $provider->getTitle()) {
                $object->setTitle($provider->getTitle());
            }
            $object->update();
        } else {
            $row = $this->findStoredLineItem($itemId, $contextId, $clientId);
            if (!$row) {
                throw new Exception('LineItem not found', 404);
            }

            global $DIC;
            $ilDB = $DIC->database();
            $ilDB->update('lti_consumer_lineitems', [
                'enabled' => ['integer', 0]
            ], [
                'id' => ['integer', (int) $row['id']]
            ]);
        }

        $response->setCode(200);
    }

    protected function findStoredLineItem(int $itemId, int $contextId, string $clientId): ?array
    {
        $storedId = -$itemId;
        if ($storedId <= 0) {
            return null;
        }

        global $DIC;
        $ilDB = $DIC->database();
        if (!$ilDB->tableExists('lti_consumer_lineitems')) {
            return null;
        }

        $query = 'SELECT * FROM lti_consumer_lineitems'
            . ' WHERE id = ' . $ilDB->quote($storedId, 'integer')
            . ' AND context_id = ' . $ilDB->quote($contextId, 'integer')
            . ' AND client_id = ' . $ilDB->quote($clientId, 'text')
            . ' AND enabled = ' . $ilDB->quote(1, 'integer');
        $res = $ilDB->query($query);
        return $ilDB->fetchAssoc($res) ?: null;
    }

    protected function checkProviderMatchesToken(ilLTIConsumeProvider $provider, object $token): void
    {
        if ($provider->getClientId() !== $this->getClientIdFromToken($token)) {
            throw new Exception('invalid clientId', 403);
        }
    }

    protected function getClientIdFromToken(object $token): string
    {
        $clientId = $token->sub ?? '';
        if (!is_string($clientId) || $clientId === '') {
            throw new Exception('invalid request', 401);
        }
        return $clientId;
    }

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
