<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class WorkspaceSessionRepositoryTest extends TestCase
{
    protected function setUp() : void
    {
        parent::setUp();
    }

    protected function tearDown() : void
    {
    }

    protected function getSessionRepo() : \ILIAS\PersonalWorkspace\WorkspaceSessionRepository
    {
        $repo = new \ILIAS\PersonalWorkspace\WorkspaceSessionRepository();
        $repo->clearClipboard();
        return $repo;
    }

    /**
     * Test clipboard cmd
     */
    public function testClipboardCmd()
    {
        $repo = $this->getSessionRepo();
        $repo->setClipboardCmd("cut");

        $this->assertEquals(
            "cut",
            $repo->getClipboardCmd()
        );
    }

    /**
     * Test source ids
     */
    public function testSourceIds()
    {
        $repo = $this->getSessionRepo();
        $repo->setClipboardSourceIds([4,6,7]);

        $this->assertEquals(
            [4,6,7],
            $repo->getClipboardSourceIds()
        );
    }

    /**
     * Test source ids
     */
    public function testShared()
    {
        $repo = $this->getSessionRepo();
        $repo->setClipboardShared(true);

        $this->assertEquals(
            true,
            $repo->getClipboardShared()
        );
    }
}
