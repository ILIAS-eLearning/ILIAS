<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIItemTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIItem::class, new ilQTIItem());
    }
}
