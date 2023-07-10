<?php

use PHPUnit\Framework\TestCase;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class BadgeManagementSessionRepositoryTest extends TestCase
{
    protected ilBadgeManagementSessionRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new ilBadgeManagementSessionRepository();
        $this->repo->clear();
    }

    protected function tearDown(): void
    {
    }

    public function testClear(): void
    {
        $repo = $this->repo;
        $repo->setBadgeIds([1,3,4]);
        $repo->clear();
        $this->assertEquals(
            [],
            $repo->getBadgeIds()
        );
    }

    public function testBadgeIds(): void
    {
        $repo = $this->repo;
        $repo->setBadgeIds([1,6,7]);
        $this->assertEquals(
            [1,6,7],
            $repo->getBadgeIds()
        );
    }
}
