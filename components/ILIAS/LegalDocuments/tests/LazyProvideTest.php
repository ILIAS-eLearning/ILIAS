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

namespace ILIAS\LegalDocuments\test;

use ILIAS\LegalDocuments\Provide;
use ILIAS\LegalDocuments\test\ContainerMock;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\LazyProvide;

require_once __DIR__ . '/ContainerMock.php';

class LazyProvideTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(LazyProvide::class, new LazyProvide($this->fail(...)));
    }

    /**
     * @dataProvider methods
     */
    public function testMethods(string $method): void
    {
        $called = false;
        $provide = $this->mockTree(Provide::class, [$method => []]);

        $create = function () use (&$called, $provide) {
            $called = true;
            return $provide;
        };

        $instance = new LazyProvide($create);
        $this->assertFalse($called);
        $this->assertSame($provide->$method(), $instance->$method());
        $this->assertTrue($called);
    }

    public function methods(): array
    {
        return [
            ['withdrawal'],
            ['publicPage'],
            ['document'],
            ['history'],
            ['allowEditing'],
        ];
    }
}
