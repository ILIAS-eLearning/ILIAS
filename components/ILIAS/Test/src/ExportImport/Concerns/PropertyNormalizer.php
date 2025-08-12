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

namespace ILIAS\Test\ExportImport\Concerns;

use ILIAS\Test\ExportImport\Contracts\Normalizable;
use ReflectionClass;
use ReflectionProperty;

/**
 * Trait for automatic normalization of object properties. The trait is especially useful for all objects that set all
 * properties via the constructor and have no additional setters.
 */
trait PropertyNormalizer
{
    /**
     * Automatically normalize all properties of the object into an array. It handles the following types:
     *
     * - Basic scalar types (int, string, bool, float)
     * - Arrays (recursively normalized)
     * - Nested objects implementing Normalizable
     * - DateTimeImmutable objects (converted to ISO 8601 strings)
     * - Null values
     *
     * @return array<string, mixed>
     */
    public function normalize(): array
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);

        $normalized = [];

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($this);
            $normalized[$property->getName()] = $this->normalizeValue($value);
        }

        return $normalized;
    }

    private function normalizeValue($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_scalar($value)) {
            return $value;
        }

        if (is_array($value)) {
            return $this->normalizeArray($value);
        }

        if ($value instanceof \DateTimeImmutable) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        if ($value instanceof Normalizable) {
            return $value->normalize();
        }

        throw new \InvalidArgumentException('Unsupported type: ' . gettype($value));
    }

    private function normalizeArray(array $array): array
    {
        return array_map(function ($value) {
            return $this->normalizeValue($value);
        }, $array);
    }

    /**
     * Automatically denormalize data to create a new instance. It will provide the required constructor arguments based
     * on the provided data. This is especially useful for objects with constructor property promotion.
     *
     * @param array<string, mixed> $data
     * @return static
     */
    public static function denormalize(array $data): static
    {
        $reflection = new ReflectionClass(static::class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            throw new \InvalidArgumentException('Class must have a constructor');
        }

        $parameters = $constructor->getParameters();
        $arguments = [];

        foreach ($parameters as $parameter) {
            $parameter_name = $parameter->getName();
            $parameter_type = $parameter->getType();

            if (!isset($data[$parameter_name])) {
                if ($parameter->isDefaultValueAvailable()) {
                    $arguments[] = $parameter->getDefaultValue();
                    continue;
                }

                throw new \InvalidArgumentException("Missing required parameter: {$parameter_name}");
            }

            $value = $data[$parameter_name];
            $arguments[] = self::denormalizeValue($value, $parameter_type);
        }

        return new static(...$arguments);
    }

    private static function denormalizeValue($value, ?\ReflectionType $type)
    {
        if ($value === null) {
            return null;
        }

        if ($type === null) {
            return $value;
        }

        // Handle union types
        if ($type instanceof \ReflectionUnionType) {
            $types = $type->getTypes();
            foreach ($types as $union_type) {
                try {
                    return self::denormalizeValue($value, $union_type);
                } catch (\InvalidArgumentException $e) {
                    continue;
                }
            }
            throw new \InvalidArgumentException('Value does not match any union type');
        }

        // Handle intersection types
        if ($type instanceof \ReflectionIntersectionType) {
            $types = $type->getTypes();
            foreach ($types as $intersection_type) {
                $value = self::denormalizeValue($value, $intersection_type);
            }
            return $value;
        }

        // Handle named types
        if ($type instanceof \ReflectionNamedType) {
            $type_name = $type->getName();

            // Handle built-in types
            switch ($type_name) {
                case 'int':
                    return (int) $value;
                case 'float':
                    return (float) $value;
                case 'string':
                    return (string) $value;
                case 'bool':
                    return (bool) $value;
                case 'array':
                    return self::denormalizeArray($value);
                case 'DateTimeImmutable':
                    return new \DateTimeImmutable($value);
                case 'DateTime':
                    return new \DateTime($value);
                default:
                    if (
                        class_exists($type_name) &&
                        in_array(Normalizable::class, class_implements($type_name))
                    ) {
                        return $type_name::denormalize($value);
                    }
                    return $value;
            }
        }

        return $value;
    }

    private static function denormalizeArray($value): array
    {
        if (!is_array($value)) {
            throw new \InvalidArgumentException('Value must be an array');
        }

        return array_map(function ($item) {
            return self::denormalizeValue($item, null);
        }, $value);
    }
}
