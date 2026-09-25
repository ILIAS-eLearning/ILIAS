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

use ceLTIc\LTI\AccessToken;
use ceLTIc\LTI\Context;
use ceLTIc\LTI\DataConnector\DataConnector;
use ceLTIc\LTI\Enum\IdScope;
use ceLTIc\LTI\Enum\LtiVersion;
use ceLTIc\LTI\Platform;
use ceLTIc\LTI\PlatformNonce;
use ceLTIc\LTI\ResourceLink;
use ceLTIc\LTI\UserResult;
use ceLTIc\LTI\Util;

/**
 * Stores what celtic/lti keeps of the platforms that launch ILIAS in the lti2_* tables of ILIAS,
 * through ilDB. The library decides by the data whether a launch is LTI 1.1 or LTI Advantage.
 *
 * The tables are the ones of the library with three differences: the ids come from sequences, the id
 * of lti2_user_result is user_pk, and a resource link always keeps its platform (consumer_pk), which
 * the learning progress sent back to the platform relies on.
 *
 * Only what a launch and the outcome services use is implemented. The rest of the methods of the
 * library keep their defaults, which store nothing.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIDataConnector extends DataConnector
{
    private const string DATE_FORMAT = 'Y-m-d H:i:s';

    private readonly ilDBInterface $database;

    public function __construct()
    {
        global $DIC;

        parent::__construct(null, '');
        $this->database = $DIC->database();
    }

    /**
     * By record id, by issuer, client id and deployment id for an LTI Advantage launch, or by consumer key
     * for an LTI 1.1 launch.
     */
    public function loadPlatform(Platform $platform): bool
    {
        $row = match (true) {
            $platform->getRecordId() !== null => $this->fetch('lti2_consumer', 'consumer_pk', $platform->getRecordId(), 'integer'),
            $platform->platformId !== null && $platform->platformId !== '' => $this->fetchAdvantagePlatform($platform),
            $platform->getKey() !== null && $platform->getKey() !== '' => $this->fetch('lti2_consumer', 'consumer_key', $platform->getKey(), 'text'),
            default => null,
        };
        if ($row === null) {
            return false;
        }

        $platform->setRecordId((int) $row['consumer_pk']);
        $platform->name = (string) $row['name'];
        $platform->setKey($row['consumer_key']);
        $platform->secret = (string) $row['secret'];
        $platform->platformId = $row['platform_id'];
        $platform->clientId = $row['client_id'];
        $platform->deploymentId = $row['deployment_id'];
        $platform->rsaKey = $row['public_key'];
        $platform->ltiVersion = LtiVersion::tryFrom((string) $row['lti_version']);
        $platform->signatureMethod = (string) ($row['signature_method'] ?: 'HMAC-SHA1');
        $platform->consumerName = $row['consumer_name'];
        $platform->consumerVersion = $row['consumer_version'];
        $platform->consumerGuid = $row['consumer_guid'];
        $platform->setSettings($this->decodeSettings($row['settings']));
        $platform->protected = (int) $row['protected'] === 1;
        $platform->enabled = (int) $row['enabled'] === 1;
        $platform->enableFrom = $this->toTimestamp($row['enable_from']);
        $platform->enableUntil = $this->toTimestamp($row['enable_until']);
        $platform->lastAccess = $this->toTimestamp($row['last_access']);
        $platform->created = $this->toTimestamp($row['created']);
        $platform->updated = $this->toTimestamp($row['updated']);
        $this->fixPlatformSettings($platform, false);

        return true;
    }

    /**
     * A launch only updates what the platform tells about itself, and the public key celtic/lti fetched
     * from the key set of an LTI Advantage platform, which it keeps with its kid. The platforms are
     * defined in the administration and when an object is released, never here.
     */
    public function savePlatform(Platform $platform): bool
    {
        if ($platform->getRecordId() === null) {
            return false;
        }

        $platform->updated = time();
        $this->fixPlatformSettings($platform, true);
        $settings = $platform->getSettings();
        $this->fixPlatformSettings($platform, false);
        $this->database->update('lti2_consumer', [
            'public_key' => ['clob', $platform->rsaKey],
            'settings' => ['clob', json_encode($settings, JSON_UNESCAPED_SLASHES)],
            'consumer_name' => ['text', $platform->consumerName],
            'consumer_version' => ['text', $platform->consumerVersion],
            'consumer_guid' => ['text', $platform->consumerGuid],
            'last_access' => ['timestamp', $platform->lastAccess === null ? null : date(self::DATE_FORMAT, $platform->lastAccess)],
            'updated' => ['timestamp', date(self::DATE_FORMAT, $platform->updated)],
        ], ['consumer_pk' => ['integer', $platform->getRecordId()]]);

        return true;
    }

    public function loadContext(Context $context): bool
    {
        $row = $context->getRecordId() !== null
            ? $this->fetch('lti2_context', 'context_pk', $context->getRecordId(), 'integer')
            : $this->database->fetchAssoc($this->database->queryF(
                'SELECT * FROM lti2_context WHERE consumer_pk = %s AND lti_context_id = %s',
                ['integer', 'text'],
                [$context->getPlatform()->getRecordId(), $context->ltiContextId]
            ));
        if ($row === null) {
            return false;
        }

        $context->setRecordId((int) $row['context_pk']);
        $context->setPlatformId((int) $row['consumer_pk']);
        $context->title = $row['title'];
        $context->ltiContextId = $row['lti_context_id'];
        $context->type = $row['type'];
        $context->setSettings($this->decodeSettings($row['settings']));
        $context->created = $this->toTimestamp($row['created']);
        $context->updated = $this->toTimestamp($row['updated']);

        return true;
    }

    public function saveContext(Context $context): bool
    {
        $context->updated = time();
        $fields = [
            'title' => ['text', $context->title],
            'type' => ['text', $context->type],
            'settings' => ['clob', json_encode($context->getSettings())],
            'updated' => ['timestamp', date(self::DATE_FORMAT, $context->updated)],
        ];

        if ($context->getRecordId() === null) {
            $context->created = $context->updated;
            $context->setRecordId($this->database->nextId('lti2_context'));
            $this->database->insert('lti2_context', $fields + [
                'context_pk' => ['integer', $context->getRecordId()],
                'consumer_pk' => ['integer', $context->getPlatform()->getRecordId()],
                'lti_context_id' => ['text', $context->ltiContextId],
                'created' => ['timestamp', date(self::DATE_FORMAT, $context->created)],
            ]);
            return true;
        }

        $this->database->update('lti2_context', $fields, ['context_pk' => ['integer', $context->getRecordId()]]);

        return true;
    }

    /**
     * By record id, or by its id at the platform within its context or directly within its platform.
     */
    public function loadResourceLink(ResourceLink $resourceLink): bool
    {
        if ($resourceLink->getRecordId() !== null) {
            $row = $this->fetch('lti2_resource_link', 'resource_link_pk', $resourceLink->getRecordId(), 'integer');
        } elseif ($resourceLink->getContext() !== null) {
            $row = $this->database->fetchAssoc($this->database->queryF(
                'SELECT * FROM lti2_resource_link WHERE context_pk = %s AND lti_resource_link_id = %s',
                ['integer', 'text'],
                [$resourceLink->getContext()->getRecordId(), $resourceLink->getId()]
            ));
        } else {
            $row = $this->database->fetchAssoc($this->database->queryF(
                'SELECT * FROM lti2_resource_link WHERE context_pk IS NULL AND consumer_pk = %s AND lti_resource_link_id = %s',
                ['integer', 'text'],
                [$resourceLink->getPlatform()->getRecordId(), $resourceLink->getId()]
            ));
        }
        if ($row === null) {
            return false;
        }

        $resourceLink->setRecordId((int) $row['resource_link_pk']);
        $resourceLink->setContextId($row['context_pk'] === null ? null : (int) $row['context_pk']);
        $resourceLink->setPlatformId($row['consumer_pk'] === null ? null : (int) $row['consumer_pk']);
        $resourceLink->title = $row['title'];
        $resourceLink->ltiResourceLinkId = $row['lti_resource_link_id'];
        $resourceLink->setSettings($this->decodeSettings($row['settings']));
        $resourceLink->primaryResourceLinkId = $row['primary_resource_link_pk'] === null ? null : (int) $row['primary_resource_link_pk'];
        $resourceLink->shareApproved = $row['share_approved'] === null ? null : (int) $row['share_approved'] === 1;
        $resourceLink->created = $this->toTimestamp($row['created']);
        $resourceLink->updated = $this->toTimestamp($row['updated']);

        return true;
    }

    public function saveResourceLink(ResourceLink $resourceLink): bool
    {
        $resourceLink->updated = time();
        $fields = [
            'title' => ['text', $resourceLink->title],
            'settings' => ['clob', json_encode($resourceLink->getSettings())],
            'primary_resource_link_pk' => ['integer', $resourceLink->primaryResourceLinkId],
            'share_approved' => ['integer', $resourceLink->shareApproved === null ? null : (int) $resourceLink->shareApproved],
            'updated' => ['timestamp', date(self::DATE_FORMAT, $resourceLink->updated)],
        ];

        if ($resourceLink->getRecordId() === null) {
            $resourceLink->created = $resourceLink->updated;
            $resourceLink->setRecordId($this->database->nextId('lti2_resource_link'));
            $this->database->insert('lti2_resource_link', $fields + [
                'resource_link_pk' => ['integer', $resourceLink->getRecordId()],
                'context_pk' => ['integer', $resourceLink->getContext()?->getRecordId()],
                'consumer_pk' => ['integer', $resourceLink->getPlatform()->getRecordId()],
                'lti_resource_link_id' => ['text', $resourceLink->getId()],
                'created' => ['timestamp', date(self::DATE_FORMAT, $resourceLink->created)],
            ]);
            return true;
        }

        $this->database->update('lti2_resource_link', $fields, ['resource_link_pk' => ['integer', $resourceLink->getRecordId()]]);

        return true;
    }

    public function loadUserResult(UserResult $userresult): bool
    {
        $row = $userresult->getRecordId() !== null
            ? $this->fetch('lti2_user_result', 'user_pk', $userresult->getRecordId(), 'integer')
            : $this->database->fetchAssoc($this->database->queryF(
                'SELECT * FROM lti2_user_result WHERE resource_link_pk = %s AND lti_user_id = %s',
                ['integer', 'text'],
                [$userresult->getResourceLink()->getRecordId(), $userresult->getId(IdScope::IdOnly)]
            ));
        if ($row === null) {
            return false;
        }

        $userresult->setRecordId((int) $row['user_pk']);
        $userresult->setResourceLinkId((int) $row['resource_link_pk']);
        $userresult->ltiUserId = $row['lti_user_id'];
        $userresult->ltiResultSourcedId = $row['lti_result_sourcedid'];
        $userresult->created = $this->toTimestamp($row['created']);
        $userresult->updated = $this->toTimestamp($row['updated']);

        return true;
    }

    public function saveUserResult(UserResult $userresult): bool
    {
        $userresult->updated = time();
        $fields = [
            'lti_result_sourcedid' => ['text', (string) $userresult->ltiResultSourcedId],
            'updated' => ['timestamp', date(self::DATE_FORMAT, $userresult->updated)],
        ];

        if ($userresult->getRecordId() === null) {
            $userresult->created = $userresult->updated;
            $userresult->setRecordId($this->database->nextId('lti2_user_result'));
            $this->database->insert('lti2_user_result', $fields + [
                'user_pk' => ['integer', $userresult->getRecordId()],
                'resource_link_pk' => ['integer', $userresult->getResourceLink()->getRecordId()],
                'lti_user_id' => ['text', $userresult->getId(IdScope::IdOnly)],
                'created' => ['timestamp', date(self::DATE_FORMAT, $userresult->created)],
            ]);
            return true;
        }

        $this->database->update('lti2_user_result', $fields, ['user_pk' => ['integer', $userresult->getRecordId()]]);

        return true;
    }

    /**
     * A nonce is found while it has not expired, which is what protects against replayed requests.
     */
    public function loadPlatformNonce(PlatformNonce $nonce): bool
    {
        $this->database->manipulateF(
            'DELETE FROM lti2_nonce WHERE expires <= %s',
            ['timestamp'],
            [date(self::DATE_FORMAT)]
        );

        return $this->database->fetchAssoc($this->database->queryF(
            'SELECT value FROM lti2_nonce WHERE consumer_pk = %s AND value = %s',
            ['integer', 'text'],
            [$nonce->getPlatform()->getRecordId(), $nonce->getValue()]
        )) !== null;
    }

    public function savePlatformNonce(PlatformNonce $nonce): bool
    {
        $this->database->insert('lti2_nonce', [
            'consumer_pk' => ['integer', $nonce->getPlatform()->getRecordId()],
            'value' => ['text', $nonce->getValue()],
            'expires' => ['timestamp', date(self::DATE_FORMAT, $nonce->expires)],
        ]);

        return true;
    }

    public function deletePlatformNonce(PlatformNonce $nonce): bool
    {
        $this->database->manipulateF(
            'DELETE FROM lti2_nonce WHERE consumer_pk = %s AND value = %s',
            ['integer', 'text'],
            [$nonce->getPlatform()->getRecordId(), $nonce->getValue()]
        );

        return true;
    }

    /**
     * The access token ILIAS got from an LTI Advantage platform for its services, one per platform.
     */
    public function loadAccessToken(AccessToken $accessToken): bool
    {
        $row = $this->fetch('lti2_access_token', 'consumer_pk', (int) $accessToken->getPlatform()->getRecordId(), 'integer');
        if ($row === null) {
            return false;
        }

        $scopes = Util::jsonDecode((string) $row['scopes'], true);
        $accessToken->scopes = is_array($scopes) ? $scopes : [];
        $accessToken->token = (string) $row['token'];
        $accessToken->expires = $this->toTimestamp($row['expires']);
        $accessToken->created = $this->toTimestamp($row['created']);
        $accessToken->updated = $this->toTimestamp($row['updated']);

        return true;
    }

    public function saveAccessToken(AccessToken $accessToken): bool
    {
        $consumer_pk = (int) $accessToken->getPlatform()->getRecordId();
        $now = date(self::DATE_FORMAT);
        $this->database->replace('lti2_access_token', ['consumer_pk' => ['integer', $consumer_pk]], [
            'scopes' => ['clob', json_encode($accessToken->scopes, JSON_UNESCAPED_SLASHES)],
            'token' => ['text', $accessToken->token],
            'expires' => ['timestamp', date(self::DATE_FORMAT, (int) $accessToken->expires)],
            'created' => ['timestamp', $accessToken->created === null ? $now : date(self::DATE_FORMAT, $accessToken->created)],
            'updated' => ['timestamp', $now],
        ]);

        return true;
    }

    /**
     * The registration of the platform (ref_id 0) comes first. Earlier releases registered an LTI
     * Advantage platform once per released object (ref_id > 0): those rows are the fallback.
     * The library leaves out client id and deployment id when the request does not give them.
     *
     * @return array|null
     */
    private function fetchAdvantagePlatform(Platform $platform): ?array
    {
        $where = ['platform_id = ' . $this->database->quote($platform->platformId, 'text')];
        if ($platform->clientId !== null && $platform->clientId !== '') {
            $where[] = 'client_id = ' . $this->database->quote($platform->clientId, 'text');
            if ($platform->deploymentId !== null && $platform->deploymentId !== '') {
                $where[] = 'deployment_id = ' . $this->database->quote($platform->deploymentId, 'text');
            }
        }
        $this->database->setLimit(1);

        return $this->database->fetchAssoc($this->database->query(
            'SELECT * FROM lti2_consumer WHERE ' . implode(' AND ', $where)
            . ' AND lti_version = ' . $this->database->quote(LtiVersion::V1P3->value, 'text')
            . ' ORDER BY ref_id, consumer_pk'
        ));
    }

    /**
     * @return array|null
     */
    private function fetch(string $table, string $column, int|string $value, string $type): ?array
    {
        return $this->database->fetchAssoc($this->database->queryF(
            'SELECT * FROM ' . $table . ' WHERE ' . $column . ' = %s',
            [$type],
            [$value]
        ));
    }

    /**
     * Earlier releases stored some settings serialized instead of as JSON.
     *
     * @return array
     */
    private function decodeSettings(?string $settings): array
    {
        if ($settings === null || $settings === '') {
            return [];
        }
        $decoded = Util::jsonDecode($settings, true);
        if (!is_array($decoded)) {
            $decoded = @unserialize($settings, ['allowed_classes' => false]);
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function toTimestamp(?string $date): ?int
    {
        return $date === null || $date === '' ? null : (strtotime($date) ?: null);
    }
}
