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

namespace ILIAS\MetaData\Services\Reader;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Elements\NullSet;
use ILIAS\MetaData\Paths\Navigator\NullNavigatorFactory;
use ILIAS\MetaData\Paths\Navigator\NavigatorInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Paths\NullPath;
use ILIAS\MetaData\Paths\Navigator\NullNavigator;
use ILIAS\MetaData\Elements\Data\NullData;
use ILIAS\MetaData\Elements\NullElement;
use ILIAS\MetaData\Elements\Data\DataInterface;
use ILIAS\MetaData\Elements\Data\Type;

class ReaderTest extends TestCase
{
    protected function getPath(int $number_of_data): PathInterface
    {
        return new class ($number_of_data) extends NullPath {
            public function __construct(public int $number_of_data)
            {
            }
        };
    }

    protected function getReader(): Reader
    {
        $nav = new class () extends NullNavigatorFactory {
            public function navigator(
                PathInterface $path,
                ElementInterface $start_element
            ): NavigatorInterface {
                return new class ($path->number_of_data) extends NullNavigator {
                    public function __construct(protected int $number_of_data)
                    {
                    }

                    protected function getElement(string $value): ElementInterface
                    {
                        return new class ($value) extends NullElement {
                            public function __construct(protected string $value)
                            {
                            }

                            public function getData(): DataInterface
                            {
                                return new class ($this->value) extends NullData {
                                    public function __construct(protected string $value)
                                    {
                                    }

                                    public function value(): string
                                    {
                                        return $this->value;
                                    }
                                };
                            }
                        };
                    }

                    public function elementsAtFinalStep(): \Generator
                    {
                        for ($i = 0; $i < $this->number_of_data; $i++) {
                            yield $this->getElement((string) $i);
                        }
                    }
                };
            }
        };

        return new Reader($nav, new NullSet());
    }

    public function testAllData(): void
    {
        $reader = $this->getReader();

        $this->assertSame(
            3,
            count(iterator_to_array($reader->allData($this->getPath(3))))
        );
        $this->assertNull($reader->allData($this->getPath(0))->current());
    }

    public function testFirstData(): void
    {
        $reader = $this->getReader();

        $this->assertSame(
            '0',
            $reader->firstData($this->getPath(1))->value()
        );
        $this->assertSame(
            '0',
            $reader->firstData($this->getPath(3))->value()
        );

        $null_data = $reader->firstData($this->getPath(0));
        $this->assertSame(
            '',
            $null_data->value()
        );
        $this->assertSame(
            Type::NULL,
            $null_data->type()
        );
    }
}
