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

namespace ILIAS\Scripts\PHPStan\Rules\SuperGlobals;

use ILIAS\Scripts\PHPStan\Attributes\RuleViolationAllowance;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Forbids writing to request-input superglobals.
 *
 * The request is immutable: values read from PHP's request-input superglobals
 * ($_GET, $_POST, $_REQUEST, $_COOKIE, $_FILES) must never be mutated. Code that
 * needs to pass values around has to use the HTTP service / request wrapper, not
 * write back into the superglobal.
 *
 * Concrete subclasses only pick the assignment node type they analyse; the actual
 * detection logic lives here. All handled node types ({@see Expr\Assign},
 * {@see Expr\AssignOp}, {@see Expr\AssignRef}) expose the assignment target as
 * their public `$var` property, which is what {@see self::processNode()} inspects.
 *
 * A class, method or function may be exempted from this rule with the
 * {@see \ILIAS\Scripts\PHPStan\Attributes\AllowRuleViolation} attribute (or its
 * convenience subclass {@see \ILIAS\Scripts\PHPStan\Attributes\AllowSuperglobalWrite}).
 *
 * Known, deliberately uncovered write vectors (documented gaps, out of scope):
 * extract(), variable-variables ($$name), by-reference function parameters and
 * list()/array-destructuring targets.
 */
abstract class AbstractSuperglobalWriteRule implements Rule
{
    public const IDENTIFIER = 'ilias.superglobalWrite';

    public const LABEL = 'Superglobal write';

    /**
     * Variable names (without leading `$`) that must not be written to.
     *
     * @return list<string>
     */
    protected function getForbiddenSuperglobals(): array
    {
        return ['_GET', '_POST', '_REQUEST', '_COOKIE', '_FILES'];
    }

    final public function processNode(Node $node, Scope $scope): array
    {
        // All handled assignment nodes expose the write target as `$var`.
        if (!isset($node->var) || !$node->var instanceof Expr) {
            return [];
        }

        $superglobal = $this->findWrittenSuperglobal($node->var);
        if ($superglobal === null) {
            return [];
        }

        if (RuleViolationAllowance::isAllowedIn($scope, self::IDENTIFIER)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                "Writing to the superglobal \$$superglobal is forbidden. "
                . 'The request is immutable; use the HTTP service / request wrapper instead.'
            )
                ->identifier(self::IDENTIFIER)
                ->metadata([
                    'rule' => self::LABEL,
                    'version' => 12,
                ])
                ->build()
        ];
    }

    /**
     * Walks an assignment target down to its root variable and returns the
     * superglobal name (without `$`) if that root is a forbidden superglobal.
     *
     * Covers `$_GET['x'] = …`, nested dimensions `$_GET['a']['b'] = …`,
     * appends `$_GET[] = …` and whole-array overwrites `$_GET = …`.
     */
    private function findWrittenSuperglobal(Expr $target): ?string
    {
        while ($target instanceof ArrayDimFetch) {
            $target = $target->var;
        }

        if (!$target instanceof Variable || !is_string($target->name)) {
            return null;
        }

        return in_array($target->name, $this->getForbiddenSuperglobals(), true)
            ? $target->name
            : null;
    }
}
