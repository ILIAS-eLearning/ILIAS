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

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ClipboardSessionRepositoryTest extends TestCase
{
    protected \ILIAS\Repository\Clipboard\ClipboardSessionRepository $clipboard;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clipboard = new \ILIAS\Repository\Clipboard\ClipboardSessionRepository();
        $this->clipboard->clear();
    }

    protected function tearDown(): void
    {
    }

    /**
     * Test clear
     */
    public function testClear(): void
    {
        $clipboard = $this->clipboard;
        $clipboard->setCmd("test");
        $clipboard->setRefIds([4]);
        $clipboard->setParent(5);
        $clipboard->clear();
        $this->assertEquals(
            "",
            $clipboard->getCmd()
        );
        $this->assertEquals(
            [],
            $clipboard->getRefIds()
        );
        $this->assertEquals(
            0,
            $clipboard->getParent()
        );
    }

    /**
     * Test cmd set/get
     */
    public function testCmd(): void
    {
        $clipboard = $this->clipboard;
        $clipboard->setCmd("test");
        $this->assertEquals(
            "test",
            $clipboard->getCmd()
        );
    }

    /**
     * Test ref ids set/get
     */
    public function testRefIds(): void
    {
        $clipboard = $this->clipboard;
        $clipboard->setRefIds([4]);
        $this->assertEquals(
            [4],
            $clipboard->getRefIds()
        );
    }

    /**
     * Test parent set/get
     */
    public function testParent(): void
    {
        $clipboard = $this->clipboard;
        $clipboard->setParent(5);
        $this->assertEquals(
            5,
            $clipboard->getParent()
        );
    }

    /**
     * Test hasEntries returns false if ref ids, but no cmd is given
     */
    public function testHasEntriesNoCmd(): void
    {
        $clipboard = $this->clipboard;
        $clipboard->setRefIds([4]);
        $this->assertEquals(
            false,
            $clipboard->hasEntries()
        );
    }

    /**
     * Test hasEntries returns true if ref ids and cmd is given
     */
    public function testHasEntriesCmd(): void
    {
        $clipboard = $this->clipboard;
        $clipboard->setRefIds([4]);
        $clipboard->setCmd("cut");
        $this->assertEquals(
            true,
            $clipboard->hasEntries()
        );
    }

    /**
     * Test hasEntries returns false if empty ref ids array and cmd is given
     */
    public function testHasEntriesCmdEmptyRefIds(): void
    {
        $clipboard = $this->clipboard;
        $clipboard->setRefIds([]);
        $clipboard->setCmd("cut");
        $this->assertEquals(
            false,
            $clipboard->hasEntries()
        );
    }
}
