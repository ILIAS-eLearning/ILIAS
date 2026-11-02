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

namespace ILIAS\LegalDocuments\test;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use ReflectionClass;

trait ContainerMock
{
    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T&MockObject
     */
    private function mock(string $class)
    {
        return $this->getMockBuilder($class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T&Stub
     */
    private function stub(string $class)
    {
        return $this->createStub($class);
    }

    private function mockMethod(string $class, string $method, array $args, $return, $times = null)
    {
        $times ??= $this->once();
        $mock = $this->mock($class);
        $mock->expects($times)->method($method)->with(...$args)->willReturn($return);

        return $mock;
    }

    /**
     * @param class-string|object $class_or_instance
     * @param array<string, mixed> $methods_and_values
     */
    private function assertGetter($class_or_instance, array $methods_and_values): void
    {
        $instance = is_string($class_or_instance) ? new $class_or_instance(...array_values($methods_and_values)) : $class_or_instance;
        foreach ($methods_and_values as $method => $value) {
            $this->assertSame($value, $instance->$method($value));
        }
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @param array<string, mixed> $tree
     * @return T&MockObject
     */
    private function mockTree(string $class, array $tree)
    {
        $double = $this->mock($class);
        $this->configureTree($double, $class, $tree);

        return $double;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @param array<string, mixed> $tree
     * @return T&Stub
     */
    private function stubTree(string $class, array $tree)
    {
        $double = $this->stub($class);
        $this->configureTree($double, $class, $tree);

        return $double;
    }

    /**
     * @param class-string $class
     * @param array<string, mixed> $tree
     */
    private function configureTree(Stub $double, string $class, array $tree): void
    {
        $reflection = new ReflectionClass($class);
        foreach ($tree as $name => $value) {
            $type = (string) $reflection->getMethod($name)->getReturnType();
            $type = $type === 'self' ? $class : $type;
            $type = preg_replace('/^\?/', '', $type);
            $double->method($name)->willReturn(
                class_exists($type) || interface_exists($type) ?
                (is_array($value) ? $this->stubTree($type, $value) : $value) :
                $value
            );
        }
    }
}
