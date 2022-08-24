<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class WorkspaceSessionRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
    }

    protected function getSessionRepo(): \ILIAS\PersonalWorkspace\WorkspaceSessionRepository
    {
        $repo = new \ILIAS\PersonalWorkspace\WorkspaceSessionRepository();
        $repo->clearClipboard();
        return $repo;
    }

    /**
     * Test clipboard cmd
     */
    public function testClipboardCmd(): void
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
    public function testSourceIds(): void
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
    public function testShared(): void
    {
        $repo = $this->getSessionRepo();
        $repo->setClipboardShared(true);

        $this->assertEquals(
            true,
            $repo->getClipboardShared()
        );
    }

    public function testClear(): void
    {
        $repo = $this->getSessionRepo();
        $repo->clearClipboard();

        $this->assertEquals(
            true,
            $repo->isClipboardEmpty()
        );
    }

    public function testNotEmpty(): void
    {
        $repo = $this->getSessionRepo();
        $repo->clearClipboard();
        $repo->setClipboardSourceIds([4,6,7]);
        $repo->setClipboardCmd("cut");

        $this->assertEquals(
            false,
            $repo->isClipboardEmpty()
        );
    }
}
