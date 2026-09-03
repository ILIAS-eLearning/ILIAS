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
 * Fallback {@see LoggingConfig} for environments without a bootstrapped
 * `logging.services` (e.g. unit tests). Disables logging entirely so callers
 * keep working without writing files or touching the database.
 */
final class NullLoggingConfig implements LoggingConfig
{
    public function isLoggingEnabled(): bool
    {
        return false;
    }

    public function getLogDirectory(): string
    {
        return '';
    }

    public function getLogFile(): string
    {
        return '';
    }

    public function getLevel(): int
    {
        return \ilLogLevel::OFF;
    }

    public function getLevelByComponent(string $component_id): int
    {
        return \ilLogLevel::OFF;
    }

    public function getCacheLevel(): int
    {
        return \ilLogLevel::OFF;
    }

    public function isCacheEnabled(): bool
    {
        return false;
    }

    public function isMemoryUsageEnabled(): bool
    {
        return false;
    }

    public function isBrowserLogEnabled(): bool
    {
        return false;
    }

    public function isBrowserLogEnabledForUser(string $login): bool
    {
        return false;
    }

    public function getBrowserLogUsers(): array
    {
        return [];
    }

    public function getErrorDirectory(): string
    {
        return '';
    }

    public function getErrorRecipient(): string
    {
        return '';
    }
}
