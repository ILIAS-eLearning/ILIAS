<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIMaterialTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIMaterial::class, new ilQTIMaterial());
    }
}
