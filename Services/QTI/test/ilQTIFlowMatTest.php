<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIFlowMatTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIFlowMat::class, new ilQTIFlowMat());
    }
}
