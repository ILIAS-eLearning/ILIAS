<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIMattextTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIMattext::class, new ilQTIMattext());
    }
}
