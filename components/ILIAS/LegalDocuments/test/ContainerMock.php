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

use ReflectionClass;

trait ContainerMock
{
    private function mock(string $class)
    {
        return $this->getMockBuilder($class)->disableOriginalConstructor()->getMock();
    }

    private function mockMethod(string $class, string $method, array $args, $return, $times = null)
    {
        $times ??= self::once();
        $mock = $this->mock($class);
        $mock->expects($times)->method($method)->with(...$args)->willReturn($return);

        return $mock;
    }

    /**
     * @param class-name|object $class_or_instance
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
     * @param array<string, mixed> $tree
     */
    private function mockTree(string $class, array $tree)
    {
        $r = new ReflectionClass($class);
        $mock = $this->mock($class);
        foreach ($tree as $name => $value) {
            $type = (string) $r->getMethod($name)->getReturnType();
            $type = $type === 'self' ? $class : $type;
            $mock->method($name)->willReturn(
                class_exists($type) || interface_exists($type) ?
                (is_array($value) ? $this->mockTree($type, $value) : $value) :
                $value
            );
        }

        return $mock;
    }
}
