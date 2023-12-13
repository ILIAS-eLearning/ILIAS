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

use PHPUnit\Framework\TestCase;
use ImportHandler\File\Path\Node\ilAttribute as ilAttributeFilePathNode;
use ImportHandler\File\Path\Comparison\ilHandler as ilFilePathComparisonHandler;

class ilAttributeTest extends TestCase
{
    protected function setUp(): void
    {

    }

    public function testAttributeTest(): void
    {
        $comp = $this->createMock(ilFilePathComparisonHandler::class);
        $comp->expects($this->any())->method('toString')->willReturn('<3');

        $node = new ilAttributeFilePathNode();
        $node2 = $node
            ->withComparison($comp)
            ->withAttribute('a');
        $node3 = $node
            ->withAnyAttributeEnabled(true);
        $node4 = $node2
            ->withAnyAttributeEnabled(true);
        $node5 = $node
            ->withAttribute('b');

        $this->assertEquals('[@]', $node->toString());
        $this->assertEquals('[@a<3]', $node2->toString());
        $this->assertEquals('[@*]', $node3->toString());
        $this->assertEquals('[@*]', $node4->toString());
        $this->assertEquals('[@b]', $node5->toString());

        $this->assertTrue($node->requiresPathSeparator());
        $this->assertTrue($node2->requiresPathSeparator());
        $this->assertTrue($node3->requiresPathSeparator());
        $this->assertTrue($node4->requiresPathSeparator());
        $this->assertTrue($node5->requiresPathSeparator());
    }
}
