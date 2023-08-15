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

namespace ILIAS\Cache\Adaptor;

use ILIAS\Cache\Config;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class PHPStatic implements Adaptor
{
    protected static array $cache = [];

    public function __construct(Config $config)
    {
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function has(string $container, string $key): bool
    {
        return isset(self::$cache[$container][$key]);
    }

    public function get(string $container, string $key): ?string
    {
        return self::$cache[$container][$key] ?? null;
    }

    public function set(string $container, string $key, string $value, int $ttl): void
    {
        self::$cache[$container][$key] = $value;
    }

    public function delete(string $container, string $key): void
    {
        unset(self::$cache[$container][$key]);
    }

    public function flushContainer(string $container): void
    {
        self::$cache[$container] = [];
    }

    public function flush(): void
    {
        self::$cache = [];
    }
}
