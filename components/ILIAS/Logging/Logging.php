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

namespace ILIAS;

class Logging implements Component\Component
{
    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        array | \ArrayAccess &$use,
        array | \ArrayAccess &$contribute,
        array | \ArrayAccess &$seek,
        array | \ArrayAccess &$provide,
        array | \ArrayAccess &$pull,
        array | \ArrayAccess &$internal,
    ): void {
        $define[] = \ILIAS\Logging\LoggerFactory::class;

        $implement[\ILIAS\Logging\LoggerFactory::class] = static fn() =>
            $internal[\ilLoggerFactory::class];


        $contribute[\ILIAS\Setup\Agent::class] = static fn() =>
            new \ilLoggingSetupAgent(
                $pull[\ILIAS\Refinery\Factory::class]
            );

        $contribute[\ILIAS\Cron\CronJob::class] = static fn() =>
            new \ilLoggerCronCleanErrorFiles(
                self::class,
                $use[\ILIAS\Language\Language::class],
                $use[\ILIAS\Logging\LoggerFactory::class]
            );

        $internal[\ilLoggerFactory::class] = static fn() =>
            \ilLoggerFactory::getInstance(
                $internal[\ilLoggingSettings::class]
            );

        $internal[\ilLoggingSettings::class] = static fn() =>
            new class () implements \ilLoggingSettings {
                public function isEnabled(): bool
                {
                    return false;
                }
                public function getLogDir(): string
                {
                }
                public function getLogFile(): string
                {
                }
                public function getLevel(): int
                {
                }
                public function getLevelByComponent(string $a_component_id): int
                {
                }
                public function getCacheLevel(): int
                {
                }
                public function isCacheEnabled(): bool
                {
                }
                public function isMemoryUsageEnabled(): bool
                {
                }
                public function isBrowserLogEnabled(): bool
                {
                }
                public function isBrowserLogEnabledForUser(string $a_login): bool
                {
                }
                public function getBrowserLogUsers(): array
                {
                }
            };
    }
}
