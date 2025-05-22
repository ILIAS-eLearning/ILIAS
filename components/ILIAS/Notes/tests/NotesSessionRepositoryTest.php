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
 * Test session repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class NotesSessionRepositoryTest extends TestCase
{
    protected \ILIAS\Notes\NotesSessionRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new \ILIAS\Notes\NotesSessionRepository();
    }

    protected function tearDown(): void
    {
    }

    /**
     * Test sort
     */
    public function testSortAscending(): void
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
