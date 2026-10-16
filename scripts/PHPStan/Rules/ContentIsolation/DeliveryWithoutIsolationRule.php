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

namespace ILIAS\Scripts\PHPStan\Rules\ContentIsolation;

use ILIAS\ResourceStorage\Consumer\ContainerURIConsumer;
use ILIAS\Scripts\PHPStan\Attributes\RuleViolationAllowance;
use ILIAS\Scripts\PHPStan\IliasVersion;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Reports every delivery of user content that bypasses the User Content Isolation.
 *
 * Content delivered from the ILIAS domain runs with the session of the user on the
 * ILIAS origin, which is exactly what the isolation prevents. Consumers can only do
 * so through the methods below, which exist for content that cannot run on the content
 * domain yet (SCORM) and are deprecated; they will be removed with ILIAS 13. The
 * methods FileDelivery and the IRSS use internally to implement them are not reported.
 *
 * A caller has to be allowed explicitly with the
 * {@see \ILIAS\Scripts\PHPStan\Attributes\AllowDeliveryWithoutIsolation} attribute (or
 * {@see \ILIAS\Scripts\PHPStan\Attributes\AllowRuleViolation} with this identifier).
 * The allowance is granted for one ILIAS major and expires with the next one.
 *
 * @implements Rule<MethodCall>
 */
final class DeliveryWithoutIsolationRule implements Rule
{
    public const IDENTIFIER = 'ilias.deliveryWithoutIsolation';

    public const LABEL = 'Delivery without content isolation';

    /**
     * Method name => the type declaring it.
     */
    private const METHODS = [
        'withoutisolation' => ContainerURIConsumer::class,
    ];

    /**
     * The reported identifier, carrying the ILIAS major the analysis runs against, so
     * inline ignores expire with the release they were written for.
     */
    public static function identifier(): string
    {
        return self::IDENTIFIER . '.v' . IliasVersion::major();
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Identifier) {
            return [];
        }

        $declaring_type = self::METHODS[$node->name->toLowerString()] ?? null;
        if ($declaring_type === null || !$this->isCalledOn($declaring_type, $node, $scope)) {
            return [];
        }

        if (RuleViolationAllowance::isAllowedIn($scope, self::IDENTIFIER)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Delivering content without User Content Isolation (%s::%s()) is deprecated and will be '
                    . 'removed with ILIAS 13. Deliver the content from the content domain, or allow the call '
                    . 'for this release with #[AllowDeliveryWithoutIsolation(reason, %d)].',
                    $declaring_type,
                    $node->name->toString(),
                    IliasVersion::major()
                )
            )
                ->identifier(self::identifier())
                ->metadata([
                    'rule' => self::LABEL,
                    'version' => IliasVersion::major(),
                ])
                ->build()
        ];
    }

    /**
     * The method names are specific enough to report a call on a value of unknown type
     * as well; a value of a known, unrelated type is left alone.
     */
    private function isCalledOn(string $declaring_type, MethodCall $node, Scope $scope): bool
    {
        $class_reflections = $scope->getType($node->var)->getObjectClassReflections();
        if ($class_reflections === []) {
            return true;
        }

        foreach ($class_reflections as $class_reflection) {
            if ($class_reflection->is($declaring_type)) {
                return true;
            }
        }

        return false;
    }
}
