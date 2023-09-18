<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class BlockSessionRepositoryTest extends TestCase
{
    protected \ILIAS\Block\BlockSessionRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new \ILIAS\Block\BlockSessionRepository();
    }

    protected function tearDown(): void
    {
    }

    /**
     * Test parent set/get
     */
    public function testParent(): void
    {
        $repo = $this->repo;
        $repo->setNavPar("one", "test");
        $this->assertEquals(
            "test",
            $repo->getNavPar("one")
        );
    }
}
