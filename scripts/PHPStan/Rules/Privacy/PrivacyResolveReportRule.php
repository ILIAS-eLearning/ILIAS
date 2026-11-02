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

namespace ILIAS\Scripts\PHPStan\Rules\Privacy;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\VerbosityLevel;

/**
 * Reports every resolve() call on a PrivacyDataType instance as one
 * "error" whose message is the marker followed by a JSON payload, so the
 * calls end up in PHPStan's JSON output, where
 * scripts/Privacy/generate-privacy-docs.php reads them.
 *
 * The rule only reads PHPStan's static view of the code; it never loads or
 * executes analysed classes. Calls to a source catalogue are recorded as
 * "catalogue:Fully\\Qualified\\Catalogue::getter" and described by the
 * generator.
 *
 * Only registered in privacy-analysis.neon for generator runs — never in a
 * linting configuration.
 *
 * @implements Rule<Node\Expr\MethodCall>
 */
class PrivacyResolveReportRule implements Rule
{
    private const string REQUIREMENT_NAMESPACE = 'ILIAS\\Data\\Privacy\\TechnicalRequirement\\';
    public const string MARKER = 'PRIVACY-RESOLVE ';
    public const string CATALOGUE_PREFIX = 'catalogue:';

    public function __construct(
        private readonly ReflectionProvider $reflection_provider,
    ) {
    }

    public function getNodeType(): string
    {
        return Node\Expr\MethodCall::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Node\Identifier || $node->name->name !== 'resolve') {
            return [];
        }

        // a nullable wrapper, e.g. at the end of a ?-> chain, is still a wrapper
        $caller_type = TypeCombinator::removeNull($scope->getType($node->var));
        $privacy_base = new ObjectType(\ILIAS\Data\Privacy\PrivacyDataType::class);
        if (!$privacy_base->isSuperTypeOf($caller_type)->yes()) {
            return [];
        }

        $requirement_class = 'unknown';
        $requirement_args = [];
        $arg = $node->args[0] ?? null;
        if ($arg instanceof Node\Arg) {
            [$requirement_class, $requirement_args] = $this->extractTechnicalRequirementInfo($arg->value, $scope);
        }

        // a union of wrappers is reported once per type
        $errors = [];
        foreach ($caller_type->getObjectClassNames() as $privacy_type) {
            $payload = [
                'privacy_type' => $privacy_type,
                'requirement_class' => $requirement_class,
                'requirement_args' => $requirement_args,
                'component' => $this->extractComponent($scope->getFile()),
                'location' => $this->extractLocation($scope),
            ];
            $errors[] = RuleErrorBuilder::message(self::MARKER . json_encode($payload, JSON_THROW_ON_ERROR))
                ->identifier('ilias.privacy.resolveReport')
                ->build();
        }

