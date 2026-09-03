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

namespace ILIAS\FileDelivery\Token\Signer\Key\Secret;

/**
 * Key rotation can provide an extra layer of mitigation against an attacker discovering a secret key. A rotation system
 * will keep a list of valid keys, generating a new key and removing the oldest key periodically. If it takes four weeks
 * for an attacker to crack a key, but the key is rotated out after three weeks, they will not be able to use any keys
 * they crack. However, if a user doesn’t refresh their token within three weeks it will be invalid too.
 *
 * The system that generates and maintains this list is outside the scope of ItsDangerous, but ItsDangerous does support
 * validating against a list of keys.
 *
 * Instead of passing a single key, you can pass a list of keys, oldest to newest. When signing the last (newest) key
 * will be used, and when validating each key will be tried from newest to oldest before raising a validation error.
 *
 * @see    https://itsdangerous.palletsprojects.com/en/2.1.x/concepts/#key-rotation
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 *
 * @internal
 */
final class SecretKeyRotation
{
    /**
     * @var SecretKey[]
     */
    private array $older_keys;

    public function __construct(
        private SecretKey $current_key,
        SecretKey ...$older_keys
    ) {
        $this->older_keys = $older_keys;
    }

    /**
     * Load a rotation from a static PHP artefact written by the setup, newest
     * key first. The path is passed in by the caller, since every component
     * that signs data keeps its own set of keys.
     *
     * A missing or unusable artefact yields a rotation with a generated key.
     * That happens before the setup has ever run - on a fresh checkout the
     * artefact directory does not even exist yet, and the bootstrap is built
     * against it - and a key that is not shared between processes simply makes
     * every token fail verification, which is the safe outcome.
     */
    public static function fromArtefact(string $path): self
    {
        $stored = is_file($path) ? @include $path : [];

        $keys = array_map(
            static fn(string $key): SecretKey => new SecretKey($key),
            array_values(array_filter(is_array($stored) ? $stored : [], 'is_string'))
        );

        $current_key = array_shift($keys) ?? self::generatedKey();

        return new self($current_key, ...$keys);
    }

    private static function generatedKey(): SecretKey
    {
        return new SecretKey(bin2hex(random_bytes(32)));
    }

    public function getAllKeys(): array
    {
        return array_merge([$this->current_key], $this->older_keys);
    }

    public function getOlderKeys(): array
    {
        return $this->older_keys;
    }

    public function getCurrentKey(): SecretKey
    {
        return $this->current_key;
    }
}
