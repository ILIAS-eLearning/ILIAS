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

namespace ILIAS\Component\Dependencies\Mocks;

use LogicException;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;

/**
 * @internal This class can only be used in Bootstrap
 */
abstract class AbstractLightMockBuilder implements MockBuilder
{
    /**
     * Generated classes are declared in the global process scope, hence the
     * bookkeeping about them has to be process-wide as well: declaring the
     * same class twice is a fatal error, no matter which builder instance
     * triggers it.
     *
     * @var array<class-string, array<string, mixed>>
     */
    private static array $return_type_map = [];

    /** @var array<class-string, true> */
    private static array $generated = [];

    /** @var array<class-string, self> */
    private static array $builders = [];

    private function createLazyShell(string $type): object
    {
        if (!class_exists($type)) {
            throw new LogicException(
                "Lazy-shell mocks only support concrete or abstract classes, not interfaces/unknown types: {$type}"
            );
        }

        $ref = new ReflectionClass($type);

        if ($ref->isInterface()) {
            throw new LogicException("Lazy-shell mocks do not support interfaces: {$type}");
        }

        if ($ref->isTrait()) {
            throw new LogicException("Traits cannot be mocked directly: {$type}");
        }

        if ($ref->isEnum()) {
            throw new LogicException("Enums cannot be mocked: {$type}");
        }

        if ($ref->isFinal()) {
            throw new LogicException("Final classes cannot be mocked without extensions/code rewriting: {$type}");
        }

        if ($ref->isAbstract()) {
            throw new LogicException("Abstract classes cannot be instantiated as a lazy shell: {$type}");
        }

        return $ref->newLazyGhost(
            static function (object $object): void {
                // Intentionally left blank.
                // This variant is only meant to provide a typed shell object.
            },
            ReflectionClass::SKIP_INITIALIZATION_ON_SERIALIZE
        );
    }

    public function create(string $fqdn): object
    {
        if (interface_exists($fqdn)) {
            return $this->createNormal($fqdn);
        }

        try {
            return $this->createLazyShell($fqdn);
        } catch (LogicException | \ReflectionException) {
            return $this->createNormal($fqdn);
        }
    }

    private function createNormal(string $type): object
    {
        $generated_class = $this->generatedClassName($type);
        $generated_fqcn = __NAMESPACE__ . '\\' . $generated_class;

        if (!isset(self::$generated[$generated_fqcn])) {
            $this->generate($type, $generated_class);
            self::$generated[$generated_fqcn] = true;
            self::$builders[$generated_fqcn] = $this;
        }

        return new $generated_fqcn();
    }

    private function generate(string $type, string $generated_class): void
    {
        if (!class_exists($type) && !interface_exists($type)) {
            throw new LogicException("Unknown class or interface: {$type}");
        }

        $ref = new ReflectionClass($type);

        if ($ref->isEnum()) {
            throw new LogicException("Enums cannot be mocked: {$type}");
        }

        if ($ref->isTrait()) {
            throw new LogicException("Traits cannot be mocked directly: {$type}");
        }

        if ($ref->isFinal()) {
            throw new LogicException("Final classes cannot be mocked without extensions/code rewriting: {$type}");
        }

        $method_code = [];
        $return_map = [];

        if ($ref->isInterface()) {
            foreach ($ref->getMethods() as $method) {
                if ($method->returnsReference()) {
                    throw new LogicException(
                        "Methods returning by reference are not supported by this lightweight mock builder: "
                        . $ref->getName() . "::" . $method->getName()
                    );
                }

                $method_code[] = $this->buildMethod($method, $return_map);
            }

            $header = "class {$generated_class} implements \\" . ltrim($type, '\\');
        } else {
            // only add an empty constructor if the parent constructor is not final
            $constructor = $ref->getConstructor();
            if ($constructor === null || !$constructor->isFinal()) {
                $method_code[] = "public function __construct(...\$__args) {}";
            }

            foreach ($ref->getMethods() as $method) {
                if (!$this->shouldOverride($method)) {
                    continue;
                }

                if ($method->returnsReference()) {
                    throw new LogicException(
                        "Methods returning by reference are not supported by this lightweight mock builder: "
                        . $ref->getName() . "::" . $method->getName()
                    );
                }

                $method_code[] = $this->buildMethod($method, $return_map);
            }

            $header = "class {$generated_class} extends \\" . ltrim($type, '\\');
        }

        self::$return_type_map[__NAMESPACE__ . '\\' . $generated_class] = $return_map;

        $code = <<<PHP
namespace ILIAS\Component\Dependencies\Mocks;

{$header}
{
    use MockObjectBehavior;

    %s
}
PHP;

        $code = sprintf($code, implode("\n\n", $method_code));

        $this->loadGeneratedCode($code, $generated_class);
    }

