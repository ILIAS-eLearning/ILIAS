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
        $define[] = Logging\Logger\LoggerFactoryInterface::class;
        $define[] = Logging\Logger\DefaultConfigLoggerFactoryInterface::class;
        $define[] = Logging\Config\ConfigInterface::class;

        $internal[Logging\Config\Basic\ConfigInterface::class] = static fn() =>
            new Logging\Config\Basic\Config(
                new Logging\Config\Basic\IniReader(
                    $use[Environment\Configuration\Installation\IliasIni::class]
                )
            );
        $internal[Logging\Config\ByComponent\ConfigInterface::class] = static fn() =>
            new Logging\Config\ByComponent\Config(
                new Logging\Config\ByComponent\DBRepository(
                    $use[Database\PDO\External::class]
                ),
                $internal[Logging\Config\Basic\ConfigInterface::class]
            );
        $internal[Logging\Logger\LevelFetcher\LevelFetcherFactory::class] = static fn() =>
            new Logging\Logger\LevelFetcher\LevelFetcherFactory();
        $internal[Logging\Logger\LazyInternalFactoryInterface::class] = static fn() =>
            new Logging\Logger\LazyInternalFactory(
                new Logging\Logger\Monolog\Factory(),
                $internal[Logging\Config\Basic\ConfigInterface::class]
            );

        $implement[Logging\Logger\LoggerFactoryInterface::class] = static fn() =>
            new Logging\Logger\LoggerFactory(
                $internal[Logging\Logger\LazyInternalFactoryInterface::class],
                $internal[Logging\Config\ByComponent\ConfigInterface::class],
                $internal[Logging\Logger\LevelFetcher\LevelFetcherFactory::class]
            );
        $implement[Logging\Logger\DefaultConfigLoggerFactoryInterface::class] = static fn() =>
            new Logging\Logger\DefaultConfigLoggerFactory(
                $internal[Logging\Logger\LazyInternalFactoryInterface::class],
                $internal[Logging\Config\Basic\ConfigInterface::class],
                $internal[Logging\Logger\LevelFetcher\LevelFetcherFactory::class]
            );
        $implement[Logging\Config\ConfigInterface::class] = static fn() =>
            new Logging\Config\Config(
                $internal[Logging\Config\Basic\ConfigInterface::class],
                $internal[Logging\Config\ByComponent\ConfigInterface::class]
            );

        $contribute[Setup\Agent::class] = static fn() =>
            new Logging\Setup\Agent(
                $pull[Refinery\Factory::class]
            );
    }
}
