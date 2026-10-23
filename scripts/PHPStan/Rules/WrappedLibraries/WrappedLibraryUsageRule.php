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

namespace ILIAS\Scripts\PHPStan\Rules\WrappedLibraries;

use ILIAS\Scripts\PHPStan\Attributes\RuleViolationAllowance;
use ILIAS\Scripts\PHPStan\IliasVersion;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ClassPropertyNode;
use PHPStan\Node\InArrowFunctionNode;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Node\InClassNode;
use PHPStan\Node\InClosureNode;
use PHPStan\Node\InFunctionNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Forbids using a wrapped third-party library outside of the component that wraps it.
 *
 * Some libraries are an implementation detail of exactly one component, which offers
 * its own services and types for them. All other components have to go through that
 * component, so the library can be updated or replaced in one place. Which namespace
 * belongs to which component is listed in {@see self::WRAPPED_LIBRARIES}.
 *
 * Every reference to a class or function of a wrapped namespace is reported: `new`,
 * static calls and properties, class constants (incl. `::class`), `instanceof`,
 * `catch`, function calls, `extends`/`implements`/trait `use`, and parameter, return
 * and property types. Plain `use` imports are not reported on their own, the usage of
 * the imported name is. Docblock types and class names in strings are out of scope.
 *
 * A class, method or function may be exempted from this rule with the
 * {@see \ILIAS\Scripts\PHPStan\Attributes\AllowRuleViolation} attribute (or its
 * convenience subclass {@see \ILIAS\Scripts\PHPStan\Attributes\AllowWrappedLibraryUsage}).
 * Like all exemptions, they are granted for one ILIAS major version, see
 * {@see self::identifier()}.
 *
 * @implements Rule<Node>
 */
final class WrappedLibraryUsageRule implements Rule
{
    public const IDENTIFIER = 'ilias.wrappedLibraryUsage';

    public const LABEL = 'Wrapped library usage';

    /**
     * Namespace of the wrapped library => the component (below components/ILIAS) that
     * wraps it, and a hint what to use instead.
     *
     * @var array<string, array{component: string, hint: string}>
     */
    private const WRAPPED_LIBRARIES = [
        'GuzzleHttp\\Psr7' => [
            'component' => 'HTTP',
            'hint' => 'Type against the Psr\\Http\\Message interfaces and create messages with ILIAS\\HTTP\\Message\\MessageFactory.',
        ],
    ];

    public function __construct(
        private readonly ReflectionProvider $reflection_provider
    ) {
    }

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
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];
        foreach ($this->referencedNames($node, $scope) as [$name, $line]) {
            $library = $this->wrappedLibraryOf($name);
            if ($library === null || $this->isInComponent($scope->getFile(), $library['component'])) {
                continue;
            }
            if (RuleViolationAllowance::isAllowedIn($scope, self::IDENTIFIER)) {
                return [];
            }

            $errors[] = RuleErrorBuilder::message(
                "{$name} belongs to a library wrapped by the {$library['component']} component "
                . "and must not be used outside of it. {$library['hint']}"
            )
                ->identifier(self::identifier())
                ->line($line)
                ->metadata([
                    'rule' => self::LABEL,
                    'version' => 12,
                ])
                ->build();
        }

        return $errors;
    }

    /**
     * @return list<array{string, int}> the fully qualified names referenced directly by
     *                                   the node, each with its line
     */
    private function referencedNames(Node $node, Scope $scope): array
    {
        $names = match (true) {
            $node instanceof Expr\New_,
            $node instanceof Expr\StaticCall,
            $node instanceof Expr\StaticPropertyFetch,
            $node instanceof Expr\ClassConstFetch,
            $node instanceof Expr\Instanceof_ => [$node->class],
            $node instanceof Stmt\Catch_ => $node->types,
            $node instanceof Stmt\TraitUse => $node->traits,
            $node instanceof InClassNode => $this->classHeaderNames($node->getOriginalNode()),
            $node instanceof InClassMethodNode,
            $node instanceof InFunctionNode,
            $node instanceof InClosureNode,
            $node instanceof InArrowFunctionNode => $this->signatureNames($node->getOriginalNode()),
            $node instanceof ClassPropertyNode => $this->typeNames($node->getNativeTypeNode()),
            default => [],
        };

        $referenced = [];
        foreach ($names as $name) {
            if (!$name instanceof Name || $name->isSpecialClassName()) {
                continue;
            }
            $referenced[] = [ltrim($name->toString(), '\\'), $name->getStartLine()];
        }

        if ($node instanceof Expr\FuncCall && $node->name instanceof Name) {
            $function = $this->reflection_provider->resolveFunctionName($node->name, $scope);
            if ($function !== null) {
                $referenced[] = [$function, $node->name->getStartLine()];
            }
        }

        return $referenced;
    }

    /**
     * @return list<Name>
     */
    private function classHeaderNames(Stmt\ClassLike $class): array
    {
        return match (true) {
            $class instanceof Stmt\Class_ => [...($class->extends === null ? [] : [$class->extends]), ...$class->implements],
            $class instanceof Stmt\Interface_ => $class->extends,
            $class instanceof Stmt\Enum_ => $class->implements,
            default => [],
        };
    }

    /**
     * @return list<Name>
     */
    private function signatureNames(Stmt\ClassMethod|Stmt\Function_|Expr\Closure|Expr\ArrowFunction $function): array
    {
        $names = $this->typeNames($function->returnType);
        foreach ($function->params as $param) {
            $names = [...$names, ...$this->typeNames($param->type)];
        }

        return $names;
    }

    /**
     * @return list<Name>
     */
    private function typeNames(?Node $type): array
    {
        return match (true) {
            $type instanceof Name => [$type],
            $type instanceof Node\NullableType => $this->typeNames($type->type),
            $type instanceof Node\UnionType,
            $type instanceof Node\IntersectionType => array_merge(...array_map($this->typeNames(...), $type->types)),
            default => [],
        };
    }

    /**
     * @return array{component: string, hint: string}|null
     */
    private function wrappedLibraryOf(string $name): ?array
    {
        foreach (self::WRAPPED_LIBRARIES as $namespace => $library) {
            if (stripos($name, $namespace . '\\') === 0) {
                return $library;
            }
        }

        return null;
    }

    private function isInComponent(string $file, string $component): bool
    {
        return str_contains(
            str_replace('\\', '/', $file),
            '/components/ILIAS/' . $component . '/'
        );
    }
}
