<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTISetvarTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTISetvar::class, new ilQTISetvar());
    }
}
