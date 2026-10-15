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

namespace ILIAS\Environment\Configuration\Installation;

use ILIAS\Environment\Configuration\Ini\IniFileReadRepository;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ClientIniFile extends IniFileReadRepository implements ClientIni
{
    // [server]

    public function getStartUrl(): string
    {
        return $this->getOrEmpty('server', 'start');
    }

    // [client]

    public function getClientName(): string
    {
        return $this->getOrEmpty('client', 'name');
    }

    public function getClientDescription(): string
    {
        return $this->getOrEmpty('client', 'description');
    }

    public function isClientAccessEnabled(): bool
    {
        return $this->getOrEmpty('client', 'access') === '1';
    }

    // [db]

    public function getDatabaseType(): string
    {
        return $this->getOrEmpty('db', 'type');
    }

    public function getDatabaseHost(): string
    {
        return $this->getOrEmpty('db', 'host');
    }

    public function getDatabaseUser(): string
    {
        return $this->getOrEmpty('db', 'user');
    }

    public function getDatabasePassword(): string
    {
        return $this->getOrEmpty('db', 'pass');
    }

    public function getDatabaseName(): string
    {
        return $this->getOrEmpty('db', 'name');
    }

    // [language]

    public function getDefaultLanguage(): string
    {
        return $this->getOrEmpty('language', 'default');
    }

    // [layout]

    public function getSkin(): string
    {
        return $this->getOrEmpty('layout', 'skin');
    }

    public function getStyle(): string
    {
        return $this->getOrEmpty('layout', 'style');
    }

    // [session]

    public function getSessionExpiry(): int
    {
        return (int) $this->getOrEmpty('session', 'expire');
    }

    // [system]

    public function getRootFolderId(): int
    {
        return (int) $this->getOrEmpty('system', 'ROOT_FOLDER_ID');
    }

    public function getSystemFolderId(): int
    {
        return (int) $this->getOrEmpty('system', 'SYSTEM_FOLDER_ID');
    }

    public function getRoleFolderId(): int
    {
        return (int) $this->getOrEmpty('system', 'ROLE_FOLDER_ID');
    }

    public function getMailSettingsId(): int
    {
        return (int) $this->getOrEmpty('system', 'MAIL_SETTINGS_ID');
    }

    // [cache]

    public function isGlobalCacheEnabled(): bool
    {
        return $this->getOrEmpty('cache', 'activate_global_cache') === '1';
    }

    public function getGlobalCacheServiceType(): int
    {
        return (int) $this->getOrEmpty('cache', 'global_cache_service_type');
    }

    // [log]

    public function getLogErrorRecipient(): string
    {
        return $this->getOrEmpty('log', 'error_recipient');
    }
}
