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
		$meter->initFormArray(array(
			'unit_id'        => 1,
			'category'       => 1,
			'sequence'       => 1,
			'unit'           => 'Meter',
			'factor'         => 1,
			'baseunit_fi'    => -1,
			'baseunit_title' => ''
		));

		$centimeter = new assFormulaQuestionUnit();
		$centimeter->initFormArray(array(
			'unit_id'        => 2,
			'category'       => 1,
			'sequence'       => 2,
			'unit'           => 'Centimeter',
			'factor'         => 0.01,
			'baseunit_fi'    => 1,
			'baseunit_title' => 'Meter'
		));

		$v1 = new assFormulaQuestionVariable('$v1', 10, 10, $meter, $precision);
		$v2 = new assFormulaQuestionVariable('$v2', 2, 2, $centimeter, $precision);
		$v1->setValue(10);
		$v2->setValue(2);

		$r1 = new assFormulaQuestionResult(
			'$r1', 0, 0, 0, $centimeter, '$v1 + $v2', $points, $precision, true, 33, 34, 33, assFormulaQuestionResult::RESULT_DEC
		);
		$r2 = new assFormulaQuestionResult(
			'$r2', 0, 0, 0, $meter, '$v1 + $v2', $points, $precision, true, 33, 34, 33, assFormulaQuestionResult::RESULT_DEC
		);
		$r3 = new assFormulaQuestionResult(
			'$r3', 0, 0, 0, null, '$v1 + $v2', $points, $precision, true, 33, 34, 33, assFormulaQuestionResult::RESULT_DEC
		);
		$r4 = new assFormulaQuestionResult(
			'$r4', 0, 0, 0, null, '$v1 + $v2', $points, $precision, true, 33, 34, 33, assFormulaQuestionResult::RESULT_DEC
		);
		$r5 = new assFormulaQuestionResult(
			'$r5', 0, 0, 0, null, '$v1 + $v2', $points, $precision, true, 33, 34, 33, assFormulaQuestionResult::RESULT_DEC
		);

		$variables = [
			$v1->getVariable() => $v1,
			$v2->getVariable() => $v2
		];

		$results = [
			$r1->getResult() => $r1,
			$r2->getResult() => $r2,
			$r3->getResult() => $r3,
			$r4->getResult() => $r4,
			$r5->getResult() => $r5
		];

		return [
			[$r1, $variables, $results, '1002.00', $centimeter],
			[$r2, $variables, $results, '10.02', $meter],
			[$r3, $variables, $results, '10.02', $meter],
			[$r4, $variables, $results, '1002.00', $centimeter],
			[$r5, $variables, $results, '10.02']
		];
	}
}
