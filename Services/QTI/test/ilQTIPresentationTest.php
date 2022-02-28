<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIPresentationTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIPresentation::class, new ilQTIPresentation());
    }
}
