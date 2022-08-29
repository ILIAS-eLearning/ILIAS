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

class FactoryTest extends TestCase
{
    protected function getMockDefition(): DefinitionInterface
    {
        return new class () implements DefinitionInterface {
            public function name(): string
            {
                return 'name';
            }

            public function unique(): bool
            {
                return false;
            }

            public function dataType(): Type
            {
                return Type::NULL;
            }
        };
    }

    protected function getMockRessourceID(): RessourceIDInterface
    {
        return new class () implements RessourceIDInterface {
            public function type(): string
            {
                return 'type';
            }

            public function objID(): int
            {
                return 0;
            }

            public function subID(): int
            {
                return 0;
            }
        };
    }

    public function testCreateElement(): void
    {
        $factory = new Factory(new MockDataFactory());
        $el = $factory->element(13, $this->getMockDefition(), 'value');

        $this->assertInstanceOf(Element::class, $el);
        $this->assertFalse($el->isRoot());
        $this->assertFalse($el->isScaffold());
    }

    public function testCreateRoot(): void
    {
        $factory = new Factory(new MockDataFactory());
        $root = $factory->root($this->getMockDefition());

        $this->assertInstanceOf(ElementInterface::class, $root);
        $this->assertTrue($root->isRoot());
        $this->assertFalse($root->isScaffold());
    }

    public function testCreateSet(): void
    {
        $factory = new Factory(new MockDataFactory());
        $root = $factory->root($this->getMockDefition());
        $set = $factory->set($this->getMockRessourceID(), $root);

        $this->assertInstanceOf(SetInterface::class, $set);
    }
}

class MockDataFactory implements DataFactoryInterface
{
    public function data(Type $type, string $value): DataInterface
    {
        return new MockData();
    }

    public function null(): DataInterface
    {
        return new MockNullData();
    }
}

class MockData implements DataInterface
{
    public function type(): Type
    {
        return Type::STRING;
    }

    public function value(): string
    {
        return 'value';
    }
}

class MockNullData implements DataInterface
{
    public function type(): Type
    {
        return Type::NULL;
    }

    public function value(): string
    {
        return '';
    }
}
