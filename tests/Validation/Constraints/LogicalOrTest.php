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
		$this->assertFalse($constraint->accepts($okValue));
		$this->assertTrue($constraint->accepts($errorValue));
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
			$constraint->check($okValue);
		} catch (\UnexpectedValueException $e) {
			$raised = true;
		}

		$this->assertTrue($raised);

		try {
			$constraint->check($errorValue);
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
		$this->assertNull($constraint->problemWith($errorValue));
		$this->assertInternalType('string', $constraint->problemWith($okValue));
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
		$ok    = $rf->ok($errorValue);
		$ok2   = $rf->ok($okValue);
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
		$this->assertEquals("This was a vault", $new_constraint->problemWith($okValue));
	}

	/**
	 * @return array
	 */
	public function constraintsProvider(): array
	{
		$f = new Validation\Factory(new Data\Factory());

		return [
			[$f->or([$f->isInt(), $f->isString()]), '5', []],
			[$f->or([$f->greaterThan(5), $f->lessThan(2)]), 3, 7]
		];
	}
}