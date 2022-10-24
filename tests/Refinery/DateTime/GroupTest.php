<?php

declare(strict_types=1);

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

namespace ILIAS\Tests\Refinery\DateTime;

use ILIAS\Refinery\DateTime\Group;
use ILIAS\Refinery\DateTime\ChangeTimezone;
use ILIAS\Tests\Refinery\TestCase;

class GroupTest extends TestCase
{
    private Group $group;

    protected function setUp(): void
    {
        $this->group = new Group();
    }

    public function testChangeTimezone(): void
    {
        $instance = $this->group->changeTimezone('Europe/Berlin');
        $this->assertInstanceOf(ChangeTimezone::class, $instance);
    }

    public function testChangeTimezoneWrongConstruction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $instance = $this->group->changeTimezone('MiddleEarth/Minas_Morgul');
    }
}
