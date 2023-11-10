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
use ILIAS\MetaData\Elements\NoID;
use ILIAS\MetaData\Structure\Definitions\NullDefinition;

class StructureElementTest extends TestCase
{
    protected function getDefinition(string $name): DefinitionInterface
    {
        return new class ($name) extends NullDefinition {
            public function __construct(protected string $name)
            {
            }

            public function name(): string
            {
                return $this->name;
            }
        };
    }

    protected function getStructureElement(
        bool $is_root,
        string $name,
        StructureElement ...$elements
    ): StructureElement {
        return new StructureElement(
            $is_root,
            $this->getDefinition($name),
            ...$elements
        );
    }

    public function testSubAndSuperElements(): void
    {
        $struct11 = $this->getStructureElement(false, '1.1');
        $struct1 = $this->getStructureElement(false, '1', $struct11);
        $struct2 = $this->getStructureElement(false, '2');
        $root = $this->getStructureElement(true, 'root', $struct1, $struct2);

        $subs = $root->getSubElements();
        $this->assertSame($struct1, $subs->current());
        $subs->next();
        $this->assertSame($struct2, $subs->current());
        $subs->next();
        $this->assertNull($subs->current());

        $this->assertSame($root, $struct1->getSuperElement());
        $this->assertSame($struct11, $struct1->getSubElements()->current());
    }

    public function testMDIDAndIsRoot(): void
    {
        $root = $this->getStructureElement(true, 'root');
        $not_root = $this->getStructureElement(false, 'name');

        $this->assertSame(NoID::ROOT, $root->getMDID());
        $this->assertSame(NoID::STRUCTURE, $not_root->getMDID());

        $this->assertTrue($root->isRoot());
        $this->assertFalse($not_root->isRoot());
    }

    public function testSubElementByName(): void
    {
        $sub1 = $this->getStructureElement(false, 'sub 1');
        $sub2 = $this->getStructureElement(false, 'sub 2');
        $sub3 = $this->getStructureElement(false, 'sub 3');
        $el = $this->getStructureElement(false, 'name', $sub1, $sub2, $sub3);

        $this->assertSame($sub2, $el->getSubElement('sub 2'));
        $this->assertSame($sub3, $el->getSubElement('sub 3'));
        $this->assertSame($sub1, $el->getSubElement('sub 1'));
        $this->assertNull($el->getSubElement('something else'));
    }
}
