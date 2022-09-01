<?php

declare(strict_types=1);

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

namespace ILIAS\CI\PHPStan\rules;

use PHPStan\Rules\Rule;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use ilDBInterface;
use ILIAS\CI\PHPStan\services\ControllerDetermination;

final class NoDatabaseUsageInControllersRule implements Rule
{
    public function __construct(
        private ControllerDetermination $determination
    ) {
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param MethodCall $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$scope->isInClass()) {
            return [];
        }

        if (!$this->determination->isController($scope->getClassReflection())) {
            return [];
        }

        $current_object_type = $scope->getType($node->var);
        $database_interface = new ObjectType(ilDBInterface::class);

        if (!$database_interface->isSuperTypeOf($current_object_type)->yes()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'A controller class must not call any method of ilDBInterface'
            )->build(),
        ];
    }
}
