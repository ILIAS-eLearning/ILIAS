<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIRespconditionTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIRespcondition::class, new ilQTIRespcondition());
    }

    /**
     * @dataProvider continues
     */
    public function testSetGetContinue($input, $expected) : void
    {
        $instance = new ilQTIRespcondition();
        $instance->setContinue($input);
        $this->assertEquals($expected, $instance->getContinue());
    }

    public function testSetGetTitle() : void
    {
        $instance = new ilQTIRespcondition();
        $instance->setTitle('Some input.');
        $this->assertEquals('Some input.', $instance->getTitle());
    }

    public function testSetGetComment() : void
    {
        $instance = new ilQTIRespcondition();
        $instance->setComment('Some input.');
        $this->assertEquals('Some input.', $instance->getComment());
    }

    public function continues() : array
    {
        class_exists(ilQTIRespcondition::class); // Force autoload to define the constants.
        return [
            ['1', CONTINUE_YES],
            ['yes', CONTINUE_YES],
            ['2', CONTINUE_NO],
            ['no', CONTINUE_NO],
            ['Random input', null],
        ];
    }
}
