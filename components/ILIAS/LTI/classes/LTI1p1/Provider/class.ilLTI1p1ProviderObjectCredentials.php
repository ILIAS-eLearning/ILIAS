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

use ceLTIc\LTI\Util;
use ILIAS\UI\Factory;

/**
 * The LTI 1.1 consumer key and secret an object is released to a platform with: one row of lti2_consumer
 * per object and platform, with the object in ref_id. The key is what tells ILIAS which object a launch
 * is for.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTI1p1ProviderObjectCredentials
{
    private const string TABLE_NAME = 'lti2_consumer';
    private const string VERSION = 'LTI-1p0';

    private int $record_id = 0;
    private string $key = '';
    private string $secret = '';
    private bool $enabled = false;

    public function __construct(private readonly int $ref_id, private readonly int $platform_id)
    {
        global $DIC;

        $db = $DIC->database();
        $row = $db->fetchAssoc($db->queryF(
            'SELECT consumer_pk, consumer_key, secret, enabled FROM ' . self::TABLE_NAME
            . ' WHERE ref_id = %s AND ext_consumer_id = %s AND (lti_version IS NULL OR lti_version = %s)',
            ['integer', 'integer', 'text'],
            [$ref_id, $platform_id, self::VERSION]
        ));
        if ($row === null) {
            // new credentials are shown before they are saved, so the form has them to post back
            $this->key = Util::getRandomString(10);
            $this->secret = Util::getRandomString(12);
            return;
        }

        $this->record_id = (int) $row['consumer_pk'];
        $this->key = (string) $row['consumer_key'];
        $this->secret = (string) $row['secret'];
        $this->enabled = (bool) $row['enabled'];
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return array what the platform needs to launch the object, read only
     */
    public function getInputs(ilLanguage $lng, Factory $ui_factory): array
    {
        $field = $ui_factory->input()->field();

        return [
            'launch_url' => $field->text($lng->txt('lti_launch_url'))->withValue(ILIAS_HTTP_PATH . '/lti.php')->withDisabled(true),
            'key' => $field->hidden()->withValue($this->key),
            'shown_key' => $field->text($lng->txt('lti_con_prov_key'))->withValue($this->key)->withDisabled(true),
            'secret' => $field->hidden()->withValue($this->secret),
            'shown_secret' => $field->text($lng->txt('lti_con_prov_secret'))->withValue($this->secret)->withDisabled(true),
        ];
    }

    /**
     * Stores the credentials the form posted back, the ones it showed.
     */
    public function save(array $data, bool $enabled): void
    {
        global $DIC;

        $db = $DIC->database();
        $now = date('Y-m-d H:i:s');
        $fields = [
            'enabled' => ['integer', (int) $enabled],
            'updated' => ['timestamp', $now],
        ];

        if ($this->record_id > 0) {
            $db->update(self::TABLE_NAME, $fields, ['consumer_pk' => ['integer', $this->record_id]]);
            $this->enabled = $enabled;
            return;
        }
        if (!$enabled) {
            return;
        }

        $this->record_id = $db->nextId(self::TABLE_NAME);
        $this->key = (string) $data['key'];
        $this->secret = (string) $data['secret'];
        $this->enabled = true;
        $db->insert(self::TABLE_NAME, $fields + [
            'consumer_pk' => ['integer', $this->record_id],
            'name' => ['text', ilStr::subStr(ilObject::_lookupTitle(ilObject::_lookupObjId($this->ref_id)), 0, 50)],
            'consumer_key' => ['text', $this->key],
            'secret' => ['text', $this->secret],
            'lti_version' => ['text', self::VERSION],
            'signature_method' => ['text', 'HMAC-SHA1'],
            'protected' => ['integer', 0],
            'settings' => ['text', '{}'],
            'created' => ['timestamp', $now],
            'ext_consumer_id' => ['integer', $this->platform_id],
            'ref_id' => ['integer', $this->ref_id],
        ]);
    }
}
