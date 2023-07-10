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

require_once('libs/composer/vendor/autoload.php');

use PHPUnit\Framework\TestCase;

abstract class ilSystemStyleExceptionBaseTest extends TestCase
{
    abstract protected function getClassName(): string;

    public function codesProvider(): array
    {
        $reflection = new ReflectionClass($this->getClassName());

        $constant_values = array_values($reflection->getConstants());
        return array_map(function ($val) {
            return [$val];
        }, $constant_values);
    }

    /**
     * @dataProvider codesProvider
     */
    public function testConstruct(int $code): void
    {
        $class_name = $this->getClassName();
        $this->assertInstanceOf($class_name, new $class_name($code, 'Additional Info'));
    }

    /**
     * @dataProvider codesProvider
     */
    public function testAssignMessageToCode(int $code): void
    {
        $class_name = $this->getClassName();
        $exception = new $class_name($code, 'Additional Info');
        $this->assertIsString($exception->getMessage());
    }

    /**
     * @dataProvider codesProvider
     */
    public function testToString(int $code): void
    {
        $class_name = $this->getClassName();
        $exception = new $class_name($code, 'Additional Info');
        $this->assertIsString($exception->__toString());
    }
}
