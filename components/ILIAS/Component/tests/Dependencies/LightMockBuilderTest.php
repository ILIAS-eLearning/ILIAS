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

namespace ILIAS\Component\Tests\Dependencies;

use PHPUnit\Framework\TestCase;
use LogicException;
use ILIAS\Component\Dependencies\Mocks\GeneratedMockRegistry;
use ILIAS\Component\Dependencies\Mocks\EvalLightMockBuilder;
use ILIAS\Component\Dependencies\Mocks\MockBuilder;
use ILIAS\Component\Dependencies\Mocks\PHPUnitMockBuilder;

// ---------------------------------------------------------------------------
// Test fixtures – prefixed "Lmb" to avoid collisions with other test files
// in the same namespace.
// ---------------------------------------------------------------------------

interface LmbEmptyInterface
{
}

interface LmbReturnTypesInterface
{
    public function returnsVoid(): void;
    public function returnsBool(): bool;
    public function returnsInt(): int;
    public function returnsFloat(): float;
    public function returnsString(): string;
    public function returnsArray(): array;
    public function returnsNullableString(): ?string;
    public function returnsObject(): object;
    public function returnsSelf(): static;
    public function returnsCallable(): callable;
    public function returnsInterface(): LmbReturnTypesInterface;
    public function takesArgs(string $a, int $b): string;
}

interface LmbNeverInterface
{
    public function neverReturns(): never;
}

abstract class LmbAbstractBase
{
    abstract public function abstractMethod(): string;
}

class LmbConcreteClass
{
    public function getValue(): int
    {
        return 1;
    }
}

final class LmbFinalClass
{
}

enum LmbTestEnum
{
    case A;
}

