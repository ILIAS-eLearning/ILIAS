<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class AccessSessionRepositoryTest extends TestCase
{
    protected \ILIAS\Portfolio\Access\AccessSessionRepository $repo;

    protected function setUp() : void
    {
        parent::setUp();
        $this->repo = new \ILIAS\Portfolio\Access\AccessSessionRepository();
    }

    protected function tearDown() : void
    {
    }

    public function testSharesSessionPassword()
    {
        $repo = $this->repo;
        $repo->setSharedSessionPassword(5, "mypass");
        $this->assertEquals(
            "mypass",
            $repo->getSharedSessionPassword(5)
        );
    }
}
