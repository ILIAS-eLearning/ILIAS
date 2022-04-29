<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTISetvarTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTISetvar::class, new ilQTISetvar());
    }

    /**
     * @dataProvider actions
     */
    public function testSetGetAction(string $input, ?string $expected) : void
    {
        $instance = new ilQTISetvar();
        $instance->setAction($input);
        $this->assertEquals($expected, $instance->getAction());
    }

    public function testSetGetContent() : void
    {
        $instance = new ilQTISetvar();
        $instance->setContent('Some input.');
        $this->assertEquals('Some input.', $instance->getContent());
    }

    public function testSetGetVarname() : void
    {
        $instance = new ilQTISetvar();
        $instance->setVarname('Some input.');
        $this->assertEquals('Some input.', $instance->getVarname());
    }

    public function actions() : array
    {
        class_exists(ilQTISetvar::class); // Force autoload to define the constants.
        return [
            ['set', ACTION_SET],
            ['1', ACTION_SET],
            ['add', ACTION_ADD],
            ['2', ACTION_ADD],
            ['subtract', ACTION_SUBTRACT],
            ['3', ACTION_SUBTRACT],
            ['multiply', ACTION_MULTIPLY],
            ['4', ACTION_MULTIPLY],
            ['divide', ACTION_DIVIDE],
            ['5', ACTION_DIVIDE],
            ['6', null],
            ['Some input.', null],
        ];
    }
}
