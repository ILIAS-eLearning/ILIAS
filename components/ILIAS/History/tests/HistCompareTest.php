<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class HistCompareTest extends TestCase
{
    //protected $backupGlobals = false;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
    }

    /**
     * Test compare
     */
    public function testCompare(): void
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
    public function testCompare2(): void
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
    public function testCompare3(): void
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
