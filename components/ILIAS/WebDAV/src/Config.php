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

namespace ILIAS\WebDAV;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Config
{
    private const string REF_PREFIX = 'ref_';
    private const string WEBDAV_ENDPOINT = 'webdav.php';
    private const string MOUNT_INSTRUCTIONS_QUERY = 'mount-instructions';
    private ?bool $is_enabed = null;

    public function enableDebugging(): bool
    {
        return false;
    }

    public function isActive(): bool
    {
        return $this->is_enabed ?? $this->is_enabed = (new \ilSetting('webdav'))->get('webdav_enabled', '0') === '1';
    }

    public function getMountInstructionsQuery(): string
    {
        return self::MOUNT_INSTRUCTIONS_QUERY;
    }

    public function prependClientName(): bool
    {
        return true; // currently true for legacy reasons
    }

    public function getEndpoint(): string
    {
        return self::WEBDAV_ENDPOINT;
    }

    public function getClientId(): string
    {
        return defined('CLIENT_ID') ? (string) CLIENT_ID : '';
    }

    public function getRefIdPrefix(): string
    {
        return self::REF_PREFIX;
    }

    public function getInvalidStartCharacters(): array
    {
        return ['.', '_', '~', '$', '/'];
    }

    public function getIncompatibleCharacters(): array
    {
        return [
            '\\',
            '<',
            '>',
            '/',
            ':',
            '*',
            '?',
            '"',
            '|',
            '#'
        ];
    }

    public function getSupportedObjectTypes(): array
    {
        return ['cat', 'fold', 'grp', 'file'];
    }

    public function getDeletedObjectsRetentionPeriod(): int
    {
        return 30;
    }
}
