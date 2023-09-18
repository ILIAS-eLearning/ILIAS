<?php

use PHPUnit\Framework\TestCase;

/**
 * Test session repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class NavigationSessionRepositoryTest extends TestCase
{
    protected \ILIAS\Navigation\NavigationSessionRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new \ILIAS\Navigation\NavigationSessionRepository();
    }

    protected function tearDown(): void
    {
    }

    /**
     * Test history
     */
    public function testSortAscending(): void
    {
        $repo = $this->repo;
        $repo->setHistory([
            0 => "a",
            1 => "b"
        ]);
        $this->assertEquals(
            [
                0 => "a",
                1 => "b"
            ],
            $repo->getHistory()
        );
    }
}
