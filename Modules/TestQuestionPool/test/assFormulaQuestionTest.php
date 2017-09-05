<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'libs/composer/vendor/autoload.php';

/** 
* Unit tests
* 
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assFormulaQuestionTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;

	/**
	 * @dataProvider simpleRatedCalcWithTwoVariablesData
	 * @param assFormulaQuestionResult     $result
	 * @param assFormulaQuestionVariable[] $variables
	 * @param assFormulaQuestionUnit[]     $results
	 * @param string                        $userResult
	 * @param assFormulaQuestionUnit|null  $userResultUnit
	 */
	public function testSimpleRatedFormulaQuestionWithTwoVariables(
		assFormulaQuestionResult $result, array $variables, array $results, $userResult, assFormulaQuestionUnit $userResultUnit = null
	)
	{
		$isCorrect = $result->isCorrect($variables, $results, $userResult, $userResultUnit);

		$this->assertTrue($isCorrect);
	}

	/**
	 *
	 */
	public function simpleRatedCalcWithTwoVariablesData()
	{
		$points    = 5;
		$precision = 2;

		$meter = new assFormulaQuestionUnit();
		$meter->initFormArray([
			'unit_id'        => 1,
			'category'       => 1,
			'sequence'       => 1,
			'unit'           => 'Meter',
			'factor'         => 1,
			'baseunit_fi'    => -1,
			'baseunit_title' => ''
		]);

		$centimeter = new assFormulaQuestionUnit();
		$centimeter->initFormArray([
			'unit_id'        => 2,
			'category'       => 1,
			'sequence'       => 2,
			'unit'           => 'Centimeter',
			'factor'         => 0.01,
			'baseunit_fi'    => 1,
			'baseunit_title' => 'Meter'
		]);

		$newtonmetre = new assFormulaQuestionUnit();
		$newtonmetre->initFormArray([
			'unit_id'        => 3,
			'category'       => 1,
			'sequence'       => 3,
			'unit'           => 'Newton Metre',
			'factor'         => 1,
			'baseunit_fi'    => -1,
			'baseunit_title' => ''
		]);

		$newtoncentimeter = new assFormulaQuestionUnit();
		$newtoncentimeter->initFormArray([
			'unit_id'        => 4,
			'category'       => 1,
			'sequence'       => 4,
			'unit'           => 'Newton Centimeter',
			'factor'         => 0.01,
			'baseunit_fi'    => 3,
			'baseunit_title' => 'Newton Metre'
		]);

		$v1 = new assFormulaQuestionVariable('$v1', 1, 20, $newtonmetre, 1);
		$v2 = new assFormulaQuestionVariable('$v2', 1, 10, $centimeter, 1);
		$v1->setValue(19.6);
		$v1->setIntprecision(1);
		$v2->setValue(6.6);
		$v2->setIntprecision(1);

		$v3 = clone $v1;
		$v4 = clone $v2;
		$v3->setUnit(null);
		$v3->setVariable('$v3');
		$v4->setUnit(null);
		$v4->setVariable('$v4');

		$r1 = new assFormulaQuestionResult(
			'$r1', 0, 0, 0, $newtoncentimeter, '$v1 * $v2', $points, $precision, true, 33, 34, 33, assFormulaQuestionResult::RESULT_DEC
		);
		$r2 = new assFormulaQuestionResult(
			'$r2', 0, 0, 0, $newtonmetre, '$v1 * $v2', $points, $precision, true, 33, 34, 33, assFormulaQuestionResult::RESULT_DEC
		);
		$r3 = new assFormulaQuestionResult(
			'$r3', 0, 0, 0, null, '$v1 * $v2', $points, $precision, true, 33, 34, 33, assFormulaQuestionResult::RESULT_DEC
		);
		$r4 = new assFormulaQuestionResult(
			'$r4', 0, 0, 0, null, '$v1 * $v2', $points, $precision, true, 33, 34, 33, assFormulaQuestionResult::RESULT_DEC
		);
		$r5 = new assFormulaQuestionResult(
			'$r5', 0, 0, 0, null, '$v1 * $v2', $points, $precision, true, 33, 34, 33, assFormulaQuestionResult::RESULT_DEC
		);
		$r6 = new assFormulaQuestionResult(
			'$r6', 0, 0, 0, null, '$v3 * $v4', $points, $precision, true, 33, 34, 33, assFormulaQuestionResult::RESULT_DEC
		);

		$variables = [
			$v1->getVariable() => $v1,
			$v2->getVariable() => $v2,
			$v3->getVariable() => $v3,
			$v4->getVariable() => $v4
		];

		$results = [
			$r1->getResult() => $r1,
			$r2->getResult() => $r2,
			$r3->getResult() => $r3,
			$r4->getResult() => $r4,
			$r5->getResult() => $r5,
			$r6->getResult() => $r6
		];

		return [
			[$r1, $variables, $results, '129.36', $newtoncentimeter],
			[$r2, $variables, $results, '1.29', $newtonmetre],
			[$r3, $variables, $results, '1.29', $newtonmetre],
			[$r4, $variables, $results, '129.36', $newtoncentimeter],
			[$r5, $variables, $results, '1.29'],
			[$r6, $variables, $results, '129.36']
		];
	}
}
