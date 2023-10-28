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

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ClassificationSessionRepositoryTest extends TestCase
{
    protected \ILIAS\Container\Classification\ClassificationSessionRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new \ILIAS\Container\Classification\ClassificationSessionRepository(14);
    }

    protected function tearDown(): void
    {
    }

    public function testValueForProvider(): void
    {
        $repo = $this->repo;
        $repo->setValueForProvider("prov", [1, 2, 3]);
        $this->assertEquals(
            [1, 2, 3],
            $repo->getValueForProvider("prov")
        );
    }

    public function testUnsetAll(): void
    {
        $repo = $this->repo;
        $repo->setValueForProvider("prov", [1, 2, 3]);
        $repo->unsetAll();
        $this->assertEquals(
            [],
            $repo->getValueForProvider("prov")
        );
    }

    public function testUnsetValueForProvider(): void
    {
        $repo = $this->repo;
        $repo->setValueForProvider("prov1", [1, 2, 3]);
        $repo->setValueForProvider("prov2", [3, 4, 5]);
        $repo->unsetValueForProvider("prov1");
        $this->assertEquals(
            [],
            $repo->getValueForProvider("prov1")
        );
        $this->assertEquals(
            [3, 4, 5],
            $repo->getValueForProvider("prov2")
        );
    }
}
