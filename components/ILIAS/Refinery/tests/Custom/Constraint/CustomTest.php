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

use ILIAS\Refinery\Custom\Constraint as CustomConstraint;
use PHPUnit\Framework\TestCase;
use ILIAS\Data\Factory as DataFactory;

class CustomTest extends TestCase
{
    private string $txt_id = '';
    private CustomConstraint $constraint;

    protected function setUp(): void
    {
        $this->txt_id = 'TXT_ID';
        $this->constraint = $this->newConstraint($this->createStub(ILIAS\Language\Language::class));
    }

    public function testWithProblemBuilder(): void
    {
        $new_constraint = $this->constraint->withProblemBuilder(static function (): string {
            return 'This was a fault';
        });
        $this->assertEquals('This was a fault', $new_constraint->problemWith(''));
    }

    public function testProblemBuilderRetrievesLngClosure(): void
    {
        $cls = null;
        $c = $this->constraint->withProblemBuilder(function ($txt) use (&$cls): string {
            $cls = $txt;
            return '';
        });
        $c->problemWith('');
        $this->assertIsCallable($cls);
    }

    public function test_use_txt(): void
    {
        $txt_out = "'%s'";
        $lng = $this->createMock(ILIAS\Language\Language::class);
        $lng
            ->expects($this->once())
            ->method('txt')
            ->with($this->txt_id)
            ->willReturn($txt_out);

        $value = 'VALUE';
        $problem = $this->newConstraint($lng)->problemWith($value);

        $this->assertEquals(sprintf($txt_out, $value), $problem);
    }

    public function test_exception_on_no_parameter(): void
    {
        $lng_closure = $this->constraint->_getLngClosure();

        $this->expectException(ArgumentCountError::class);

        $lng_closure();
    }

    public function test_no_sprintf_on_one_parameter(): void
    {
        $txt_out = 'txt';
        $lng = $this->createMock(ILIAS\Language\Language::class);
        $lng
            ->expects($this->once())
            ->method('txt')
            ->with($this->txt_id)
            ->willReturn($txt_out);

        $lng_closure = $this->newConstraint($lng)->_getLngClosure();
        $res = $lng_closure($this->txt_id);

        $this->assertEquals($txt_out, $res);
    }

    public function test_gracefully_handle_arrays_and_objects(): void
    {
        $lng = $this->createMock(ILIAS\Language\Language::class);
        $lng
            ->expects($this->once())
            ->method('txt')
            ->with('id')
            ->willReturn('%s-%s-%s-%s-');

        $lng_closure = $this->newConstraint($lng)->_getLngClosure();
        $res = $lng_closure('id', [], new stdClass(), 'foo', null);

        $this->assertEquals('array-' . stdClass::class . '-foo-null-', $res);
    }

    private function newConstraint(ILIAS\Language\Language $lng): CustomConstraint
    {
        $is_ok = static fn($value): bool => false;
        $error = fn(callable $txt, $value): string => $txt($this->txt_id, $value);

        return new class ($is_ok, $error, $this->createStub(DataFactory::class), $lng) extends CustomConstraint {
            public function _getLngClosure(): Closure
            {
                return $this->getLngClosure();
            }
        };
    }
}
