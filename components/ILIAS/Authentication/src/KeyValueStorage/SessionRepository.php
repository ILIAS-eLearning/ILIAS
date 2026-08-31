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

use ILIAS\KeyValueStorage\SessionRepository as KeyValueSessionRepository;
use ILIAS\KeyValueStorage\StorageNamespace;

/**
 * Keeps key-value storage in the ILIAS session.
 *
 * Every entry is a session variable of its own, named
 * "<prefix><namespace>:<key>". ilSession only knows single keys - it can
 * neither list nor clear by prefix - so one nested array per namespace would
 * mean reading and writing the whole array on every single write. Flat keys
 * keep a write to one entry, at the price of scanning the session when a whole
 * namespace is dropped, which happens rarely.
 *
 * The colon is what separates namespace from key: it cannot occur in a
 * namespace, and keys must not contain it, so no pair of namespace and key can
 * ever produce the session key of another pair.
 */
final readonly class SessionRepository implements KeyValueSessionRepository
{
    private const string PREFIX = 'kvs:';

    private const string SEPARATOR = ':';

    public function has(StorageNamespace $namespace, string $key): bool
    {
        return \ilSession::has($this->sessionKey($namespace, $key));
    }

    public function read(StorageNamespace $namespace, string $key): ?string
    {
        $value = \ilSession::get($this->sessionKey($namespace, $key));

        return \is_string($value) ? $value : null;
    }

    public function write(StorageNamespace $namespace, string $key, string $value): void
    {
        \ilSession::set($this->sessionKey($namespace, $key), $value);
    }

    public function remove(StorageNamespace $namespace, string $key): void
    {
        \ilSession::clear($this->sessionKey($namespace, $key));
    }

    public function removeAll(StorageNamespace $namespace): void
    {
        $prefix = self::PREFIX . $namespace->value() . self::SEPARATOR;

        foreach (\ilSession::keys() as $session_key) {
            if (\str_starts_with($session_key, $prefix)) {
                \ilSession::clear($session_key);
            }
        }
    }

    private function sessionKey(StorageNamespace $namespace, string $key): string
    {
        return self::PREFIX . $namespace->value() . self::SEPARATOR . $key;
    }
}
