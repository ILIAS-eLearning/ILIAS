<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIItemfeedbackTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIItemfeedback::class, new ilQTIItemfeedback());
    }
}