        return $errors;
    }

    /**
     * @return array{string, array<int|string, string>}
     */
    private function extractTechnicalRequirementInfo(Node\Expr $expr, Scope $scope): array
    {
        if ($expr instanceof Node\Expr\New_) {
            if (!$expr->class instanceof Node\Name) {
                return ['unknown', []];
            }
            $class = $scope->resolveName($expr->class);
            return [
                $this->shortName($class),
                $this->extractArgs($expr->args, $scope, $this->constructorParameterNames($class, $scope)),
            ];
        }

        $requirement_class = $this->requirementClassOf($scope->getType($expr)->getObjectClassNames());

        // requirements built via the TechnicalRequirements factory: the call
        // arguments carry the data
        if ($expr instanceof Node\Expr\MethodCall && $expr->name instanceof Node\Identifier) {
            // caller type unresolvable (e.g. factory obtained from the
            // untyped legacy container): map the factory method to its
            // declared return type
            $requirement_class ??= $this->requirementClassOfFactoryMethod($expr->name->name, $scope);
            if ($requirement_class !== null) {
                return [
                    $this->shortName($requirement_class),
                    $this->extractArgs($expr->args, $scope, $this->factoryParameterNames($expr->name->name, $scope)),
                ];
            }
        }

        // a requirement passed in a variable: the class is known, the
        // arguments are not
        if ($requirement_class !== null) {
            return [$this->shortName($requirement_class), []];
        }

        return ['dynamic', [$scope->getType($expr)->describe(VerbosityLevel::typeOnly())]];
    }

    /**
     * The one concrete requirement class among the given classes; null if
     * there is none or more than one, e.g. for `$a ? $r->displayToUser() :
     * $r->export()`.
     *
     * @param list<string> $class_names
     */
    private function requirementClassOf(array $class_names): ?string
    {
        $requirement_classes = array_values(array_filter(
            $class_names,
            fn(string $class): bool => str_starts_with($class, self::REQUIREMENT_NAMESPACE)
                && $this->reflection_provider->hasClass($class)
                && !$this->reflection_provider->getClass($class)->isInterface()
        ));

        return count($requirement_classes) === 1 ? $requirement_classes[0] : null;
    }

    private function requirementClassOfFactoryMethod(string $method, Scope $scope): ?string
    {
        $factory = \ILIAS\Data\Privacy\TechnicalRequirement\TechnicalRequirements::class;
        if (!$this->reflection_provider->hasClass($factory)) {
            return null;
        }
        $factory_reflection = $this->reflection_provider->getClass($factory);
        if (!$factory_reflection->hasMethod($method)) {
            return null;
        }
        $variants = $factory_reflection->getMethod($method, $scope)->getVariants();
        if (count($variants) !== 1) {
            return null;
        }

        return $this->requirementClassOf($variants[0]->getReturnType()->getObjectClassNames());
    }

    /**
     * @return list<string>
     */
    private function constructorParameterNames(string $class, Scope $scope): array
    {
        if (!$this->reflection_provider->hasClass($class)
            || !$this->reflection_provider->getClass($class)->hasConstructor()
        ) {
            return [];
        }
        $constructor = $this->reflection_provider->getClass($class)->getConstructor();

        return $this->parameterNames($constructor->getVariants());
    }

    /**
     * @return list<string>
     */
    private function factoryParameterNames(string $method, Scope $scope): array
    {
        $factory = \ILIAS\Data\Privacy\TechnicalRequirement\TechnicalRequirements::class;
        if (!$this->reflection_provider->hasClass($factory)
            || !$this->reflection_provider->getClass($factory)->hasMethod($method)
        ) {
            return [];
        }

        return $this->parameterNames($this->reflection_provider->getClass($factory)->getMethod($method, $scope)->getVariants());
    }

    /**
     * @param list<\PHPStan\Reflection\ParametersAcceptor> $variants
     * @return list<string>
     */
    private function parameterNames(array $variants): array
    {
        if (count($variants) !== 1) {
            return [];
        }

        return array_map(
            static fn(\PHPStan\Reflection\ParameterReflection $parameter): string => $parameter->getName(),
            $variants[0]->getParameters()
        );
    }

    /**
     * The arguments keyed by parameter name where it is known, so the
     * generator finds "where", "to" and "in_order_to" also for named arguments.
     *
     * @param array<Node\Arg|Node\VariadicPlaceholder> $raw_args
     * @param list<string> $parameter_names
     * @return array<int|string, string>
     */
    private function extractArgs(array $raw_args, Scope $scope, array $parameter_names = []): array
    {
        $args = [];
        foreach ($raw_args as $position => $arg) {
            if (!$arg instanceof Node\Arg) {
                continue;
            }
            $key = $arg->name?->name ?? $parameter_names[$position] ?? $position;
            $known_source = $this->tryDescribeKnownSourceCall($arg->value, $scope);
            if ($known_source !== null) {
                $args[$key] = $known_source;
            } elseif (count($constant_strings = $scope->getType($arg->value)->getConstantStrings()) === 1) {
                // literals, constants and concatenations of them
                $args[$key] = $constant_strings[0]->getValue();
            } elseif ($arg->value instanceof Node\Expr\New_
                && $arg->value->class instanceof Node\Name
            ) {
                $inner_args = array_map(
                    static fn($a): string => $a instanceof Node\Arg && $a->value instanceof Node\Scalar\String_
                        ? $a->value->value
                        : '?',
                    $arg->value->args
                );
                $args[$key] = $this->shortName((string) $arg->value->class)
                    . '(' . implode(', ', $inner_args) . ')';
            } else {
                // not known statically: the type in braces, e.g. "{string}"
                $args[$key] = '{' . $scope->getType($arg->value)->describe(VerbosityLevel::typeOnly()) . '}';
            }
        }

        return $args;
    }

    /**
     * Records a call to a getter of a source catalogue as
     * "catalogue:Fully\Qualified\Catalogue::getter"; the generator renders
     * the actual table and columns.
     */
    private function tryDescribeKnownSourceCall(Node\Expr $expr, Scope $scope): ?string
    {
        if (!$expr instanceof Node\Expr\MethodCall || !$expr->name instanceof Node\Identifier) {
            return null;
        }

        foreach ($scope->getType($expr->var)->getObjectClassNames() as $class) {
            if ($this->reflection_provider->hasClass($class)
                && $this->reflection_provider->getClass($class)->implementsInterface(
                    \ILIAS\Data\Privacy\Source\SourceCatalogue::class
                )
            ) {
                return self::CATALOGUE_PREFIX . $class . '::' . $expr->name->name;
            }
        }

        return null;
    }

    private function extractLocation(Scope $scope): string
    {
        $function = $scope->getFunction();
        if ($function === null) {
            return '';
        }
        $class = $scope->getClassReflection();

        return ($class !== null ? $this->shortName($class->getName()) . '::' : '')
            . $this->shortName($function->getName()) . '()';
    }

    private function shortName(string $class): string
    {
        return basename(str_replace('\\', '/', $class));
    }

    private function extractComponent(string $file_path): string
    {
        if (preg_match('#components/ILIAS/([^/]+)/#', str_replace('\\', '/', $file_path), $m) === 1) {
            return $m[1];
        }
        return 'Unknown';
    }
}
