<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIDecvarTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIDecvar::class, new ilQTIDecvar());
    }
}
