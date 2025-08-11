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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class APCu extends BaseAdaptor implements Adaptor
{
    protected static ?bool $is_available = null;

    public function isAvailable(): bool
    {
        return self::$is_available ?? self::$is_available = (\function_exists('apcu_fetch') && filter_var(\ini_get('apc.enabled'), \FILTER_VALIDATE_BOOL));
    }

    public function has(string $container, string $key): bool
    {
        return apcu_exists($this->buildKey($container, $key)) === true;
    }

    public function get(string $container, string $key): ?string
    {
        $success = false;
        $return = apcu_fetch($this->buildKey($container, $key), $success) ?: null;
        if (!$success) {
            return null;
        }

        return $return;
    }

    public function set(string $container, string $key, string $value, int $ttl): void
    {
        if (!apcu_store($this->buildKey($container, $key), $value, $ttl)) {
            file_put_contents($container . '_chache.log', $key);
        }
    }

    public function delete(string $container, string $key): void
    {
        apcu_delete($this->buildKey($container, $key));
    }

    public function flushContainer(string $container): void
    {
        apcu_delete(new \APCUIterator('/^' . $this->buildContainerPrefix($container) . '/'));
    }

    public function flush(): void
    {
        apcu_clear_cache();
    }
}
