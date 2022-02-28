<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIResponseVarTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIResponseVar::class, new ilQTIResponseVar('a'));
    }
}
