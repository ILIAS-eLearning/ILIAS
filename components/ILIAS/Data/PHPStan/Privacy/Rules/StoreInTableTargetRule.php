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
use PHPStan\Type\VerbosityLevel;

/**
 * Ensures that StoreInTable is always instantiated with a concrete
 * DbTarget (DbTableColumn/DbTableColumns) object.
 *
 * @implements Rule<Node\Expr\New_>
 */
final class StoreInTableTargetRule implements Rule
{
    public function getNodeType(): string
    {
        return Node\Expr\New_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->class instanceof Node\Name) {
            return [];
        }

        if ($scope->resolveName($node->class) !== \ILIAS\Data\Privacy\Purpose\StoreInTable::class) {
            return [];
        }

        $arg = $node->args[0] ?? null;
        if (!$arg instanceof Node\Arg) {
            return [
                RuleErrorBuilder::message('StoreInTable requires a DbTarget argument.')
                    ->line($node->getLine())
                    ->identifier('ilias.privacy.storeInTableMissingTarget')
                    ->build(),
            ];
        }

        $arg_type = $scope->getType($arg->value);
        $expected_type = new ObjectType(\ILIAS\Data\Privacy\Source\DbTarget::class);

        if (!$expected_type->isSuperTypeOf($arg_type)->yes()) {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'StoreInTable expects a DbTarget (DbTableColumn/DbTableColumns), got %s.'
                        . ' Use the KnownSources catalogue.',
                        $arg_type->describe(VerbosityLevel::typeOnly())
                    )
                )
                ->line($node->getLine())
                ->identifier('ilias.privacy.storeInTableWrongTarget')
                ->build(),
            ];
        }

        return [];
    }
}
