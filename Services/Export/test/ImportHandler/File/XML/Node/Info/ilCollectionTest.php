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

namespace Test\ImportHandler\File\XML\Node\Info;

use ImportHandler\File\XML\Node\Info\DOM\ilHandler;
use ImportHandler\File\XML\Node\Info\ilCollection;
use PHPUnit\Framework\TestCase;

class ilCollectionTest extends TestCase
{
    public function testNodeInfoCollection(): void
    {
        $node1 = $this->createMock(ilHandler::class);
        $node2 = $this->createMock(ilHandler::class);
        $node3 = $this->createMock(ilHandler::class);

        $collection = new ilCollection();
        $collection = $collection->withElement($node1);
        $collection = $collection->withElement($node2);
        $collection = $collection->withElement($node3);

        $collection2 = $collection->removeFirst();
        $collection3 = $collection2->removeFirst();
        $collection4 = $collection3->removeFirst();

        $this->assertEquals($node1, $collection->getFirst());
        $this->assertEquals($node2, $collection2->getFirst());
        $this->assertEquals($node3, $collection3->getFirst());

        $this->assertCount(3, $collection);
        $this->assertCount(2, $collection2);
        $this->assertCount(1, $collection3);
        $this->assertCount(0, $collection4);
    }
}
