<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIRenderFibTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIRenderFib::class, new ilQTIRenderFib());
    }
}
