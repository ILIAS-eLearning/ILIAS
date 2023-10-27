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

use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\Value\Target;
use ILIAS\LegalDocuments\Intercept\ConditionalIntercept;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../ContainerMock.php';

class ConditionalInterceptTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ConditionalIntercept::class, new ConditionalIntercept(
            $this->fail(...),
            'foo',
            $this->mock(Target::class)
        ));
    }

    public function testIntercept(): void
    {
        $instance = new ConditionalIntercept(
            fn() => true,
            'foo',
            $this->mock(Target::class)
        );

        $this->assertTrue($instance->intercept());
    }

    public function testId(): void
    {
        $instance = new ConditionalIntercept(
            $this->fail(...),
            'foo',
            $this->mock(Target::class)
        );

        $this->assertSame('foo', $instance->id());
    }

    public function testTarget(): void
    {
        $target = $this->mock(Target::class);
        $instance = new ConditionalIntercept(
            $this->fail(...),
            'foo',
            $target
        );

        $this->assertSame($target, $instance->target());
    }
}
