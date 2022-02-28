<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIAssessmentTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIAssessment::class, new ilQTIAssessment());
    }
}
