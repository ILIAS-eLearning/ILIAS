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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Logical\LogicalOr;
use PHPUnit\Framework\TestCase;

class LogicalOrTest extends TestCase
{
    /**
     * @dataProvider constraintsProvider
     * @param LogicalOr $constraint
     * @param mixed $okValue
     * @param mixed $errorValue
     */
    public function testAccept(LogicalOr $constraint, $okValue, $errorValue) : void
    {
        $this->assertTrue($constraint->accepts($okValue));
        $this->assertFalse($constraint->accepts($errorValue));
    }

    /**
     * @dataProvider constraintsProvider
     * @param LogicalOr $constraint
     * @param mixed $okValue
     * @param mixed $errorValue
     */
    public function testCheck(LogicalOr $constraint, $okValue, $errorValue) : void
    {
        $raised = false;

        try {
            $constraint->check($errorValue);
        } catch (UnexpectedValueException $e) {
            $raised = true;
        }

        $this->assertTrue($raised);

        try {
            $constraint->check($okValue);
            $raised = false;
        } catch (UnexpectedValueException $e) {
            $raised = true;
        }

        $this->assertFalse($raised);
    }

    /**
     * @dataProvider constraintsProvider
     * @param LogicalOr $constraint
     * @param mixed $okValue
     * @param mixed $errorValue
     */
    public function testProblemWith(LogicalOr $constraint, $okValue, $errorValue) : void
    {
        $this->assertNull($constraint->problemWith($okValue));
        $this->assertIsString($constraint->problemWith($errorValue));
    }

    /**
     * @dataProvider constraintsProvider
     * @param LogicalOr $constraint
     * @param mixed $okValue
     * @param mixed $errorValue
     */
    public function testRestrict(LogicalOr $constraint, $okValue, $errorValue) : void
    {
        $rf = new DataFactory();
        $ok = $rf->ok($okValue);
        $ok2 = $rf->ok($errorValue);
        $error = $rf->error('text');

        $result = $constraint->applyTo($ok);
        $this->assertTrue($result->isOk());

        $result = $constraint->applyTo($ok2);
        $this->assertTrue($result->isError());

        $result = $constraint->applyTo($error);
        $this->assertSame($error, $result);
    }

    /**
     * @dataProvider constraintsProvider
     * @param LogicalOr $constraint
     * @param mixed $okValue
     * @param mixed $errorValue
     */
    public function testWithProblemBuilder(LogicalOr $constraint, $okValue, $errorValue) : void
    {
        $new_constraint = $constraint->withProblemBuilder(static function () : string {
            return "This was a vault";
        });
        $this->assertEquals("This was a vault", $new_constraint->problemWith($errorValue));
    }

    /**
     * @return array
     */
    public function constraintsProvider() : array
    {
        $mock = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $data_factory = new DataFactory();

        $refinery = new Refinery($data_factory, $mock);
        return [
            [
                $refinery->logical()->logicalOr([
                    $refinery->int()->isLessThan(6),
                    $refinery->int()->isGreaterThan(100)
                ]),
                '5',
                8
            ],
            [
                $refinery->logical()->logicalOr([$refinery->int()->isGreaterThan(5), $refinery->int()->isLessThan(2)]),
                7,
                3
            ]
        ];
    }
}
