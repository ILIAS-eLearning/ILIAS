<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIMatappletTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIMatapplet::class, new ilQTIMatapplet());
    }
}
