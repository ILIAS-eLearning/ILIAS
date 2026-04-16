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

namespace ILIAS\Environment\Configuration\Instance;

/**
 * Typed read access to client.ini.php.
 *
 * Sections: server, client, db, auth, language, layout, session, system, cache, log
 *
 * @author     Fabian Schmid <fabian@sr.solutions>
 *
 * @deprecated Try to avoid consuming this directly. Use or introduce more specific Interfaces, e.g.
 * \ILIAS\Environment\Configuration\Instance\Directories and move to other components.
 * * This will help to reactor configuration in the future.
 */
interface ClientIni
{
    // [server]
    public function getStartUrl(): string;

    // [client]
    public function getClientName(): string;

    public function getClientDescription(): string;

    public function isClientAccessEnabled(): bool;

    // [db]
    public function getDatabaseType(): string;

    public function getDatabaseHost(): string;

    public function getDatabaseUser(): string;

    public function getDatabasePassword(): string;

    public function getDatabaseName(): string;

    // [language]
    public function getDefaultLanguage(): string;

    // [layout]
    public function getSkin(): string;

    public function getStyle(): string;

    // [session]
    public function getSessionExpiry(): int;

    // [system]
    public function getRootFolderId(): int;

    public function getSystemFolderId(): int;

    public function getRoleFolderId(): int;

    public function getMailSettingsId(): int;

    // [cache]
    public function isGlobalCacheEnabled(): bool;

    public function getGlobalCacheServiceType(): int;

    // [log]
    public function getLogErrorRecipient(): string;
}
