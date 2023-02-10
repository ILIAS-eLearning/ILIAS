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

namespace ILIAS\CI\PHPStan\Rules;

use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;

abstract class LegacyClassUsageRule implements Rule
{
    protected ReflectionProvider $reflectionProvider;
    protected \PHPStan\Rules\Generics\GenericAncestorsCheck $genericAncestorsCheck;

    public function __construct(
        ReflectionProvider $reflectionProvider,
        \PHPStan\Rules\Generics\GenericAncestorsCheck $genericAncestorsCheck
    ) {
        $this->reflectionProvider = $reflectionProvider;
        $this->genericAncestorsCheck = $genericAncestorsCheck;
    }

    public function getNodeType(): string
    {
        return Node\Expr::class;
    }

    abstract protected function getForbiddenClasses(): array;

    abstract protected function getHumanReadableRuleName(): string;

    final public function processNode(Node $node, Scope $scope): array
    {
        switch (true) {
            case $node instanceof Node\Expr\StaticCall:
            case $node instanceof Node\Expr\New_:
                if ($node->class instanceof Node\Name) {
                    $class_name = $node->class->toString();
                } else {
                    return [];
                }
                break;
            default:
                return [];
        }

        try {
            $reflection = $this->reflectionProvider->getClass($class_name);
            $parent_class_names = $reflection->getParentClassesNames();
        } catch (\Throwable $t) {
            $parent_class_names = [];
        }
        $class_names_to_test = array_merge($parent_class_names, [$class_name]);
        unset($reflection);
        foreach ($this->getForbiddenClasses() as $class) {
            if (in_array($class, $class_names_to_test)) {
                return [
                    RuleErrorBuilder::message($class_name)
                        ->metadata(['rule' => $this->getHumanReadableRuleName()])
                        ->build()
                ];
            }
        }
        return [];
    }
}