    abstract protected function loadGeneratedCode(string $code, string $generated_class): void;

    private function shouldOverride(ReflectionMethod $method): bool
    {
        if ($method->isConstructor()) {
            return false;
        }

        if ($method->isDestructor()) {
            return false;
        }

        if ($method->isFinal()) {
            return false;
        }

        if ($method->isPrivate()) {
            return false;
        }

        if ($method->isStatic()) {
            return false;
        }
        // only methods that can visibly be overridden in the target class or its parents
        if ($method->isPublic()) {
            return true;
        }
        if ($method->isProtected()) {
            return true;
        }
        return $method->isAbstract();
    }

    /**
     * @param array<string, mixed> $return_map
     */
    private function buildMethod(ReflectionMethod $method, array &$return_map): string
    {
        $visibility = $method->isPublic() ? 'public' : 'protected';
        $name = $method->getName();
        $params = $this->buildParameterList($method);
        $return_type = $this->renderType($method->getReturnType());

        $return_map[$name] = $this->normalizeType($method->getReturnType());

        $body = $this->buildMethodBody($name, $method->getReturnType());
        $attribute = $this->buildMethodAttributes($method);

        return trim(
            $attribute .
            sprintf(
                '%s function %s(%s)%s %s',
                $visibility,
                $name,
                $params,
                $return_type !== '' ? ': ' . $return_type : '',
                $body
            )
        );
    }

    private function buildMethodAttributes(ReflectionMethod $method): string
    {
        if ($this->needsReturnTypeWillChange($method)) {
            return "#[\\ReturnTypeWillChange]\n";
        }

        return '';
    }

    private function needsReturnTypeWillChange(ReflectionMethod $method): bool
    {
        if ($method->hasReturnType()) {
            return false;
        }

        if (method_exists($method, 'hasTentativeReturnType') && $method->hasTentativeReturnType()) {
            return true;
        }

        try {
            $prototype = $method->getPrototype();

            if ($prototype->hasReturnType()) {
                return false;
            }

            if (method_exists($prototype, 'hasTentativeReturnType') && $prototype->hasTentativeReturnType()) {
                return true;
            }
        } catch (\ReflectionException) {
            // No prototype available.
        }

        return false;
    }

    private function buildMethodBody(string $method_name, ?ReflectionType $return_type): string
    {
        $normalized = $this->normalizeType($return_type);

        if ($normalized['kind'] === 'never') {
            return <<<PHP
{
    throw new \\LogicException('Mocked never-returning method called: {$method_name}');
}
PHP;
        }

        if ($normalized['kind'] === 'void') {
            return <<<PHP
{
    \$this->__mockInvoke('{$method_name}', func_get_args());
    return;
}
PHP;
        }

        return <<<PHP
{
    return \$this->__mockInvoke('{$method_name}', func_get_args());
}
PHP;
    }

    private function buildParameterList(ReflectionMethod $method): string
    {
        $parts = [];

        foreach ($method->getParameters() as $parameter) {
            $parts[] = $this->buildParameter($parameter);
        }

        return implode(', ', $parts);
    }

    private function buildParameter(ReflectionParameter $parameter): string
    {
        $code = '';

        $type = $this->renderType($parameter->getType());
        if ($type !== '') {
            $code .= $type . ' ';
        }

        if ($parameter->isPassedByReference()) {
            $code .= '&';
        }

        if ($parameter->isVariadic()) {
            $code .= '...';
        }

        $code .= '$' . $parameter->getName();

        if (!$parameter->isVariadic() && $parameter->isDefaultValueAvailable()) {
            if ($parameter->isDefaultValueConstant()) {
                $code .= ' = ' . $parameter->getDefaultValueConstantName();
            } else {
                $code .= ' = ' . $this->exportValue($parameter->getDefaultValue());
            }
        }

        return $code;
    }

