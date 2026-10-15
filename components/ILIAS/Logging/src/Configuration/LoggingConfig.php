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

namespace ILIAS\Logging\Configuration;

/**
 * Typed read-access to the logging configuration. Combines values from
 * `ilias.ini.php`, `client.ini.php` and the persistent `logging` settings
 * stored in the database.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface LoggingConfig
{
    public function isLoggingEnabled(): bool;

    public function getLogDirectory(): string;

    public function getLogFile(): string;

    public function getLevel(): int;

    public function getLevelByComponent(string $component_id): int;

    public function getCacheLevel(): int;

    public function isCacheEnabled(): bool;

    public function isMemoryUsageEnabled(): bool;

    public function isBrowserLogEnabled(): bool;

    public function isBrowserLogEnabledForUser(string $login): bool;

    /**
     * @return string[]
     */
    public function getBrowserLogUsers(): array;

    public function getErrorDirectory(): string;

    public function getErrorRecipient(): string;
}
