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

namespace ILIAS\Scripts\PHPStan\Attributes;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Php\PhpFunctionReflection;

/**
 * Shared helper for ILIAS custom PHPStan rules: decides whether the code position
 * described by a {@see Scope} is exempt from a rule via an {@see AllowRuleViolation}
 * attribute (or any subclass of it) on the enclosing class, method or function.
 *
 * Rules call {@see self::isAllowedIn()} with their own error identifier before
 * emitting an error.
 */
final class RuleViolationAllowance
{
    public static function isAllowedIn(Scope $scope, string $rule_identifier): bool
    {
        try {
            foreach (self::reflections($scope) as $reflection) {
                // IS_INSTANCEOF so convenience subclasses (e.g. AllowSuperglobalWrite) match too.
                foreach ($reflection->getAttributes(
                    AllowRuleViolation::class,
                    \ReflectionAttribute::IS_INSTANCEOF
                ) as $attribute) {
                    $allowance = $attribute->newInstance();
                    if (in_array($rule_identifier, $allowance->rules, true)) {
                        return true;
                    }
                }
            }
        } catch (\Throwable) {
            // Reflection can fail for not-yet-loadable symbols; never break analysis over it.
            return false;
        }

        return false;
    }

    /**
     * @return iterable<\ReflectionClass<object>|\ReflectionMethod|\ReflectionFunction>
     */
    private static function reflections(Scope $scope): iterable
    {
        $class_reflection = $scope->getClassReflection();
        if ($class_reflection !== null) {
            $native_class = $class_reflection->getNativeReflection();
            yield $native_class;

            $function_name = $scope->getFunctionName();
            if ($function_name !== null && $native_class->hasMethod($function_name)) {
                yield $native_class->getMethod($function_name);
            }

            return;
        }

        $function = $scope->getFunction();
        if ($function instanceof PhpFunctionReflection) {
            yield $function->getNativeReflection();
        }
    }
}
