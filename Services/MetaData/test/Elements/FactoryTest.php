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

namespace ILIAS\MetaData\Elements;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Elements\Data\DataFactoryInterface;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Elements\Data\DataInterface;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\Elements\RessourceID\RessourceIDInterface;
use ILIAS\MetaData\Structure\Definitions\NullDefinition;
use ILIAS\MetaData\Elements\Data\NullDataFactory;
use ILIAS\MetaData\Elements\RessourceID\NullRessourceID;

class FactoryTest extends TestCase
{
    public function testCreateElement(): void
    {
        $factory = new Factory(new NullDataFactory());
        $el = $factory->element(13, new NullDefinition(), 'value');

        $this->assertInstanceOf(Element::class, $el);
        $this->assertFalse($el->isRoot());
        $this->assertFalse($el->isScaffold());
    }

    public function testCreateRoot(): void
    {
        $factory = new Factory(new NullDataFactory());
        $root = $factory->root(new NullDefinition());

        $this->assertInstanceOf(ElementInterface::class, $root);
        $this->assertTrue($root->isRoot());
        $this->assertFalse($root->isScaffold());
    }

    public function testCreateSet(): void
    {
        $factory = new Factory(new NullDataFactory());
        $root = $factory->root(new NullDefinition());
        $set = $factory->set(new NullRessourceID(), $root);

        $this->assertInstanceOf(SetInterface::class, $set);
    }
}
