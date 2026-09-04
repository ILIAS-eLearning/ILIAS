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

/**
 * Flags direct DbTableColumn/DbTableColumns instantiation with string
 * literals outside the KnownSources catalogue. Developers should add the
 * column to the matching Known/*Sources class instead.
 *
 * Escape hatch: a @privacy-undocumented comment on the same statement.
 *
 * @implements Rule<Node\Expr\New_>
 */
final class PreferKnownSourcesRule implements Rule
{
    private const array LOCATOR_CLASSES = [
        \ILIAS\Data\Privacy\Source\DbTableColumn::class,
        \ILIAS\Data\Privacy\Source\DbTableColumns::class,
    ];

    private const string CATALOGUE_PATH = 'components/ILIAS/Data/src/Privacy/Source/Known';

    public function getNodeType(): string
    {
        return Node\Expr\New_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->class instanceof Node\Name) {
            return [];
        }

        $class = $scope->resolveName($node->class);
        if (!in_array($class, self::LOCATOR_CLASSES, true)) {
            return [];
        }

        // the catalogue itself is the one legitimate place for literals
        if (str_contains(str_replace('\\', '/', $scope->getFile()), self::CATALOGUE_PATH)) {
            return [];
        }

        if ($this->hasEscapeAnnotation($node, $scope)) {
            return [];
        }

        $literals = [];
        foreach ($node->args as $arg) {
            if ($arg instanceof Node\Arg && $arg->value instanceof Node\Scalar\String_) {
                $literals[] = $arg->value->value;
            }
        }

        if ($literals === []) {
            return [];
        }

        $short = basename(str_replace('\\', '/', $class));

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Direct %s("%s") — add this column to the KnownSources catalogue'
                    . ' or annotate with @privacy-undocumented.',
                    $short,
                    implode('", "', $literals)
                )
            )
            ->line($node->getLine())
            ->identifier('ilias.privacy.unknownSource')
            ->tip('Add a getter to components/ILIAS/Data/src/Privacy/Source/Known.')
            ->build(),
        ];
    }

    /**
     * The escape hatch counts when the annotation is attached to the
     * node itself, sits on the same line, or on the line directly above
     * (parser comment attribution does not reach expressions nested in
     * array items or argument lists).
     */
    private function hasEscapeAnnotation(Node\Expr\New_ $node, Scope $scope): bool
    {
        foreach ($node->getComments() as $comment) {
            if (str_contains($comment->getText(), '@privacy-undocumented')) {
                return true;
            }
        }

        $lines = file($scope->getFile());
        if ($lines === false) {
            return false;
        }
        foreach ([$node->getLine() - 1, $node->getLine() - 2] as $index) {
            if (isset($lines[$index]) && str_contains($lines[$index], '@privacy-undocumented')) {
                return true;
            }
        }

        return false;
    }
}
