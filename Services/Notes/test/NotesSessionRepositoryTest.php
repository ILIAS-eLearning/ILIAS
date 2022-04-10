<?php

use PHPUnit\Framework\TestCase;

/**
 * Test session repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class NotesSessionRepositoryTest extends TestCase
{
    protected \ILIAS\Notes\NotesSessionRepository $repo;

    protected function setUp() : void
    {
        parent::setUp();
        $this->repo = new \ILIAS\Notes\NotesSessionRepository();
    }

    protected function tearDown() : void
    {
    }

    /**
     * Test sort
     */
    public function testSortAscending() : void
    {
        $repo = $this->repo;
        $repo->setSortAscending(true);
        $this->assertEquals(
            true,
            $repo->getSortAscending()
        );
        $repo->setSortAscending(false);
        $this->assertEquals(
            false,
            $repo->getSortAscending()
        );
    }
}
