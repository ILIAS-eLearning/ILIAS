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

namespace ILIAS\Data\Privacy\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * Forbids passing PrivacyDataType instances directly to functions that
 * would expose or serialize the raw object state.
 *
 * @implements Rule<Node\Expr\FuncCall>
 */
final class NoRawValueAccessRule implements Rule
{
    private const array FORBIDDEN_FUNCTIONS = [
        'var_dump', 'print_r', 'var_export',
        'json_encode', 'serialize',
    ];

    public function getNodeType(): string
    {
        return Node\Expr\FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Node\Name) {
            return [];
        }

        $function_name = strtolower((string) $node->name);
        if (!in_array($function_name, self::FORBIDDEN_FUNCTIONS, true)) {
            return [];
        }

        $errors = [];
        foreach ($node->args as $arg) {
            if (!$arg instanceof Node\Arg) {
                continue;
            }
            $type = $scope->getType($arg->value);
            if (new ObjectType(\ILIAS\Data\Privacy\PrivacyDataType::class)->isSuperTypeOf($type)->yes()) {
                $errors[] = RuleErrorBuilder::message(
                    sprintf(
                        'PrivacyDataType passed directly to %s(). Call ->resolve() with a Purpose first.',
                        $function_name
                    )
                )
                ->line($node->getLine())
                ->identifier('ilias.privacy.rawAccess')
                ->build();
            }
        }

        return $errors;
    }
}
