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
