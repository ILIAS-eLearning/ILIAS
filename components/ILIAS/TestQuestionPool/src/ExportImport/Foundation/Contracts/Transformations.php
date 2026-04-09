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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;
use ILIAS\Refinery\Custom\Group;

/**
 * Provides a set of transformations for normalizing and denormalizing values. It uses the Refinery library to perform
 * the transformations. It also provides a registry of normalizers, which are used to handle the normalization and
 * denormalization of complex objects.
 */
interface Transformations
{
    /*
        Normalization/Denormalization
    */

    /**
     * Converts a value into a normalized form. If the value is a `Normalizable` object, it will be converted using the
     * object's toNormalized method. Otherwise, it will be converted using the appropriate normalizer.
     *
     * @throws NormalizingException if the value is not supported
     */
    public function normalize(mixed $value, array $context = []): array|float|bool|int|string|null;

    /**
     * Converts a normalized form back. If the expected type is a class string, it attempts to create a new instance of
     * the class using normalizer for the type. If the expected parameter is an object of the `Normalizable` interface,
     * an copy of the object will be returned with the new state from the normalized form.
     *
     * @template T of object
     * @param class-string<T>|T $expected
     * @return T|null
     *
     * @throws NormalizingException if the type is not supported
     */
    public function denormalize(array|float|bool|int|string|null $normalized, string|object $expected): mixed;

    /*
        Transformations
    */

    /**
     * Returns the pipe of the given class from the pipeline.
     *
     * @template T of Pipe
     * @param class-string<T> $pipe_class
     * @return T
     *
     * @throws \InvalidArgumentException if the pipe is not found
     */
    public function context(string $pipe_class): Pipe;

    /**
     * Returns a group of transformations that can be used to create custom transformations.
     */
    public function custom(): Group;

    /**
     * @throws \InvalidArgumentException if the value cannot be transformed into an integer
     */
    public function int(mixed $value): int;

    /**
     * @throws \InvalidArgumentException if the value cannot be transformed into a float
     */
    public function float(mixed $value): float;

    /**
     * @throws \InvalidArgumentException if the value cannot be transformed into a string
     */
    public function string(mixed $value): string;

    /**
     * @throws \InvalidArgumentException if the value cannot be transformed into a boolean
     */
    public function bool(mixed $value): bool;

    public function nullableInt(mixed $value): ?int;

    public function nullableFloat(mixed $value): ?float;

    public function nullableString(mixed $value): ?string;

    public function nullableBool(mixed $value): ?bool;
}
