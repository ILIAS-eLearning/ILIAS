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
use ILIAS\MetaData\Elements\RessourceID\NullRessourceID;

class SetTest extends TestCase
{
    protected function getRoot(): ElementInterface
    {
        return new class () extends NullElement {
            public function getMDID(): NoID|int
            {
                return NoID::ROOT;
            }

            public function isRoot(): bool
            {
                return true;
            }
        };
    }

    public function testGetRoot(): void
    {
        $root = $this->getRoot();
        $set = new Set(new NullRessourceID(), $root);

        $this->assertSame($root, $set->getRoot());
    }

    public function testGetRessourceID(): void
    {
        $root = $this->getRoot();
        $id = new NullRessourceID();
        $set = new Set($id, $root);

        $this->assertSame($id, $set->getRessourceID());
    }
}
