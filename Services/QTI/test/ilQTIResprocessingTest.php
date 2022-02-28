<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIResprocessingTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIResprocessing::class, new ilQTIResprocessing());
    }
}
