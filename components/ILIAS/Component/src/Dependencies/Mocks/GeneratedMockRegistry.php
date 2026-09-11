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

namespace ILIAS\Component\Dependencies\Mocks;

/**
 * Bookkeeping about the mock classes that have been generated into the running
 * process.
 *
 * The state has to be process-wide: a generated class is declared in the global
 * scope, so declaring it a second time - from whatever builder instance - is a
 * fatal error. The registry also hands the generated mocks back to the builder
 * that created them, so that nested mocks are built the same way the outer one
 * was.
 *
 * @internal This class can only be used in Bootstrap
 */
final class GeneratedMockRegistry
{
    /** @var array<class-string, array<string, array<string, mixed>>> */
    private static array $return_types = [];

    /** @var array<class-string, AbstractLightMockBuilder> */
    private static array $builders = [];

    /**
     * @param class-string $generated_fqcn
     */
    public static function isGenerated(string $generated_fqcn): bool
    {
        return isset(self::$builders[$generated_fqcn]);
    }

    /**
     * @param class-string                     $generated_fqcn
     * @param array<string, array<string, mixed>> $return_types method name => normalized return type
     */
    public static function register(
        string $generated_fqcn,
        array $return_types,
        AbstractLightMockBuilder $builder
    ): void {
        self::$return_types[$generated_fqcn] = $return_types;
        self::$builders[$generated_fqcn] = $builder;
    }

    /**
     * @return array<string, mixed>|null null if the class was not generated here,
     *                                   or if it has no such method
     */
    public static function returnTypeFor(string $generated_fqcn, string $method): ?array
    {
        return self::$return_types[$generated_fqcn][$method] ?? null;
    }

    /**
     * Entry point for the generated mocks themselves, see {@see MockObjectBehavior}.
     */
    public static function defaultValueFor(object $mock, string $method): mixed
    {
        $builder = self::$builders[$mock::class] ?? null;

        if ($builder === null) {
            // not a generated mock - nothing to derive a value from
            return null;
        }

        return $builder->defaultValueFor($mock, $method);
    }
}
