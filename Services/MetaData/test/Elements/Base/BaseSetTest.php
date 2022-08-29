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

class BaseSetTest extends TestCase
{
    public function testGetRoot(): void
    {
        $root = new MockBaseRoot();
        $set = new ImplementedBaseSet($root);

        $this->assertSame($root, $set->getRoot());
    }

    public function testNotRootException(): void
    {
        $not_root = new MockBaseNotRoot();

        $this->expectException(\ilMDElementsException::class);
        $set = new ImplementedBaseSet($not_root);
    }

    public function testClone(): void
    {
        $root = new MockBaseRoot();
        $set = new ImplementedBaseSet($root);

        $cloned_set = clone $set;
        $this->assertEquals($root, $cloned_set->getRoot());
        $this->assertNotSame($root, $cloned_set->getRoot());
    }
}

class ImplementedBaseSet extends BaseSet
{
}

class MockBaseRoot implements BaseElementInterface
{
    public function getMDID(): int|NoID
    {
        return NoID::ROOT;
    }

    public function getDefinition(): DefinitionInterface
    {
        throw new \ilMDElementsException('This should not be called.');
    }

    public function getSubElements(): \Generator
    {
        throw new \ilMDElementsException('This should not be called.');
    }

    public function getSuperElement(): ?BaseElementInterface
    {
        return null;
    }

    public function isRoot(): bool
    {
        return true;
    }
}

class MockBaseNotRoot extends MockBaseRoot
{
    public function getMDID(): int|NoID
    {
        return 13;
    }

    public function isRoot(): bool
    {
        return false;
    }
}
