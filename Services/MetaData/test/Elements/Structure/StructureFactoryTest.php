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

namespace ILIAS\MetaData\Elements\Structure;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Structure\Definitions\NullDefinition;

class StructureFactoryTest extends TestCase
{
    public function testCreateElement(): void
    {
        $factory = new StructureFactory();
        $struct = $factory->structure(new NullDefinition());

        $this->assertInstanceOf(StructureElement::class, $struct);
        $this->assertFalse($struct->isRoot());
    }

    public function testCreateRoot(): void
    {
        $factory = new StructureFactory();
        $struct = $factory->root(new NullDefinition());

        $this->assertInstanceOf(StructureElementInterface::class, $struct);
        $this->assertTrue($struct->isRoot());
    }

    public function testCreateSet(): void
    {
        $factory = new StructureFactory();
        $root = $factory->root(new NullDefinition());
        $set = $factory->set($root);

        $this->assertInstanceOf(StructureSetInterface::class, $set);
    }
}
