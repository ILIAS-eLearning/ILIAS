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

namespace ILIAS\MetaData\Elements;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\Elements\NoID;
use ILIAS\MetaData\Elements\Data\DataInterface;
use ILIAS\MetaData\Elements\RessourceID\RessourceIDInterface;
use ILIAS\MetaData\Repository\Utilities\ScaffoldProviderInterface;
use ILIAS\MetaData\Elements\Markers\Action;
use ILIAS\MetaData\Elements\Markers\MarkerFactoryInterface;
use ILIAS\MetaData\Elements\Markers\MarkerInterface;

class SetTest extends TestCase
{
    protected function getMockRessourceID(): RessourceIDInterface
    {
        return new class () implements RessourceIDInterface {
            public function type(): string
            {
                return 'type';
            }

            public function objID(): int
            {
                return 0;
            }

            public function subID(): int
            {
                return 0;
            }
        };
    }

    public function testGetRoot(): void
    {
        $root = new MockRoot();
        $set = new Set($this->getMockRessourceID(), $root);

        $this->assertSame($root, $set->getRoot());
    }

    public function testGetRessourceID(): void
    {
        $root = new MockRoot();
        $id = $this->getMockRessourceID();
        $set = new Set($id, $root);

        $this->assertSame($id, $set->getRessourceID());
    }
}

class MockRoot implements ElementInterface
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

    public function getSuperElement(): ?ElementInterface
    {
        return null;
    }

    public function isScaffold(): bool
    {
        $this->throwException();
    }

    public function getData(): DataInterface
    {
        $this->throwException();
    }

    public function isMarked(): bool
    {
        $this->throwException();
    }

    public function getMarker(): ?MarkerInterface
    {
        $this->throwException();
    }

    public function mark(MarkerFactoryInterface $factory, Action $action, string $data_value = '')
    {
        $this->throwException();
    }

    public function addScaffoldsToSubElements(ScaffoldProviderInterface $scaffold_provider): void
    {
        $this->throwException();
    }

    public function addScaffoldToSubElements(
        ScaffoldProviderInterface $scaffold_provider,
        string $name
    ): ?ElementInterface {
        $this->throwException();
    }

    protected function throwException(): void
    {
        throw new \ilMDElementsException('This should not be called.');
    }
}
