<?php declare(strict_types=1);

require_once("libs/composer/vendor/autoload.php");

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
        $this->assertInstanceOf($this->getClassName(),new ($this->getClassName())($code, "Additional Info"));
    }

    /**
     * @dataProvider codesProvider
     */
    public function testAssignMessageToCode(int $code) : void
    {
        $exception = new ($this->getClassName())($code, "Additional Info");
        $this->assertIsString($exception->getMessage());
    }

    /**
     * @dataProvider codesProvider
     */
    public function testToString(int $code) : void
    {
        $exception = new ($this->getClassName())($code, "Additional Info");
        $this->assertIsString($exception->__toString());
    }
}
