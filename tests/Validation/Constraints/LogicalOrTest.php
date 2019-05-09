<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'libs/composer/vendor/autoload.php';

use ILIAS\Data;
use ILIAS\Refinery\Integer\Constraints\GreaterThan;
use ILIAS\Refinery\Integer\Constraints\LessThan;
use ILIAS\Refinery\Validation;
use ILIAS\Refinery\Validation\Constraints\LogicalOr;
use PHPUnit\Framework\TestCase;

/**
 * Class LogicalOrTest
 * @author  Michael Jansen <mjansen@databay.de>
 */
class LogicalOrTest extends TestCase
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
		$this->assertIsString($constraint->problemWith($errorValue));
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
	public function constraintsProvider(): array
	{
		$mock = $this->getMockBuilder(\ilLanguage::class)->disableOriginalConstructor()->getMock();
		$data_factory = new Data\Factory();
		$f = new Validation\Factory($data_factory, $mock);

		return [
			[$f->or([$f->isInt(), $f->isString()]), '5', []],
			[$f->or([new GreaterThan(5, $data_factory, $mock), new LessThan(2, $data_factory, $mock)]), 7, 3]
		];
	}
}
