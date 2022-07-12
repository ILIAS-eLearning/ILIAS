<?php declare(strict_types=1);

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

namespace ILIAS\Tests\Refinery\Container;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Container\Group as ContainerGroup;
use ILIAS\Refinery\Container\AddLabels;
use ILIAS\Tests\Refinery\TestCase;

class GroupTest extends TestCase
{
    private ContainerGroup $group;
    private DataFactory $dataFactory;

    protected function setUp() : void
    {
        $this->dataFactory = new DataFactory();
        $this->group = new ContainerGroup($this->dataFactory);
    }

    public function testCustomConstraint() : void
    {
        $instance = $this->group->addLabels(['hello', 'world']);
        $this->assertInstanceOf(AddLabels::class, $instance);
    }
}
