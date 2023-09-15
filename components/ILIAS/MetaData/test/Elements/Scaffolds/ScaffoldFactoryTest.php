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

namespace ILIAS\MetaData\Elements\Scaffolds;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Elements\Data\DataFactoryInterface;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Elements\Data\DataInterface;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\NoID;

class ScaffoldFactoryTest extends TestCase
{
    public function testCreateScaffold(): void
    {
        $factory = new ScaffoldFactory(new MockDataFactory());
        $scaffold = $factory->scaffold(new MockDefinition());

        $this->assertInstanceOf(ElementInterface::class, $scaffold);
        $this->assertSame(NoID::SCAFFOLD, $scaffold->getMDID());
        $this->assertSame(Type::NULL, $scaffold->getData()->type());
    }
}

class MockDataFactory implements DataFactoryInterface
{
    public function data(Type $type, string $value): DataInterface
    {
        throw new \ilMDElementsException(
            'This should not be called here.'
        );
    }

    public function null(): DataInterface
    {
        return new MockNullData();
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

class MockDefinition implements DefinitionInterface
{
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
}
