<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class PCMapEditorSessionRepositoryTest extends TestCase
{
    //protected $backupGlobals = false;
    protected \ILIAS\COPage\PC\MapEditorSessionRepository $repo;

    protected function setUp() : void
    {
        parent::setUp();
        $this->repo = new \ILIAS\COPage\PC\MapEditorSessionRepository();
    }

    protected function tearDown() : void
    {
    }

    /**
     * Test mode
     */
    public function testMode() : void
    {
        $repo = $this->repo;
        $repo->setMode("testmode");
        $this->assertEquals(
            "testmode",
            $repo->getMode()
        );
    }

    /**
     * Test area nr
     */
    public function testAreaNr() : void
    {
        $repo = $this->repo;
        $repo->setAreaNr("3");
        $this->assertEquals(
            "3",
            $repo->getAreaNr()
        );
    }
}
