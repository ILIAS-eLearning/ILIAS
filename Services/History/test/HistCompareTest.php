<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class HistCompareTest extends TestCase
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
     * Test compare
     */
    public function testCompare() : void
    {
        $this->assertEquals(
            -1,
            ilHistory::_compareHistArray(
                ["date" => "2021-12-01"],
                ["date" => "2021-12-03"],
            )
        );
    }

    /**
     * Test compare 2
     */
    public function testCompare2() : void
    {
        $this->assertEquals(
            1,
            ilHistory::_compareHistArray(
                ["date" => "2021-12-01"],
                ["date" => "2021-11-03"],
            )
        );
    }

    /**
     * Test compare 3
     */
    public function testCompare3() : void
    {
        $this->assertEquals(
            0,
            ilHistory::_compareHistArray(
                ["date" => "2021-12-03"],
                ["date" => "2021-12-03"],
            )
        );
    }
}