    private function renderType(?ReflectionType $type): string
    {
        if ($type === null) {
            return '';
        }

        if ($type instanceof ReflectionNamedType) {
            $name = $type->getName();
            $prefix = !$type->isBuiltin() && $name !== 'self' && $name !== 'parent' && $name !== 'static'
                ? '\\'
                : '';

            if ($type->allowsNull() && $name !== 'mixed' && $name !== 'null') {
                return '?' . $prefix . $name;
            }

            return $prefix . $name;
        }

        if ($type instanceof ReflectionUnionType) {
            $parts = [];
            foreach ($type->getTypes() as $sub_type) {
                $parts[] = $this->renderType($sub_type);
            }
            return implode('|', $parts);
        }

        if ($type instanceof ReflectionIntersectionType) {
            $parts = [];
            foreach ($type->getTypes() as $sub_type) {
                $parts[] = $this->renderType($sub_type);
            }
            return implode('&', $parts);
        }

        throw new LogicException('Unsupported reflection type: ' . $type::class);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeType(?ReflectionType $type): array
    {
        if ($type === null) {
            return ['kind' => 'none'];
        }

        if ($type instanceof ReflectionNamedType) {
            $name = $type->getName();

            if ($name === 'void') {
                return ['kind' => 'void'];
            }

            if ($name === 'never') {
                return ['kind' => 'never'];
            }

            return [
                'kind' => 'named',
                'name' => $name,
                'builtin' => $type->isBuiltin(),
                'nullable' => $type->allowsNull(),
            ];
        }

        if ($type instanceof ReflectionUnionType) {
            return [
                'kind' => 'union',
                'types' => array_map(
                    $this->normalizeType(...),
                    $type->getTypes()
                ),
            ];
        }

        if ($type instanceof ReflectionIntersectionType) {
            return [
                'kind' => 'intersection',
                'types' => array_map(
                    $this->normalizeType(...),
                    $type->getTypes()
                ),
            ];
        }

        throw new LogicException('Unsupported reflection type: ' . $type::class);
    }

    /**
     * Entry point for the generated mocks themselves, see {@see MockObjectBehavior}.
     * The builder that generated the class resolves the default, so that nested
     * mocks are built the same way the outer one was.
     */
    public static function defaultValueFor(object $object, string $method): mixed
    {
        $class = $object::class;
        $meta = self::$return_type_map[$class][$method] ?? null;

        if ($meta === null) {
            // not a generated mock, or a method this builder never saw
            return null;
        }

        $builder = self::$builders[$class] ?? null;

        if ($builder === null) {
            throw new LogicException("No mock builder registered for generated class {$class}");
        }

        return $builder->defaultByMeta($object, $meta);
    }

    /**
     * @param array<string, mixed> $meta
     */
    private function defaultByMeta(object $object, array $meta): mixed
    {
        $kind = $meta['kind'] ?? 'none';

        if ($kind === 'none') {
            return null;
        }

        if ($kind === 'void') {
            return null;
        }

        if ($kind === 'never') {
            throw new LogicException('Cannot produce a default value for return type never');
        }

        if ($kind === 'intersection') {
            throw new LogicException('Intersection return types need explicit stubbing');
        }

        if ($kind === 'union') {
            foreach ($meta['types'] as $sub_type) {
                if (($sub_type['kind'] ?? null) === 'named' && ($sub_type['name'] ?? null) === 'null') {
                    return null;
                }
            }

            foreach ($meta['types'] as $sub_type) {
                if (($sub_type['kind'] ?? null) === 'named' && ($sub_type['name'] ?? null) !== 'null') {
                    return $this->defaultByMeta($object, $sub_type);
                }
            }

            return null;
        }

        $name = $meta['name'] ?? null;
        $nullable = (bool) ($meta['nullable'] ?? false);

        if ($nullable) {
            return null;
        }

        return match ($name) {
            'void' => null,
            'never' => throw new LogicException('Cannot return from a never-returning method'),
            'null' => null,
            'mixed' => null,
            'bool', 'false' => false,
            'true' => true,
            'int' => 0,
            'float' => 0.0,
            'string' => '',
            'array' => [],
            'iterable' => [],
            'callable' => static fn(): null => null,
            'object' => new \stdClass(),
            'self', 'static' => $object,
            'parent' => $this->createNormal((string) get_parent_class($object)),
            default => $this->createNormal($name),
        };
    }

    private function generatedClassName(string $type): string
    {
        return 'Mock_' . md5($type);
    }

    private function exportValue(mixed $value): string
    {
        return var_export($value, true);
    }
}
