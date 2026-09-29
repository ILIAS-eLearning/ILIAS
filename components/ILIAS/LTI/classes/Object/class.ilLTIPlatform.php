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

use ceLTIc\LTI\DataConnector\DataConnector;
use ceLTIc\LTI\Enum\LtiVersion;
use ceLTIc\LTI\Platform;

/**
 * A platform that launches ILIAS, stored in lti2_consumer. The library owns the columns of the LTI
 * protocol, this class adds what ILIAS needs on top of them.
 *
 * ILIAS has no data connector of the library, so the platform holds no more than the code building it
 * puts in: checking the OAuth1 signature of an outcome, for instance, only needs key, secret and record.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIPlatform extends Platform
{
    public function __construct(?DataConnector $data_connector = null)
    {
        parent::__construct($data_connector ?? DataConnector::getDataConnector());
    }

    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    /**
     * Stores the LTI Advantage registration of a platform of the administration, once for the whole platform
     * (ref_id 0). The URLs go to the settings of celtic/lti. An empty registration still marks the platform as
     * an LTI Advantage one, until it registers through Dynamic Registration.
     *
     * @param int   $platform_id  the platform in lti_ext_consumer
     * @param array $registration platform_id, client_id, deployment_id, keyset_url, token_url and authentication_url
     * @param array $settings     further settings of celtic/lti to store
     */
    public static function saveRegistration(
        int $platform_id,
        string $name,
        bool $enabled,
        array $registration,
        array $settings = []
    ): void {
        global $DIC;

        $db = $DIC->database();
        $now = date('Y-m-d H:i:s');
        $fields = [
            'name' => ['text', ilStr::subStr($name, 0, 50)],
            'lti_version' => ['text', LtiVersion::V1P3->value],
            'signature_method' => ['text', 'RS256'],
            'enabled' => ['integer', (int) $enabled],
            'platform_id' => ['text', $registration['platform_id']],
            'client_id' => ['text', $registration['client_id']],
            'deployment_id' => ['text', $registration['deployment_id']],
            'settings' => ['text', json_encode([
                '_jku' => $registration['keyset_url'],
                '_oauth2_access_token_url' => $registration['token_url'],
                '_authentication_request_url' => $registration['authentication_url'],
            ] + $settings, JSON_UNESCAPED_SLASHES)],
            // the key celtic/lti fetched from the key set is a cache: it is fetched again from the saved URL
            'public_key' => ['clob', null],
            'updated' => ['timestamp', $now],
        ];

        $row = $db->fetchAssoc($db->query(
            'SELECT consumer_pk FROM lti2_consumer WHERE ref_id = 0 AND ext_consumer_id = ' . $db->quote($platform_id, 'integer')
        ));
        if ($row !== null) {
            $db->update('lti2_consumer', $fields, ['consumer_pk' => ['integer', (int) $row['consumer_pk']]]);
            return;
        }
        $db->insert('lti2_consumer', $fields + [
            'consumer_pk' => ['integer', $db->nextId('lti2_consumer')],
            'secret' => ['text', ''],
            'protected' => ['integer', 0],
            'created' => ['timestamp', $now],
            'ext_consumer_id' => ['integer', $platform_id],
            'ref_id' => ['integer', 0],
        ]);
    }

    /**
     * The platform of the administration an LTI Advantage registration belongs to, 0 when none has it. A launch
     * names the platform by these three ids, so no two platforms may share them.
     */
    public static function lookupIdByRegistration(string $issuer, string $client_id, string $deployment_id): int
    {
        global $DIC;

        $db = $DIC->database();
        $db->setLimit(1);
        $row = $db->fetchAssoc($db->queryF(
            'SELECT ext_consumer_id FROM lti2_consumer WHERE platform_id = %s AND client_id = %s AND deployment_id = %s'
            . ' AND lti_version = %s ORDER BY ref_id, consumer_pk',
            ['text', 'text', 'text', 'text'],
            [$issuer, $client_id, $deployment_id, LtiVersion::V1P3->value]
        ));

        return (int) ($row['ext_consumer_id'] ?? 0);
    }

    /**
     * The platform of the administration as stored in lti_ext_consumer, empty when it does not exist.
     */
    public static function lookupAdministrationRow(int $platform_id): array
    {
        global $DIC;

        $db = $DIC->database();

        return $db->fetchAssoc($db->query(
            'SELECT * FROM lti_ext_consumer WHERE id = ' . $db->quote($platform_id, 'integer')
        )) ?? [];
    }

    /**
     * The settings of celtic/lti the LTI Advantage registration of a platform of the administration has.
     */
    public static function lookupRegistrationSettings(int $platform_id): array
    {
        global $DIC;

        $db = $DIC->database();
        $row = $db->fetchAssoc($db->query(
            'SELECT settings FROM lti2_consumer WHERE ref_id = 0 AND ext_consumer_id = ' . $db->quote($platform_id, 'integer')
        ));
        $settings = json_decode((string) ($row['settings'] ?? ''), true);

        return is_array($settings) ? $settings : [];
    }
}
