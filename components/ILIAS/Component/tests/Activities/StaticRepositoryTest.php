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

namespace ILIAS\Component\Tests\Activities;

use PHPUnit\Framework\TestCase;
use ILIAS\Component\Activities\StaticRepository;
use ILIAS\Component\Activities\Activity;
use ILIAS\Component\Dependencies\Name;

class StaticRepositoryTest extends TestCase
{
    public function testEmptyRepository(): void
    {
        $repository = new StaticRepository([]);

        $this->assertEquals([], iterator_to_array($repository->getActivitiesByName("%.*%")));
    }

    public function testKeyIsName(): void
    {
        $name = "\\ILIAS\\Component\\Tests\\SomeActivity";

        $activity = $this->createMock(Activity::class);
        $activity
            ->method("getName")
            ->willReturn(new Name($name));

        $repository = new StaticRepository([$activity]);

        $activities = iterator_to_array($repository->getActivitiesByName("%.*%"));

        $this->assertEquals($activity, $activities[$name]);
    }

    public function testGetActivitiesByNameNoMatch(): void
    {
        $name = "\\ILIAS\\Component\\Tests\\SomeActivity";

        $activity = $this->createMock(Activity::class);
        $activity
            ->method("getName")
            ->willReturn(new Name($name));

        $repository = new StaticRepository([$activity]);

        $activities = iterator_to_array($repository->getActivitiesByName("%Foo%"));

        $this->assertEquals([], $activities);
    }

    public function testGetActivitiesByNameMatch(): void
    {
        $name = "\\ILIAS\\Component\\Tests\\SomeActivity";

        $activity = $this->createMock(Activity::class);
        $activity
            ->method("getName")
            ->willReturn(new Name($name));

        $repository = new StaticRepository([$activity]);

        $activities = iterator_to_array($repository->getActivitiesByName("%.*Some.*%"));

        $this->assertEquals($activity, $activities[$name]);
    }
}
