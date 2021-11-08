<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ClipboardSessionRepositoryTest extends TestCase
{
    //protected $backupGlobals = false;
    protected \ILIAS\Repository\Clipboard\ClipboardSessionRepository $clipboard;

    protected function setUp() : void
    {
        parent::setUp();
        $this->clipboard = new \ILIAS\Repository\Clipboard\ClipboardSessionRepository();
        $this->clipboard->clear();
    }

    protected function tearDown() : void
    {
    }

    /**
     * Test clear
     */
    public function testClear()
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
    public function testCmd()
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
    public function testRefIds()
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
    public function testParent()
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
    public function testHasEntriesNoCmd()
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
    public function testHasEntriesCmd()
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
    public function testHasEntriesCmdEmptyRefIds()
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
