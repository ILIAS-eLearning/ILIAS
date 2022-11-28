<?php

declare(strict_types=1);

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

namespace ILIAS\Tests\Refinery\Parser\ABNF;

use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use PHPUnit\Framework\TestCase;
use ILIAS\Refinery\Parser\ABNF\Transform;
use ILIAS\Refinery\Parser\ABNF\Intermediate;
use ILIAS\Refinery\Transformation;
use Closure;
use stdClass;

class TransformTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(Transform::class, new Transform());
    }

    /**
     * @depends testConstruct
     */
    public function testTo(): void
    {
        $transform = new Transform();
        $transformed = new stdClass();
        $transformation = $this->getMockBuilder(Transformation::class)->getMock();
        $transformation->method('applyTo')->willReturnCallback(function (Result $x) use ($transformed): Result {
            $this->assertEquals('x', $x->value());
            return new Ok($transformed);
        });

        $parse = $transform->to($transformation, function (Intermediate $x, Closure $cc): Result {
            return $cc($x->accept());
        });

        $end = new stdClass();

        $x = $parse(new Intermediate('x'), function (Result $x) use ($transformed, $end): Result {
            $this->assertEquals([$transformed], $x->value()->accepted());
            return new Ok($end);
        });

        $this->assertEquals($end, $x->value());
    }

    /**
     * @depends testConstruct
     */
    public function testFailedToTransform(): void
    {
        $transform = new Transform();
        $transformation = $this->getMockBuilder(Transformation::class)->getMock();
        $transformation->method('applyTo')->willReturnCallback(function (Result $x): Result {
            $this->assertEquals('x', $x->value());
            return new Error('Sorryy.');
        });

        $parse = $transform->to($transformation, function (Intermediate $x, Closure $cc): Result {
            return $cc($x->accept());
        });

        $end = new stdClass();

        $x = $parse(new Intermediate('x'), function (Result $x) use ($end): Result {
            $this->assertFalse($x->isOK());
            return new Ok($end);
        });

        $this->assertEquals($end, $x->value());
    }

    /**
     * @depends testConstruct
     */
    public function testFailedToParse(): void
    {
        $transform = new Transform();
        $transformCalled = false;
        $transformation = $this->getMockBuilder(Transformation::class)->getMock();
        $transformation->method('applyTo')->willReturnCallback(function (Result $x) use (&$transformCalled): Result {
            $transformCalled = true;
            return $x;
        });

        $parse = $transform->to($transformation, function (Intermediate $x, Closure $cc): Result {
            return $cc($x->reject());
        });

        $end = new stdClass();

        $x = $parse(new Intermediate('x'), function (Result $x) use ($end): Result {
            $this->assertFalse($x->isOK());
            return new Ok($end);
        });

        $this->assertEquals($end, $x->value());
        $this->assertFalse($transformCalled);
    }

    public function testAs(): void
    {
        $transform = new Transform();
        $parse = $transform->as('hej', function (Intermediate $x, Closure $cc): Result {
            return $cc($x->accept());
        });

        $result = $parse(new Intermediate('lorem'), static function (Result $x): Result {
            return $x->map(static fn (Intermediate $x): array => $x->accepted());
        });

        $this->assertTrue($result->isOK());
        $this->assertEquals(['hej' => 'l'], $result->value());
    }
}
