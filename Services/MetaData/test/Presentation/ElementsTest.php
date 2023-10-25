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

namespace ILIAS\MetaData\Presentation;

use PHPUnit\Framework\TestCase;
use ILIAS\Data\DateFormat\DateFormat;
use ILIAS\MetaData\Elements\Base\BaseElementInterface;
use ILIAS\MetaData\Elements\NoID;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Elements\Base\NullBaseElement;
use ILIAS\MetaData\Structure\Definitions\NullDefinition;

class ElementsTest extends TestCase
{
    protected function getElements(): Elements
    {
        $format = $this->createMock(DateFormat::class);
        $util = new class ($format) extends NullUtilities {
            public function txt(string $key): string
            {
                return 'translated ' . $key;
            }
        };

        return new Elements($util);
    }

    protected function getBaseElement(
        string $name,
        ?BaseElementInterface $super
    ): BaseElementInterface {
        return new class ($name, $super) extends NullBaseElement {
            public function __construct(
                protected string $name,
                protected ?BaseElementInterface $super
            ) {
            }

            public function getDefinition(): DefinitionInterface
            {
                return new class ($this->name) extends NullDefinition {
                    public function __construct(protected string $name)
                    {
                    }

                    public function name(): string
                    {
                        return $this->name;
                    }
                };
            }

            public function getSuperElement(): ?BaseElementInterface
            {
                return $this->super;
            }

            public function isRoot(): bool
            {
                return is_null($this->super);
            }
        };
    }

    protected function getTreeOfBaseElements(): BaseElementInterface
    {
        $root = $this->getBaseElement('root', null);
        $high = $this->getBaseElement('high', $root);
        $middle = $this->getBaseElement('lifeCycle', $high);
        return $this->getBaseElement('lowElement', $middle);
    }

    public function testName(): void
    {
        $elements = $this->getElements();
        $low = $this->getTreeOfBaseElements();
        $this->assertSame(
            'translated meta_low_element',
            $elements->name($low)
        );
        $this->assertSame(
            'translated meta_low_element_plural',
            $elements->name($low, true)
        );
        $lifecycle = $low->getSuperElement();
        $this->assertSame(
            'translated meta_lifecycle',
            $elements->name($lifecycle)
        );
        $this->assertSame(
            'translated meta_lifecycle_plural',
            $elements->name($lifecycle, true)
        );
    }

    public function testNameWithParents(): void
    {
        $elements = $this->getElements();
        $low = $this->getTreeOfBaseElements();
        $this->assertSame(
            'translated meta_high: translated meta_lifecycle: translated meta_low_element',
            $elements->nameWithParents($low)
        );
        $this->assertSame(
            'translated meta_high: translated meta_lifecycle: translated meta_low_element_plural',
            $elements->nameWithParents($low, null, true)
        );
    }

    public function testNameWithParentsForRoot(): void
    {
        $elements = $this->getElements();
        $root = $this->getBaseElement('root', null);
        $this->assertSame(
            'translated meta_root',
            $elements->nameWithParents($root)
        );
        $this->assertSame(
            'translated meta_root_plural',
            $elements->nameWithParents($root, null, true)
        );
        $this->assertSame(
            'translated meta_root',
            $elements->nameWithParents($root, null, false, true)
        );
    }

    public function testNameWithParentsAndSkipInitial(): void
    {
        $elements = $this->getElements();
        $low = $this->getTreeOfBaseElements();
        $this->assertSame(
            'translated meta_high: translated meta_lifecycle',
            $elements->nameWithParents($low, null, false, true)
        );
        $this->assertSame(
            'translated meta_high: translated meta_lifecycle',
            $elements->nameWithParents($low, null, true, true)
        );
    }

    public function testNameWithParentsAndCutOff(): void
    {
        $elements = $this->getElements();
        $low = $this->getTreeOfBaseElements();
        $lifecycle = $low->getSuperElement();
        $high = $lifecycle->getSuperElement();
        $this->assertSame(
            'translated meta_lifecycle: translated meta_low_element',
            $elements->nameWithParents($low, $high)
        );
        $this->assertSame(
            'translated meta_lifecycle: translated meta_low_element_plural',
            $elements->nameWithParents($low, $high, true)
        );
        $this->assertSame(
            'translated meta_low_element',
            $elements->nameWithParents($low, $lifecycle)
        );
        $this->assertSame(
            'translated meta_lifecycle',
            $elements->nameWithParents($low, $high, true, true)
        );
        $this->assertSame(
            'translated meta_low_element_plural',
            $elements->nameWithParents($low, $lifecycle, true, true)
        );
    }
}
