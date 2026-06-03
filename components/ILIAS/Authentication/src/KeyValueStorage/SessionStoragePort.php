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

namespace ILIAS\Authentication\KeyValueStorage;

use ILIAS\KeyValueStorage\StorageNamespace;
use ILIAS\KeyValueStorage\SessionStoragePort as KeyValueSessionStoragePort;

/**
 * Session-backed implementation of the session storage port.
 *
 * Each entry is stored as a separate top-level session variable keyed by
 * {@see SESSION_ROOT}, namespace, and storage key. See Authentication ADR 0001.
 */
final readonly class SessionStoragePort implements KeyValueSessionStoragePort
{
    private const string SESSION_ROOT = '__ilias_kv_storage__';

    public function has(StorageNamespace $namespace, string $key): bool
    {
        return \ilSession::has($this->buildSessionKey($namespace, $key));
    }

    public function read(StorageNamespace $namespace, string $key): ?string
    {
        $value = \ilSession::get($this->buildSessionKey($namespace, $key));

        return \is_string($value) ? $value : null;
    }

    public function write(StorageNamespace $namespace, string $key, string $value): void
    {
        \ilSession::set($this->buildSessionKey($namespace, $key), $value);
    }

    public function remove(StorageNamespace $namespace, string $key): void
    {
        \ilSession::clear($this->buildSessionKey($namespace, $key));
    }

    public function clearNamespace(StorageNamespace $namespace): void
    {
        $prefix = self::SESSION_ROOT . '.' . $namespace->value() . '.';
        $session = $_SESSION ?? [];

        foreach (\array_keys($session) as $session_key) {
            if (!\is_string($session_key) || !\str_starts_with($session_key, $prefix)) {
                continue;
            }

            \ilSession::clear($session_key);
        }
    }

    private function buildSessionKey(StorageNamespace $namespace, string $key): string
    {
        return self::SESSION_ROOT . '.' . $namespace->value() . '.' . $key;
    }
}