final class LightMockBuilderTest extends TestCase
{
    private MockBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new EvalLightMockBuilder();
    }

    // --- create(): type resolution ---

    public function testCreateInterfaceReturnsInstanceOfInterface(): void
    {
        $mock = $this->builder->create(LmbEmptyInterface::class);
        $this->assertInstanceOf(LmbEmptyInterface::class, $mock);
    }

    public function testCreateAbstractClassReturnsInstance(): void
    {
        $mock = $this->builder->create(LmbAbstractBase::class);
        $this->assertInstanceOf(LmbAbstractBase::class, $mock);
    }

    public function testCreateConcreteClassReturnsInstance(): void
    {
        $mock = $this->builder->create(LmbConcreteClass::class);
        $this->assertInstanceOf(LmbConcreteClass::class, $mock);
    }

    public function testCreateFinalClassThrowsLogicException(): void
    {
        $this->expectException(LogicException::class);
        $this->builder->create(LmbFinalClass::class);
    }

    public function testCreateEnumThrowsLogicException(): void
    {
        $this->expectException(LogicException::class);
        $this->builder->create(LmbTestEnum::class);
    }

    public function testCreateUnknownTypeThrowsLogicException(): void
    {
        $this->expectException(LogicException::class);
        $this->builder->create('\\Non\\Existent\\TypeXyz');
    }

    // --- Default return values ---

    public function testDefaultVoidMethodReturnsNull(): void
    {
        $mock = $this->builder->create(LmbReturnTypesInterface::class);
        $this->assertNull($mock->returnsVoid());
    }

    public function testDefaultBoolReturnsFalse(): void
    {
        $mock = $this->builder->create(LmbReturnTypesInterface::class);
        $this->assertFalse($mock->returnsBool());
    }

    public function testDefaultIntReturnsZero(): void
    {
        $mock = $this->builder->create(LmbReturnTypesInterface::class);
        $this->assertSame(0, $mock->returnsInt());
    }

    public function testDefaultFloatReturnsZeroPointZero(): void
    {
        $mock = $this->builder->create(LmbReturnTypesInterface::class);
        $this->assertEqualsWithDelta(0.0, $mock->returnsFloat(), PHP_FLOAT_EPSILON);
    }

    public function testDefaultStringReturnsEmptyString(): void
    {
        $mock = $this->builder->create(LmbReturnTypesInterface::class);
        $this->assertSame('', $mock->returnsString());
    }

    public function testDefaultArrayReturnsEmptyArray(): void
    {
        $mock = $this->builder->create(LmbReturnTypesInterface::class);
        $this->assertSame([], $mock->returnsArray());
    }

    public function testDefaultNullableStringReturnsNull(): void
    {
        $mock = $this->builder->create(LmbReturnTypesInterface::class);
        $this->assertNull($mock->returnsNullableString());
    }

    public function testDefaultObjectReturnsStdClassInstance(): void
    {
        $mock = $this->builder->create(LmbReturnTypesInterface::class);
        $this->assertInstanceOf(\stdClass::class, $mock->returnsObject());
    }

    public function testDefaultSelfReturnsSameObject(): void
    {
        $mock = $this->builder->create(LmbReturnTypesInterface::class);
        $this->assertSame($mock, $mock->returnsSelf());
    }

    public function testDefaultCallableReturnsCallable(): void
    {
        $mock = $this->builder->create(LmbReturnTypesInterface::class);
        $this->assertIsCallable($mock->returnsCallable());
    }

    public function testDefaultInterfaceReturnsMockImplementingThatInterface(): void
    {
        $mock = $this->builder->create(LmbReturnTypesInterface::class);
        $this->assertInstanceOf(LmbReturnTypesInterface::class, $mock->returnsInterface());
    }

    public function testNeverReturnMethodThrowsLogicException(): void
    {
        $mock = $this->builder->create(LmbNeverInterface::class);
        $this->expectException(LogicException::class);
        $mock->neverReturns();
    }

    // --- Stub configuration ---

    public function testSetReturnValueOverridesDefault(): void
    {
        $mock = $this->builder->create(LmbReturnTypesInterface::class);
        $mock->__mockSetReturnValue('returnsInt', 42);
        $this->assertSame(42, $mock->returnsInt());
    }

    public function testSetReturnValueFluentReturnsInstance(): void
    {
        $mock = $this->builder->create(LmbReturnTypesInterface::class);
        $this->assertSame($mock, $mock->__mockSetReturnValue('returnsInt', 1));
    }

    public function testSetCallbackOverridesDefault(): void
    {
        $mock = $this->builder->create(LmbReturnTypesInterface::class);
        $mock->__mockSetCallback('returnsString', fn(): string => 'hello');
        $this->assertSame('hello', $mock->returnsString());
    }

    public function testCallbackReceivesArguments(): void
    {
        $mock = $this->builder->create(LmbReturnTypesInterface::class);
        $mock->__mockSetCallback('takesArgs', fn(string $a, int $b): string => $a . $b);
        $this->assertSame('test7', $mock->takesArgs('test', 7));
    }

    public function testCallbackTakesPrecedenceOverConfiguredReturnValue(): void
    {
        $mock = $this->builder->create(LmbReturnTypesInterface::class);
        $mock->__mockSetReturnValue('returnsString', 'from-value');
        $mock->__mockSetCallback('returnsString', fn(): string => 'from-callback');
        $this->assertSame('from-callback', $mock->returnsString());
    }

    // --- Generated-class caching ---

    public function testSameTypeAlwaysProducesSameGeneratedClass(): void
    {
        $first = $this->builder->create(LmbEmptyInterface::class);
        $second = $this->builder->create(LmbEmptyInterface::class);
        $this->assertInstanceOf($first::class, $second);
    }

    public function testDifferentTypesProduceDifferentClasses(): void
    {
        $first = $this->builder->create(LmbEmptyInterface::class);
        $second = $this->builder->create(LmbReturnTypesInterface::class);
        $this->assertNotInstanceOf($first::class, $second);
    }

    // --- PHPUnitMockBuilder: keeps the alternative implementation compiling ---

    public function testPHPUnitMockBuilderCreatesInterfaceMock(): void
    {
        $mock = (new PHPUnitMockBuilder())->create(LmbReturnTypesInterface::class);

        $this->assertInstanceOf(LmbReturnTypesInterface::class, $mock);
        $this->assertSame(0, $mock->returnsInt());
    }

    // --- defaultValueFor boundary behaviour ---

    public function testDefaultValueForUnknownMethodReturnsNull(): void
    {
        $result = GeneratedMockRegistry::defaultValueFor(new \stdClass(), 'nonExistentMethod');
        $this->assertNull($result);
    }

    public function testDefaultValueForKnownMethodMatchesDirectInvocation(): void
    {
        $mock = $this->builder->create(LmbReturnTypesInterface::class);
        $this->assertSame(
            $mock->returnsInt(),
            GeneratedMockRegistry::defaultValueFor($mock, 'returnsInt')
        );
    }
}
