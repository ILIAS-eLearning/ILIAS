<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIOutcomesTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIOutcomes::class, new ilQTIOutcomes());
    }
}
