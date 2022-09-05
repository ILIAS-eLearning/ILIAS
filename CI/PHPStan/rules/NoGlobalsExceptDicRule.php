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

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PhpParser\Node\Stmt\Global_;
use ilInitialisation;

final class NoGlobalsExceptDicRule implements Rule
{
    public function getNodeType(): string
    {
        return Global_::class;
    }

    /**
     * @param Global_ $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($scope->isInClass() && $scope->getClassReflection()->getName() === ilInitialisation::class) {
            return [];
        }

        foreach ($node->vars as $variable) {
            $forbidden_globals = [];

            if ($variable->name !== 'DIC') {
                $forbidden_globals[] =  $variable->name;
            }

            if ($forbidden_globals !== []) {
                return [
                    RuleErrorBuilder::message(
                        'You must not use global variables excecpt $DIC: ' . implode(', ', $forbidden_globals)
                    )->build()
                ];
            }
        }

        return [];
    }
}
