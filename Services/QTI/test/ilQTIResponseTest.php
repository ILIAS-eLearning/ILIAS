<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIResponseTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIResponse::class, new ilQTIResponse());
    }
}
