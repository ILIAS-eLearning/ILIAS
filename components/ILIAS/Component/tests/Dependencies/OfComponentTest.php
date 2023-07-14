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

namespace ILIAS\Component\Tests\Dependencies;

use PHPUnit\Framework\TestCase;
use ILIAS\Component\Component;
use ILIAS\Component\Dependencies as D;

class OfComponentTest extends TestCase
{
    protected Component $component;
    protected D\OfComponent $of_component;

    public function setUp(): void
    {
        $this->component = $this->createMock(Component::class);
        $this->of_component = new D\OfComponent(
            $this->component
        );
    }

    public function testGetComponent(): void
    {
        $this->assertEquals($this->component, $this->of_component->getComponent());
    }

    public function testInDependencies(): void
    {
        $name = TestInterface::class;

        $out = new D\Out(D\OutType::PROVIDE, $name, "Some\\Class", []);
        $in = new D\In(D\InType::PULL, $name);

        $of_component = new D\OfComponent(
            $this->component,
            $in,
            $out
        );

        $result = iterator_to_array($of_component->getInDependencies());

        $this->assertEquals([$in], $result);
    }
}
