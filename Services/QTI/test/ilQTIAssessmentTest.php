<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIAssessmentTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIAssessment::class, new ilQTIAssessment());
    }

    public function testSetGetIdent() : void
    {
        $instance = new ilQTIAssessment();
        $instance->setIdent('Some input.');
        $this->assertEquals('Some input.', $instance->getIdent());
    }

    public function testSetGetTitle() : void
    {
        $instance = new ilQTIAssessment();
        $instance->setTitle('Some input.');
        $this->assertEquals('Some input.', $instance->getTitle());
    }

    public function testSetGetXmllang() : void
    {
        $instance = new ilQTIAssessment();
        $instance->setXmllang('Some input.');
        $this->assertEquals('Some input.', $instance->getXmllang());
    }

    public function testSetGetComment() : void
    {
        $instance = new ilQTIAssessment();
        $instance->setComment('Some input.');
        $this->assertEquals('Some input.', $instance->getComment());
    }
}
