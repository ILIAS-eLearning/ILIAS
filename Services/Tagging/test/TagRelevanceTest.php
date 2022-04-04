<?php declare(strict_types=1);

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
    public function testTagRelevance() : void
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
