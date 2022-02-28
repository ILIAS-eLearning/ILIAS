<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIRenderChoiceTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIRenderChoice::class, new ilQTIRenderChoice());
    }
}
