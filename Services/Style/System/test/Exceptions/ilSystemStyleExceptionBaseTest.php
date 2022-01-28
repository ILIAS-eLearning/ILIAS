<?php

declare(strict_types=1);

require_once('libs/composer/vendor/autoload.php');

use PHPUnit\Framework\TestCase;

abstract class ilSystemStyleExceptionBaseTest extends TestCase
{
    abstract protected function getClassName() : string;

    public function codesProvider() : array
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
    public function testConstruct(int $code) : void
    {
        $class_name = $this->getClassName();
        $this->assertInstanceOf($class_name, new $class_name($code, 'Additional Info'));
    }

    /**
     * @dataProvider codesProvider
     */
    public function testAssignMessageToCode(int $code) : void
    {
        $class_name = $this->getClassName();
        $exception = new $class_name($code, 'Additional Info');
        $this->assertIsString($exception->getMessage());
    }

    /**
     * @dataProvider codesProvider
     */
    public function testToString(int $code) : void
    {
        $class_name = $this->getClassName();
        $exception = new $class_name($code, 'Additional Info');
        $this->assertIsString($exception->__toString());
    }
}
