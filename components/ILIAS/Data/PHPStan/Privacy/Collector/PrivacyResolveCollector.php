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

namespace ILIAS\Data\Privacy\PHPStan\Collector;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Type\ObjectType;
use PHPStan\Type\VerbosityLevel;

/**
 * Collects every resolve() call on a PrivacyDataType instance. The
 * collected entries are turned into report "errors" by
 * {@see PrivacyResolveReportRule} and rendered into per-component
 * privacy documentation by scripts/Privacy/generate-privacy-docs.php.
 *
 * @implements Collector<Node\Expr\MethodCall, array{
 *   privacy_type: string,
 *   purpose_class: string,
 *   purpose_args: list<string>,
 *   component: string,
 *   line: int
 * }>
 */
final class PrivacyResolveCollector implements Collector
{
    public function getNodeType(): string
    {
        return Node\Expr\MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): ?array
    {
        if (!$node->name instanceof Node\Identifier) {
            return null;
        }
        if ($node->name->name !== 'resolve') {
            return null;
        }

        $caller_type = $scope->getType($node->var);
        $privacy_base = new ObjectType(\ILIAS\Data\Privacy\PrivacyDataType::class);
        if (!$privacy_base->isSuperTypeOf($caller_type)->yes()) {
            return null;
        }

        $purpose_class = 'unknown';
        $purpose_args = [];
        $arg = $node->args[0] ?? null;
        if ($arg instanceof Node\Arg) {
            [$purpose_class, $purpose_args] = $this->extractPurposeInfo($arg->value, $scope);
        }

        return [
            'privacy_type' => $caller_type->describe(VerbosityLevel::typeOnly()),
            'purpose_class' => $purpose_class,
            'purpose_args' => $purpose_args,
            'component' => $this->extractComponent($scope->getFile()),
            'line' => $node->getLine(),
        ];
    }

    /**
     * @return array{string, list<string>}
     */
    private function extractPurposeInfo(Node\Expr $expr, Scope $scope): array
    {
        // purposes built via the Purposes factory: the call's return type
        // is the concrete purpose class, the call arguments carry the data
        if ($expr instanceof Node\Expr\MethodCall && $expr->name instanceof Node\Identifier) {
            foreach ($scope->getType($expr)->getObjectClassNames() as $class) {
                if (str_starts_with($class, 'ILIAS\\Data\\Privacy\\Purpose\\')) {
                    return [
                        basename(str_replace('\\', '/', $class)),
                        $this->extractArgs($expr->args, $scope),
                    ];
                }
            }

            // caller type unresolvable (e.g. factory obtained from the
            // untyped legacy container): map the factory method name to
            // its declared return type
            $method = $expr->name->name;
            if (method_exists(\ILIAS\Data\Privacy\Purpose\Purposes::class, $method)) {
                $return_type = new \ReflectionMethod(\ILIAS\Data\Privacy\Purpose\Purposes::class, $method)
                    ->getReturnType();
                if ($return_type instanceof \ReflectionNamedType
                    && is_a($return_type->getName(), \ILIAS\Data\Privacy\Purpose\Purpose::class, true)
                ) {
                    return [
                        basename(str_replace('\\', '/', $return_type->getName())),
                        $this->extractArgs($expr->args, $scope),
                    ];
                }
            }
        }

        if (!$expr instanceof Node\Expr\New_) {
            return ['dynamic', [$scope->getType($expr)->describe(VerbosityLevel::typeOnly())]];
        }
        if (!$expr->class instanceof Node\Name) {
            return ['unknown', []];
        }

        $class = $scope->resolveName($expr->class);
        $short = basename(str_replace('\\', '/', $class));

        return [$short, $this->extractArgs($expr->args, $scope)];
    }

    /**
     * @param array<Node\Arg|Node\VariadicPlaceholder> $raw_args
     * @return list<string>
     */
    private function extractArgs(array $raw_args, Scope $scope): array
    {
        $args = [];
        foreach ($raw_args as $arg) {
            if (!$arg instanceof Node\Arg) {
                continue;
            }
            $known_source = $this->tryDescribeKnownSourceCall($arg->value, $scope);
            if ($known_source !== null) {
                $args[] = $known_source;
            } elseif ($arg->value instanceof Node\Scalar\String_) {
                $args[] = $arg->value->value;
            } elseif ($arg->value instanceof Node\Expr\New_
                && $arg->value->class instanceof Node\Name
            ) {
                $inner_args = array_map(
                    static fn($a): string => $a instanceof Node\Arg && $a->value instanceof Node\Scalar\String_
                        ? $a->value->value
                        : '?',
                    $arg->value->args
                );
                $args[] = basename(str_replace('\\', '/', (string) $arg->value->class))
                    . '(' . implode(', ', $inner_args) . ')';
            } else {
                $args[] = $scope->getType($arg->value)->describe(VerbosityLevel::typeOnly());
            }
        }

        return $args;
    }

    /**
     * The KnownSources catalogue getters are pure, parameterless value
     * factories — evaluate them to render the actual table/columns in
     * the report instead of just the return type.
     */
    private function tryDescribeKnownSourceCall(Node\Expr $expr, Scope $scope): ?string
    {
        if (!$expr instanceof Node\Expr\MethodCall || !$expr->name instanceof Node\Identifier) {
            return null;
        }

        foreach ($scope->getType($expr->var)->getObjectClassNames() as $class) {
            if (!str_starts_with($class, 'ILIAS\\Data\\Privacy\\Source\\Known\\')) {
                continue;
            }
            $method = $expr->name->name;
            if (!method_exists($class, $method)) {
                continue;
            }
            if (new \ReflectionMethod($class, $method)->getNumberOfRequiredParameters() > 0) {
                continue;
            }
            $result = (new $class())->{$method}();
            if ($result instanceof \ILIAS\Data\Privacy\Source\Source) {
                return $result->describe();
            }
        }

        return null;
    }

    private function extractComponent(string $file_path): string
    {
        if (preg_match('#components/ILIAS/([^/]+)/#', str_replace('\\', '/', $file_path), $m) === 1) {
            return $m[1];
        }
        return 'Unknown';
    }
}
