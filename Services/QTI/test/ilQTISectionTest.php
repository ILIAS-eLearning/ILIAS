<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTISectionTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTISection::class, new ilQTISection());
    }
}
