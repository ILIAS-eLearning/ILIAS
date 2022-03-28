<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIDisplayfeedbackTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIDisplayfeedback::class, new ilQTIDisplayfeedback());
    }

    public function testSetGetFeedbacktype() : void
    {
        $instance = new ilQTIDisplayfeedback();
        $instance->setFeedbacktype('Some input.');
        $this->assertEquals('Some input.', $instance->getFeedbacktype());
    }

    public function testSetGetLinkrefid() : void
    {
        $instance = new ilQTIDisplayfeedback();
        $instance->setLinkrefid('Some input.');
        $this->assertEquals('Some input.', $instance->getLinkrefid());
    }

    public function testSetGetContent() : void
    {
        $instance = new ilQTIDisplayfeedback();
        $instance->setContent('Some input.');
        $this->assertEquals('Some input.', $instance->getContent());
    }
}
