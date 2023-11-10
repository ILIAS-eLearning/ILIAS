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

namespace ILIAS\MetaData\Elements\Base;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Elements\NoID;
use ILIAS\MetaData\Structure\Definitions\NullDefinition;

class BaseElementTest extends TestCase
{
    protected function getBaseElement(
        int|NoID $md_id,
        string $name,
        BaseElement ...$sub_elements
    ): BaseElement {
        $definition = $this->getDefinition($name);
        return new class ($md_id, $definition, ...$sub_elements) extends BaseElement {
            public function __construct(
                NoID|int $md_id,
                DefinitionInterface $definition,
                BaseElement ...$sub_elements
            ) {
                parent::__construct($md_id, $definition, ...$sub_elements);
            }
        };
    }

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

    public function testSubAndSuperElements(): void
    {
        $el11 = $this->getBaseElement(11, '1.1');
        $el21 = $this->getBaseElement(21, '2.1');
        $el22 = $this->getBaseElement(22, '2.2');

        $el1 = $this->getBaseElement(1, '1', $el11);
        $el2 = $this->getBaseElement(2, '2', $el21, $el22);

        $root = $this->getBaseElement(NoID::ROOT, 'root', $el1, $el2);

        $this->assertNull($root->getSuperElement());
        $this->assertSame($root, $el2->getSuperElement());

        $subs = $el2->getSubElements();
        $this->assertSame($subs->current(), $el21);
        $subs->next();
        $this->assertSame($subs->current(), $el22);
        $subs->next();
        $this->assertNull($subs->current());

        $this->assertSame($el2, $el21->getSuperElement());
        $this->assertNull($el21->getSubElements()->current());
    }

    public function testClone(): void
    {
        $el11 = $this->getBaseElement(11, '1.1');
        $el1 = $this->getBaseElement(1, '1', $el11);
        $root = $this->getBaseElement(NoID::ROOT, 'root', $el1);

        $cloned_root = clone $root;
        $cloned_subs = $cloned_root->getSubElements();
        $cloned_el = $cloned_subs->current();
        $this->assertEquals($el1, $cloned_el);
        $this->assertNotSame($el1, $cloned_el);
        $this->assertSame($cloned_root, $cloned_el->getSuperElement());
        $cloned_subs->next();
        $this->assertNull($cloned_subs->current());

        $cloned_subs = $cloned_el->getSubElements();
        $this->assertEquals($el11, $cloned_subs->current());
        $this->assertNotSame($el11, $cloned_subs->current());
        $this->assertSame($cloned_el, $cloned_subs->current()->getSuperElement());
        $cloned_subs->next();
        $this->assertNull($cloned_subs->current());

        $cloned_el1 = clone $el1;
        $this->assertNull($cloned_el1->getSuperElement());
    }

    public function testRootAsSubElementException(): void
    {
        $root = $this->getBaseElement(NoID::ROOT, 'root');

        $this->expectException(\ilMDElementsException::class);
        $not_root = $this->getBaseElement(13, 'name', $root);
    }

    public function testMDIDAndIsRoot(): void
    {
        $root = $this->getBaseElement(NoID::ROOT, 'root');
        $not_root = $this->getBaseElement(13, 'name');

        $this->assertSame(NoID::ROOT, $root->getMDID());
        $this->assertSame(13, $not_root->getMDID());

        $this->assertTrue($root->isRoot());
        $this->assertFalse($not_root->isRoot());
    }

    public function testDefinition(): void
    {
        $def = $this->getDefinition('name');
        $el = new class (13, $def) extends BaseElement {
            public function __construct(
                NoID|int $md_id,
                DefinitionInterface $definition
            ) {
                parent::__construct($md_id, $definition);
            }
        };

        $this->assertSame($def, $el->getDefinition());
    }
}
