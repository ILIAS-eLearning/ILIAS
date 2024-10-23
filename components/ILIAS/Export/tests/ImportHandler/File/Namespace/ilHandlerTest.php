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

namespace Test\ImportHandler\File\Namespace;

use ILIAS\Export\ImportHandler\File\Namespace\ilHandler as ilFileNamespaceHandler;
use PHPUnit\Framework\TestCase;

class ilHandlerTest extends TestCase
{
    protected function setUp(): void
    {

    }

    public function testNamespace(): void
    {
        $namespace = new ilFileNamespaceHandler();
        $namespace = $namespace->withNamespace('http://test/test/test/4_2');
        $namespace = $namespace->withPrefix('prefix');

        $this->assertEquals('http://test/test/test/4_2', $namespace->getNamespace());
        $this->assertEquals('prefix', $namespace->getPrefix());
    }
}
