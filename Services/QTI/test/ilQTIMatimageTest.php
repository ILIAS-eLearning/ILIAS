<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIMatimageTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIMatimage::class, new ilQTIMatimage());
    }
}
