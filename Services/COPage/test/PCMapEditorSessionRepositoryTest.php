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
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class PCMapEditorSessionRepositoryTest extends TestCase
{
    //protected $backupGlobals = false;
    protected \ILIAS\COPage\PC\MapEditorSessionRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new \ILIAS\COPage\PC\MapEditorSessionRepository();
    }

    protected function tearDown(): void
    {
    }

    /**
     * Test mode
     */
    public function testMode(): void
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
    public function testAreaNr(): void
    {
        $repo = $this->repo;
        $repo->setAreaNr("3");
        $this->assertEquals(
            "3",
            $repo->getAreaNr()
        );
    }
}
