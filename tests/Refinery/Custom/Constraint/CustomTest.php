<?php declare(strict_types=1);

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

use ILIAS\Refinery\Custom\Constraint as CustomConstraint;
use PHPUnit\Framework\TestCase;
use ILIAS\Data\Factory as DataFactory;

class MyValidationConstraintsConstraint extends CustomConstraint
{
    public function _getLngClosure() : Closure
    {
        return $this->getLngClosure();
    }
}

class MyToStringClass
{
    private string $str_repr = '';

    public function __toString() : string
    {
        return $this->str_repr;
    }
}

class ValidationConstraintsCustomTest extends TestCase
{
    private string $txt_id = '';
    private ilLanguage $lng;
    private MyValidationConstraintsConstraint $constraint;

    protected function setUp() : void
    {
        $is_ok = static function ($value) : bool {
            return false;
        };
        $this->txt_id = "TXT_ID";
        $error = function (callable $txt, $value) : string {
            return $txt($this->txt_id, $value);
        };
        $this->lng = $this->createMock(ilLanguage::class);
        $this->constraint = new MyValidationConstraintsConstraint($is_ok, $error, new DataFactory(), $this->lng);
    }

    public function testWithProblemBuilder() : void
    {
        $new_constraint = $this->constraint->withProblemBuilder(static function () : string {
            return "This was a fault";
        });
        $this->assertEquals("This was a fault", $new_constraint->problemWith(""));
    }

    public function testProblemBuilderRetrievesLngClosure() : void
    {
        $cls = null;
        $c = $this->constraint->withProblemBuilder(function ($txt) use (&$cls) : string {
            $cls = $txt;
            return "";
        });
        $c->problemWith("");
        $this->assertIsCallable($cls);
    }

    public function test_use_txt() : void
    {
        $txt_out = "'%s'";
        $this->lng
            ->expects($this->once())
            ->method("txt")
            ->with($this->txt_id)
            ->willReturn($txt_out);

        $value = "VALUE";
        $problem = $this->constraint->problemWith($value);

        $this->assertEquals(sprintf($txt_out, $value), $problem);
    }

    public function test_exception_on_no_parameter() : void
    {
        $lng_closure = $this->constraint->_getLngClosure();

        $this->expectException(InvalidArgumentException::class);

        $lng_closure();
    }

    public function test_no_sprintf_on_one_parameter() : void
    {
        $lng_closure = $this->constraint->_getLngClosure();

        $txt_out = "txt";
        $this->lng
            ->expects($this->once())
            ->method("txt")
            ->with($this->txt_id)
            ->willReturn($txt_out);

        $res = $lng_closure($this->txt_id);

        $this->assertEquals($txt_out, $res);
    }

    public function test_gracefully_handle_arrays_and_objects() : void
    {
        $lng_closure = $this->constraint->_getLngClosure();

        $this->lng
            ->expects($this->once())
            ->method("txt")
            ->with("id")
            ->willReturn("%s-%s-%s-%s-");

        $to_string = new MyToStringClass();

        $res = $lng_closure("id", [], new stdClass(), "foo", null);

        $this->assertEquals("array-" . stdClass::class . "-foo-null-", $res);
    }
}
