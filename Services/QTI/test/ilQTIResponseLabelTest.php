<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIResponseLabelTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIResponseLabel::class, new ilQTIResponseLabel());
    }
}
