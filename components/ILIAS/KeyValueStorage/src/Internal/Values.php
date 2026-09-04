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

namespace ILIAS\KeyValueStorage\Internal;

use ILIAS\KeyValueStorage\Exception\InvalidStoredValueException;

/**
 * Translates between the values a consumer works with and the strings a
 * repository stores.
 *
 * Values are stored as JSON. serialize()/unserialize() are never used, so
 * reading a value can never instantiate an object.
 *
 * @internal
 */
final readonly class Values
{
    private const int MAX_DEPTH = 512;

    /**
     * @throws \InvalidArgumentException if the value cannot be stored
     */
    public function encode(mixed $value): string
    {
        $this->checkEncodable($value);

        try {
            return \json_encode($value, JSON_THROW_ON_ERROR, self::MAX_DEPTH);
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException(
                'The value could not be encoded: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * @throws InvalidStoredValueException if the stored string cannot be read back
     */
    public function decode(string $value): mixed
    {
        try {
            return \json_decode($value, true, self::MAX_DEPTH, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new InvalidStoredValueException('The stored value is not valid JSON.', 0, $e);
        }
    }

    private function checkEncodable(mixed $value): void
    {
        if ($value === null || \is_scalar($value)) {
            return;
        }

        if (\is_array($value)) {
            foreach ($value as $item) {
                $this->checkEncodable($item);
            }

            return;
        }

        if ($value instanceof \JsonSerializable) {
            $this->checkEncodable($value->jsonSerialize());

            return;
        }

        throw new \InvalidArgumentException(
            'Only null, scalars, arrays of those and objects implementing JsonSerializable can be stored, got '
            . \get_debug_type($value) . '.'
        );
    }
}
