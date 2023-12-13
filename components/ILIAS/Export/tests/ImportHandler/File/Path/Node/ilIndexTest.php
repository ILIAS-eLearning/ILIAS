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

namespace Test\ImportHandler\File\Path\Node;

use ImportHandler\File\Path\Comparison\ilHandler as ilFilePathComparisonHandler;
use PHPUnit\Framework\TestCase;
use ImportHandler\File\Path\Node\ilIndex as ilIndexFilePathNode;

class ilIndexTest extends TestCase
{
    public function testIndexNode(): void
    {
        $comp = $this->createMock(ilFilePathComparisonHandler::class);
        $comp->expects($this->any())->method('toString')->willReturn('<3');

        $node = new ilIndexFilePathNode();
        $node2 = $node->withIndex(20);
        $node3 = $node2->withComparison($comp);
        $node4 = $node2->withIndexingFromEndEnabled(true);

        $this->assertEquals('[0]', $node->toString());
        $this->assertEquals('[20]', $node2->toString());
        $this->assertEquals('[position()<3]', $node3->toString());
        $this->assertEquals('[(last)-20]', $node4->toString());

        $this->assertFalse($node->requiresPathSeparator());
        $this->assertFalse($node2->requiresPathSeparator());
        $this->assertFalse($node3->requiresPathSeparator());
        $this->assertFalse($node4->requiresPathSeparator());
    }
}
