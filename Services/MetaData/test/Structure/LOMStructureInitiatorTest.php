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

namespace ILIAS\MetaData\Structure;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Elements\Structure\StructureFactory;
use ILIAS\MetaData\Structure\Definitions\NullReaderFactory;
use ILIAS\MetaData\Structure\Definitions\ReaderInterface;
use ILIAS\MetaData\Structure\Definitions\NullReader;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\Structure\Definitions\NullDefinition;

class LOMStructureInitiatorTest extends TestCase
{
    protected function getLOMStructureInitiator(): LOMStructureInitiator
    {
        $initial_array = [
            'name' => 'root',
            'subs' => [
                [
                    'name' => 'first',
                    'subs' => []
                ],
                [
                    'name' => 'second',
                    'subs' => [
                        [
                            'name' => 'sub second',
                            'subs' => []
                        ]
                    ]
                ]
            ]
        ];

        $reader_factory = new class ($initial_array) extends NullReaderFactory {
            public function __construct(protected array $initial_array)
            {
            }

            public function reader(): ReaderInterface
            {
                return new class ($this->initial_array) extends NullReader {
                    public function __construct(protected array $array)
                    {
                    }

                    public function definition(): DefinitionInterface
                    {
                        return new class ($this->array['name']) extends NullDefinition {
                            public function __construct(protected string $name)
                            {
                            }

                            public function name(): string
                            {
                                return $this->name;
                            }
                        };
                    }

                    public function subDefinitions(): \Generator
                    {
                        foreach ($this->array['subs'] as $sub) {
                            yield new self($sub);
                        }
                    }
                };
            }
        };

        /*
         * The tree structure needs to be built from actual elements
         * and not only their interfaces (see BaseElement::addSubElement,
         *  so the factory does not have an interface and needs to be used
         *  here as is.
         */
        return new LOMStructureInitiator(
            $reader_factory,
            new StructureFactory()
        );
    }

    public function testSet(): void
    {
        $root = $this->getLOMStructureInitiator()->set()->getRoot();
        $this->assertSame(
            'root',
            $root->getDefinition()->name()
        );
        $subs = $root->getSubElements();
        $first = $subs->current();
        $subs->next();
        $second = $subs->current();
        $subs->next();
        $third = $subs->current();

        $this->assertSame(
            'first',
            $first->getDefinition()->name()
        );
        $this->assertSame(
            'second',
            $second->getDefinition()->name()
        );
        $this->assertNull($third);

        $this->assertNull($first->getSubElements()->current());
        $this->assertSame(
            'sub second',
            $second->getSubElements()->current()->getDefinition()->name()
        );
    }
}
