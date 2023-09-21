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

class StructureSetTest extends TestCase
{
    public function testGetRoot(): void
    {
        $root = new MockStructureRoot();
        $set = new StructureSet($root);

        $this->assertSame($root, $set->getRoot());
    }
}

class MockStructureRoot implements StructureElementInterface
{
    public function isRoot(): bool
    {
        return true;
    }

    public function getDefinition(): DefinitionInterface
    {
        $this->throwException();
    }

    public function getMDID(): NoID
    {
        return NoID::ROOT;
    }

    public function getSubElements(): \Generator
    {
        $this->throwException();
    }

    public function getSubElement(string $name): ?StructureElementInterface
    {
        $this->throwException();
    }

    public function getSuperElement(): ?StructureElementInterface
    {
        return null;
    }

    protected function throwException(): void
    {
        throw new \ilMDElementsException('This should not be called.');
    }
}
