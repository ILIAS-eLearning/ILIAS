<?php

use PHPUnit\Framework\TestCase;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class NewsContextTest extends TestCase
{
    protected function tearDown() : void
    {
    }

    /**
     * Test admin view
     */
    public function testContextProperties() : void
    {
        $context = new ilNewsContext(
            1,
            "otype",
            2,
            "osubtype"
        );

        $this->assertEquals(
            1,
            $context->getObjId()
        );
        $this->assertEquals(
            "otype",
            $context->getObjType()
        );
        $this->assertEquals(
            2,
            $context->getSubId()
        );
        $this->assertEquals(
            "osubtype",
            $context->getSubType()
        );
    }
}
