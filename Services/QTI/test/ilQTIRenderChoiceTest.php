<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIRenderChoiceTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIRenderChoice::class, new ilQTIRenderChoice());
    }

    public function testSetGetMinnumber() : void
    {
        $instance = new ilQTIRenderChoice();
        $instance->setMinnumber('Some input.');
        $this->assertEquals('Some input.', $instance->getMinnumber());
    }

    public function testSetGetMaxnumber() : void
    {
        $instance = new ilQTIRenderChoice();
        $instance->setMaxnumber('Some input.');
        $this->assertEquals('Some input.', $instance->getMaxnumber());
    }
}
