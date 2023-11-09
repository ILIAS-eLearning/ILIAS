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

namespace ILIAS\LegalDocuments\test\Intercept;

use ILIAS\LegalDocuments\Value\Target;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\Intercept;
use ILIAS\LegalDocuments\Intercept\LazyIntercept;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../ContainerMock.php';

class LazyInterceptTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(LazyIntercept::class, new LazyIntercept($this->fail(...)));
    }

    public function testIntercept(): void
    {
        $intercept = $this->mockTree(Intercept::class, ['intercept' => true]);
        $instance = new LazyIntercept(fn() => $intercept);

        $this->assertTrue($intercept->intercept());

    }

    public function testId(): void
    {
        $intercept = $this->mockTree(Intercept::class, ['id' => 'foo']);
        $instance = new LazyIntercept(fn() => $intercept);

        $this->assertSame('foo', $intercept->id());
    }

    public function testTarget(): void
    {
        $target = $this->mock(Target::class);
        $intercept = $this->mockTree(Intercept::class, ['target' => $target]);
        $instance = new LazyIntercept(fn() => $intercept);

        $this->assertSame($target, $intercept->target());
    }
}
