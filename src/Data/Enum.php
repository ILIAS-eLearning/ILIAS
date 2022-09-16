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

namespace ILIAS\Data;

use InvalidArgumentException;
use ReflectionClass;

trait Enum
{
    private static ?array $constantsCache = null;
    private int|string $value;

    public function __construct(int|string $value)
    {
        if (!in_array($value, static::constantValues())) {
            throw new InvalidArgumentException(sprintf(
                '%s is not a valid value for this enum.',
                print_r($value, true)
            ));
        }

        $this->value = $value;
    }

    public function value(): int|string
    {
        return $this->value;
    }

    public static function from(int|string $value): self
    {
        return new static($value);
    }

    public static function tryFrom(int|string $value): ?self
    {
        try {
            return new static($value);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    /**
     * @return self[]
     */
    public static function cases(): array
    {
        return array_map(fn (string|int $value): self => new static($value), self::constantValues());
    }

    public static function __callStatic($name, $arguments)
    {
        if (!in_array($name, static::constantKeys())) {
            throw new InvalidArgumentException(sprintf(
                '%s is an invalid value for this enum.',
                print_r($name, true)
            ));
        }

        return new static(constant(get_called_class() . '::' . $name));
    }

    public function equals($other): bool
    {
        if (!($other instanceof self)) {
            return false;
        }

        return $other->value() === $this->value();
    }

    protected static function constants(): array
    {
        if (static::$constantsCache !== null) {
            return static::$constantsCache;
        }

        $reflect = new ReflectionClass(get_called_class());
        return static::$constantsCache = $reflect->getConstants();
    }

    /**
     * @return int[]|string[]
     */
    protected static function constantValues(): array
    {
        return array_values(static::constants());
    }

    protected static function constantKeys(): array
    {
        return array_keys(static::constants());
    }
}
