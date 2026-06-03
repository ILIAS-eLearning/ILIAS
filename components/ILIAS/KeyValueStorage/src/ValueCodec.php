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

namespace ILIAS\KeyValueStorage;

use ILIAS\KeyValueStorage\Exception\InvalidStoragePayloadException;

/**
 * Encodes and decodes values for transport through a storage port.
 *
 * Values are stored as plain JSON. PHP {@see unserialize()} is never used.
 * Before encoding, values are validated for JSON compatibility. Objects must
 * implement {@see \JsonSerializable}; {@see \json_encode()} performs the actual
 * serialization (including nested structures).
 */
final readonly class ValueCodec
{
    public function encode(mixed $value): string
    {
        $this->assertEncodableValue($value);

        try {
            return \json_encode($value, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw InvalidStoragePayloadException::fromJsonException($exception);
        }
    }

    public function decode(string $value): mixed
    {
        try {
            $decoded = \json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw InvalidStoragePayloadException::fromJsonException($exception);
        }

        $this->assertStorableDecodedValue($decoded);

        return $decoded;
    }

    private function assertEncodableValue(mixed $value): void
    {
        if ($value === null || \is_bool($value) || \is_int($value) || \is_float($value) || \is_string($value)) {
            return;
        }

        if (\is_array($value)) {
            foreach ($value as $key => $item) {
                if (!\is_int($key) && !\is_string($key)) {
                    throw new \InvalidArgumentException('Array keys must be integers or strings.');
                }

                $this->assertEncodableValue($item);
            }

            return;
        }

        if ($value instanceof \JsonSerializable) {
            $this->assertEncodableValue($value->jsonSerialize());

            return;
        }

        throw new \InvalidArgumentException(
            'Only JSON-serializable values can be stored. Supported: null, boolean, integer, float, string, '
            . 'array, and objects implementing JsonSerializable. Got ' . \gettype($value) . '.'
        );
    }

    private function assertStorableDecodedValue(mixed $value): void
    {
        if ($value === null || \is_bool($value) || \is_int($value) || \is_float($value) || \is_string($value)) {
            return;
        }

        if (\is_array($value)) {
            foreach ($value as $key => $item) {
                if (!\is_int($key) && !\is_string($key)) {
                    throw InvalidStoragePayloadException::fromInvalidStructure(
                        'Decoded array keys must be integers or strings.'
                    );
                }

                $this->assertStorableDecodedValue($item);
            }

            return;
        }

        throw InvalidStoragePayloadException::fromInvalidStructure(
            'Decoded value contains unsupported type ' . \gettype($value) . '.'
        );
    }
}
