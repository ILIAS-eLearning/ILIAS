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
use ILIAS\CI\PHPStan\services\ControllerDetermination;
use ilTabsGUI;
use ilTemplate;
use ilGlobalTemplateInterface;
use ilLocatorGUI;
use ilToolbarGUI;

final class NoUserInterfaceComponentsInNonControllersRule implements Rule
{
    private const FORBIDDEN_TYPES = [
        ilTemplate::class,
        ilGlobalTemplateInterface::class,
        ilTabsGUI::class,
        ilToolbarGUI::class,
        ilLocatorGUI::class,
        \ILIAS\UI\Component\Component::class,
        \ILIAS\UI\Factory::class,
        \ILIAS\UI\Renderer::class,
    ];

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

        if ($this->determination->isController($scope->getClassReflection())) {
            return [];
        }

        $current_object_type = $scope->getType($node->var);

        $violations = [];
        foreach (self::FORBIDDEN_TYPES as $class_string) {
            $component = new ObjectType($class_string);
            if ($component->isSuperTypeOf($current_object_type)->yes()) {
                $violations[] = RuleErrorBuilder::message(
                    'A non controller class must not call any method of ' . $class_string
                )->build();
            }
        }

        return $violations;
    }
}
