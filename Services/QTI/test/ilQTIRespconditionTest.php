<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIRespconditionTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIRespcondition::class, new ilQTIRespcondition());
    }
}
