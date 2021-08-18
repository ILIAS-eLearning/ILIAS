<?php

use PHPUnit\Framework\TestCase;

/**
 * Test tagging
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class TagRelevanceTest extends TestCase
{
    //protected $backupGlobals = false;

    protected function setUp() : void
    {
        parent::setUp();
    }

    protected function tearDown() : void
    {
    }

    /**
     * Test if each rater has $num_assignments peers
     */
    public function testTagRelevance()
    {
        $this->assertEquals(
            "ilTagRelVeryLow",
            ilTagging::getRelevanceClass(1, 10)
        );
        $this->assertEquals(
            "ilTagRelLow",
            ilTagging::getRelevanceClass(3, 10),
        );
        $this->assertEquals(
            "ilTagRelMiddle",
            ilTagging::getRelevanceClass(5, 10),
        );
    }
}
