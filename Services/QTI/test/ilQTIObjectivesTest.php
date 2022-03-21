<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIObjectivesTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIObjectives::class, new ilQTIObjectives());
    }
}
