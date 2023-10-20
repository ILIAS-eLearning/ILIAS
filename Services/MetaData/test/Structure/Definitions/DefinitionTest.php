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

namespace ILIAS\MetaData\Structure\Definitions;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Elements\Data\Type;

class DefinitionTest extends TestCase
{
    public function testName(): void
    {
        $definition = new Definition('some name', false, Type::NULL);
        $this->assertSame(
            'some name',
            $definition->name()
        );
    }

    public function testUnique(): void
    {
        $definition = new Definition('', false, Type::NULL);
        $this->assertFalse($definition->unique());
        $definition = new Definition('', true, Type::NULL);
        $this->assertTrue($definition->unique());
    }

    public function testDataType(): void
    {
        $definition = new Definition('', false, Type::NULL);
        $this->assertSame(
            Type::NULL,
            $definition->dataType()
        );
        $definition = new Definition('', false, Type::STRING);
        $this->assertSame(
            Type::STRING,
            $definition->dataType()
        );
        $definition = new Definition('', false, Type::VOCAB_SOURCE);
        $this->assertSame(
            Type::VOCAB_SOURCE,
            $definition->dataType()
        );
    }
}
