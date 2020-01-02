<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'libs/composer/vendor/autoload.php';

use ILIAS\Data;
use ILIAS\Validation;
use ILIAS\Validation\Constraints\LogicalOr;

/**
 * Class LogicalOrTest
 * @author  Michael Jansen <mjansen@databay.de>
 */
class LogicalOrTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider constraintsProvider
     * @param LogicalOr $constraint
     * @param           $okValue
     * @param           $errorValue
     */
    public function testAccept(LogicalOr $constraint, $okValue, $errorValue)
    {
        $this->assertTrue($constraint->accepts($okValue));
        $this->assertFalse($constraint->accepts($errorValue));
    }

    /**
     * @dataProvider constraintsProvider
     * @param LogicalOr $constraint
     * @param           $okValue
     * @param           $errorValue
     */
    public function testCheck(LogicalOr $constraint, $okValue, $errorValue)
    {
        $raised = false;

        try {
            $constraint->check($errorValue);
        } catch (\UnexpectedValueException $e) {
            $raised = true;
        }

        $this->assertTrue($raised);

        try {
            $constraint->check($okValue);
            $raised = false;
        } catch (\UnexpectedValueException $e) {
            $raised = true;
        }

        $this->assertFalse($raised);
    }

    /**
     * @dataProvider constraintsProvider
     * @param LogicalOr $constraint
     * @param           $okValue
     * @param           $errorValue
     */
    public function testProblemWith(LogicalOr $constraint, $okValue, $errorValue)
    {
        $this->assertNull($constraint->problemWith($okValue));
        $this->assertInternalType('string', $constraint->problemWith($errorValue));
    }

    /**
     * @dataProvider constraintsProvider
     * @param LogicalOr $constraint
     * @param           $okValue
     * @param           $errorValue
     */
    public function testRestrict(LogicalOr $constraint, $okValue, $errorValue)
    {
        $rf    = new Data\Factory();
        $ok    = $rf->ok($okValue);
        $ok2   = $rf->ok($errorValue);
        $error = $rf->error('text');

        $result = $constraint->restrict($ok);
        $this->assertTrue($result->isOk());

        $result = $constraint->restrict($ok2);
        $this->assertTrue($result->isError());

        $result = $constraint->restrict($error);
        $this->assertSame($error, $result);
    }

    /**
     * @dataProvider constraintsProvider
     * @param LogicalOr $constraint
     * @param           $okValue
     * @param           $errorValue
     */
    public function testWithProblemBuilder(LogicalOr $constraint, $okValue, $errorValue)
    {
        $new_constraint = $constraint->withProblemBuilder(function () {
            return "This was a vault";
        });
        $this->assertEquals("This was a vault", $new_constraint->problemWith($errorValue));
    }

    /**
     * @return array
     */
    public function constraintsProvider() : array
    {
        $mock = $this->getMockBuilder(\ilLanguage::class)->disableOriginalConstructor()->getMock();
        $f = new Validation\Factory(new Data\Factory(), $mock);

        return [
            [$f->or([$f->isInt(), $f->isString()]), '5', []],
            [$f->or([$f->greaterThan(5), $f->lessThan(2)]), 7, 3]
        ];
    }
}
