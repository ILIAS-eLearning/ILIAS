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

namespace ILIAS\Export\Test\ImportHandler\File\XML\Node\Info\Attribute;

use ILIAS\Export\ImportHandler\Parser\NodeInfo\Attribute\Handler as ilXMLFileNodeInfoAttributePair;
use PHPUnit\Framework\TestCase;

class ilPairTest extends TestCase
{
    public function testPair(): void
    {
        $pair = new ilXMLFileNodeInfoAttributePair();
        $pair = $pair
            ->withKey('key')
            ->withValue('value');
        $this->assertEquals('key', $pair->getKey());
        $this->assertEquals('value', $pair->getValue());
    }
}
