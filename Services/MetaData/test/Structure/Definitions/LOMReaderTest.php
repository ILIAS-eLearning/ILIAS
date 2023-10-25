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

class LOMReaderTest extends TestCase
{
    protected function getInitialReader(): LOMReader
    {
        $initial_array = [
            'name' => 'root',
            'unique' => true,
            'type' => Type::NULL,
            'sub' => [
                [
                    'name' => 'sub string',
                    'unique' => false,
                    'type' => Type::STRING,
                    'sub' => []
                ],
                [
                    'name' => 'sub language',
                    'unique' => true,
                    'type' => Type::LANG,
                    'sub' => [
                        [
                            'name' => 'sub sub duration',
                            'unique' => false,
                            'type' => Type::DURATION,
                            'sub' => []
                        ]
                    ]
                ]
            ]
        ];
        return new class ($initial_array) extends LOMReader {
            public function __construct(protected array $initial_array)
            {
                parent::__construct();
            }

            protected function getDefinitionArray(): array
            {
                return $this->initial_array;
            }
        };
    }

    public function testGetDefinition(): void
    {
        $reader = $this->getInitialReader();
        $definition = $reader->definition();
        $this->assertSame(
            'root',
            $definition->name()
        );
        $this->assertSame(
            Type::NULL,
            $definition->dataType()
        );
        $this->assertTrue($definition->unique());
    }

    public function testSubDefinitions(): void
    {
        $sub_readers = $this->getInitialReader()->subDefinitions();
        $first_reader = $sub_readers->current();
        $sub_readers->next();
        $second_reader = $sub_readers->current();
        $sub_readers->next();
        $third_reader = $sub_readers->current();

        $definition = $first_reader->definition();
        $this->assertSame(
            'sub string',
            $definition->name()
        );
        $this->assertSame(
            Type::STRING,
            $definition->dataType()
        );
        $this->assertFalse($definition->unique());

        $definition = $second_reader->definition();
        $this->assertSame(
            'sub language',
            $definition->name()
        );
        $this->assertSame(
            Type::LANG,
            $definition->dataType()
        );
        $this->assertTrue($definition->unique());

        $this->assertNull($third_reader);
        $this->assertNull($first_reader->subDefinitions()->current());
        $this->assertInstanceOf(
            LOMReader::class,
            $second_reader->subDefinitions()->current()
        );
    }
}
