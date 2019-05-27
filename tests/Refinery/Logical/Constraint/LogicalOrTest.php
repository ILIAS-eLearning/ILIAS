<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'libs/composer/vendor/autoload.php';

use ILIAS\Data;
use ILIAS\Refinery\Logical\LogicalOr;
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

		$refinery = new \ILIAS\Refinery\Factory($data_factory, $mock);
		return [
			[$refinery->logical()->logicalOr([$refinery->int()->isLessThan(6), $refinery->int()->isGreaterThan(100)]), '5', 8],
			[$refinery->logical()->logicalOr([$refinery->int()->isGreaterThan(5), $refinery->int()->isLessThan(2)]), 7, 3]
		];
	}
}
