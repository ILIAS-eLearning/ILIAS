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
class ImageMapEditSessionRepositoryTest extends TestCase
{
    //protected $backupGlobals = false;
    protected \ILIAS\MediaObjects\ImageMap\ImageMapEditSessionRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new \ILIAS\MediaObjects\ImageMap\ImageMapEditSessionRepository();
        $this->repo->clear();
    }

    protected function tearDown(): void
    {
    }

    /**
     * Test clear
     */
    public function testClear(): void
    {
        $repo = $this->repo;

        $repo->setCoords("1,2,3");
        $repo->setAreaNr(4);
        $repo->setLinkType("int");
        $repo->clear();
        $this->assertEquals(
            "",
            $repo->getCoords()
        );
        $this->assertEquals(
            0,
            $repo->getAreaNr()
        );
        $this->assertEquals(
            "",
            $repo->getLinkType()
        );
    }

    public function testTargetScript(): void
    {
        $repo = $this->repo;
        $repo->setTargetScript("ilias.php?a=1");
        $this->assertEquals(
            "ilias.php?a=1",
            $repo->getTargetScript()
        );
    }

    public function testLinkType(): void
    {
        $repo = $this->repo;
        $repo->setLinkType("ext");
        $this->assertEquals(
            "ext",
            $repo->getLinkType()
        );
    }

    public function testAreaNr(): void
    {
        $repo = $this->repo;
        $repo->setAreaNr(4);
        $this->assertEquals(
            4,
            $repo->getAreaNr()
        );
    }

    public function testCoords(): void
    {
        $repo = $this->repo;
        $repo->setCoords("7,8,9,2");
        $this->assertEquals(
            "7,8,9,2",
            $repo->getCoords()
        );
    }

    public function testAreaType(): void
    {
        $repo = $this->repo;
        $repo->setAreaType("Rect");
        $this->assertEquals(
            "Rect",
            $repo->getAreaType()
        );
    }

    public function testExternalLink(): void
    {
        $repo = $this->repo;
        $repo->setExternalLink("https://www.ilias.de");
        $this->assertEquals(
            "https://www.ilias.de",
            $repo->getExternalLink()
        );
    }
}
