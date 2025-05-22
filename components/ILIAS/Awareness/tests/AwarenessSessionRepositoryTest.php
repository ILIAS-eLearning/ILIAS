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
class AwarenessSessionRepositoryTest extends TestCase
{
    protected \ILIAS\Awareness\AwarenessSessionRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new \ILIAS\Awareness\AwarenessSessionRepository();
    }

    protected function tearDown(): void
    {
    }

    public function testCount(): void
    {
        $repo = $this->repo;
        $repo->setCount(15);
        $this->assertEquals(
            15,
            $repo->getCount()
        );
    }

    public function testHighlightCount(): void
    {
        $repo = $this->repo;
        $repo->setHighlightCount(6);
        $this->assertEquals(
            6,
            $repo->getHighlightCount()
        );
    }

    public function testLastUpdate(): void
    {
        $repo = $this->repo;
        $repo->setLastUpdate(1234);
        $this->assertEquals(
            1234,
            $repo->getLastUpdate()
        );
    }

    public function testOnlineUsersTS(): void
    {
        $repo = $this->repo;
        $repo->setOnlineUsersTS("2022-01-01 16:00:05");
        $this->assertEquals(
            "2022-01-01 16:00:05",
            $repo->getOnlineUsersTS()
        );
    }
}
