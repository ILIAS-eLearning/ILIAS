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

namespace Test\ImportHandler\File\Path;

use PHPUnit\Framework\TestCase;
use ILIAS\Export\ImportHandler\File\Path\ilHandler as ilFilePathHandler;
use ILIAS\Export\ImportHandler\File\Path\Node\ilSimple as ilSimpleFilePathNode;

class ilHandlerTest extends TestCase
{
    protected function setUp(): void
    {

    }

    public function testPath(): void
    {
        $node1 = $this->createMock(ilSimpleFilePathNode::class);
        $node1->expects($this->any())->method('toString')->willReturn('Node1');
        $node1->expects($this->any())->method('requiresPathSeparator')->willReturn(true);
        $node2 = $this->createMock(ilSimpleFilePathNode::class);
        $node2->expects($this->any())->method('toString')->willReturn('Node2');
        $node2->expects($this->any())->method('requiresPathSeparator')->willReturn(true);
        $node3 = $this->createMock(ilSimpleFilePathNode::class);
        $node3->expects($this->any())->method('toString')->willReturn('Node3');
        $node3->expects($this->any())->method('requiresPathSeparator')->willReturn(true);
        $nodes = [$node1, $node2, $node3];

        $path = new ilFilePathHandler();
        $path = $path
            ->withNode($node1)
            ->withNode($node2)
            ->withNode($node3);
        $path_at_root = $path
            ->withStartAtRoot(true);
        $sub_path = $path->subPath(1);
        $sub_path2 = $path->subPath(0, 2);

        $this->assertCount(3, $path);
        $this->assertEquals('//Node1/Node2/Node3', $path->toString());
        $this->assertEquals('/Node1/Node2/Node3', $path_at_root->toString());
        $this->assertEquals('//Node2/Node3', $sub_path->toString());
        $this->assertEquals('//Node1/Node2', $sub_path2->toString());
    }
}
