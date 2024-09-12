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

use ILIAS\LegalDocuments\Map;
use ILIAS\LegalDocuments\Wiring;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\LazyProvide;
use ILIAS\LegalDocuments\UseSlot;
use ILIAS\LegalDocuments\Consumer;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Internal;

require_once __DIR__ . '/ContainerMock.php';

class InternalTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Internal::class, new Internal($this->fail(...), $this->fail(...), []));
    }

    public function testAll(): void
    {
        $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $this->createWithDummy()->all('foo'));
    }

    public function testGet(): void
    {
        $this->assertSame(2, $this->createWithDummy()->get('foo', 'b'));
    }

    private function createWithDummy(): Internal
    {
        return new Internal(
            $this->fail(...),
            fn() => $this->wiring(),
            [
                $this->dummyConsumer()::class
            ]
        );
    }

    private function dummyConsumer(): Consumer
    {
        return new class () implements Consumer {
            public function uses(UseSlot $slot, LazyProvide $provide): UseSlot
            {
                return $slot;
            }
            public function id(): string
            {
                return self::class;
            }
        };
    }

    private function wiring(): Wiring
    {
        return new class () extends Wiring {
            public function __construct()
            {
            }
            public function map(): Map
            {
                return new Map(['foo' => ['a' => 1, 'b' => 2, 'c' => 3]]);
            }
        };
    }
}
