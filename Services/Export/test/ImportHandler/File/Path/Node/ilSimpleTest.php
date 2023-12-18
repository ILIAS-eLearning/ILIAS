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
use ILIAS\Export\ImportHandler\File\Path\Node\ilSimple as ilSimpleFilePathNode;

class ilSimpleTest extends TestCase
{
    public function testSimpleNode(): void
    {
        $node = new ilSimpleFilePathNode();
        $node2 = $node->withName('Name1');

        $this->assertEquals('', $node->toString());
        $this->assertEquals('Name1', $node2->toString());
        $this->assertTrue($node->requiresPathSeparator());
        $this->assertTrue($node2->requiresPathSeparator());
    }
}
