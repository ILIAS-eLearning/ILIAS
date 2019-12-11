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
    protected $backupGlobals = false;

    /**
     * @dataProvider simpleRatedCalculationsData
     * @param assFormulaQuestionResult     $result
     * @param assFormulaQuestionVariable[] $variables
     * @param assFormulaQuestionUnit[]     $results
     * @param string                       $userResult
     * @param assFormulaQuestionUnit|null  $userResultUnit
     * @param bool                         $expectedResult
     */
    public function testSimpleRatedFormulaQuestionCalculations(
        assFormulaQuestionResult $result,
        array $variables,
        array $results,
        $userResult,
        $userResultUnit,
        $expectedResult
    ) {
        $isCorrect = $result->isCorrect($variables, $results, $userResult, $userResultUnit);
        $this->assertEquals($expectedResult, $isCorrect);
    }

    /**
     *
     */
    public function simpleRatedCalculationsData()
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
            '$r1',
            0,
            0,
            0,
            $newtoncentimeter,
            '$v1 * $v2',
            $points,
            $precision,
            true,
            33,
            34,
            33,
            assFormulaQuestionResult::RESULT_DEC
        );
        $r2 = new assFormulaQuestionResult(
            '$r2',
            0,
            0,
            0,
            $newtonmetre,
            '$v1 * $v2',
            $points,
            $precision,
            true,
            33,
            34,
            33,
            assFormulaQuestionResult::RESULT_DEC
        );
        $r3 = new assFormulaQuestionResult(
            '$r3',
            0,
            0,
            0,
            null,
            '$v1 * $v2',
            $points,
            $precision,
            true,
            33,
            34,
            33,
            assFormulaQuestionResult::RESULT_DEC
        );
        $r4 = new assFormulaQuestionResult(
            '$r4',
            0,
            0,
            0,
            null,
            '$v1 * $v2',
            $points,
            $precision,
            true,
            33,
            34,
            33,
            assFormulaQuestionResult::RESULT_DEC
        );
        $r5 = new assFormulaQuestionResult(
            '$r5',
            0,
            0,
            0,
            null,
            '$v1 * $v2',
            $points,
            $precision,
            true,
            33,
            34,
            33,
            assFormulaQuestionResult::RESULT_DEC
        );
        $r6 = new assFormulaQuestionResult(
            '$r6',
            0,
            0,
            0,
            null,
            '$v3 * $v4',
            $points,
            $precision,
            true,
            33,
            34,
            33,
            assFormulaQuestionResult::RESULT_DEC
        );
        
        // RESULT_FRAC
        $v5 = new assFormulaQuestionVariable('$v5', 1, 20, null, 1);
        $v6 = new assFormulaQuestionVariable('$v6', 1, 10, null, 1);
        $v5->setValue(1);
        $v6->setValue(3);
        
        $v7 = new assFormulaQuestionVariable('$v7', 1, 20, null, 1);
        $v8 = new assFormulaQuestionVariable('$v8', 1, 10, null, 1);
        $v7->setValue(2);
        $v8->setValue(4);
        
        $r7 = new assFormulaQuestionResult(
            '$r7',
            0,
            0,
            0,
            null,
            '$v5/$v6',
            $points,
            $precision,
            true,
            33,
            34,
            33,
            assFormulaQuestionResult::RESULT_FRAC
        );
        
        $r8 = new assFormulaQuestionResult(
            '$r8',
            0,
            0,
            0,
            null,
            '$v7/$v8',
            $points,
            $precision,
            true,
            33,
            34,
            33,
            assFormulaQuestionResult::RESULT_FRAC
        );
        
        // RESULT_CO_FRAC
        $v9 = clone $v7;
        $v9->setVariable('$v9');
        $v10 = clone $v8;
        $v10->setVariable('$v10');
        
        $v11 = clone $v7;
        $v11->setVariable('$v11');
        $v12 = clone $v8;
        $v12->setVariable('$v12');
        
        $r9 = new assFormulaQuestionResult(
            '$r9',
            0,
            0,
            0,
            null,
            '$v9/$v10',
            $points,
            $precision,
            true,
            33,
            34,
            33,
            assFormulaQuestionResult::RESULT_CO_FRAC
        );
        
        $r10 = new assFormulaQuestionResult(
            '$r10',
            0,
            0,
            0,
            null,
            '$v11/$v12',
            $points,
            $precision,
            true,
            33,
            34,
            33,
            assFormulaQuestionResult::RESULT_CO_FRAC
        );

        // RESULT_NO_SELECTION
        $v13 = new assFormulaQuestionVariable('$v13', 1, 20, null, 1);
        $v14 = new assFormulaQuestionVariable('$v14', 1, 10, null, 1);
        $v13->setValue(1);
        $v14->setValue(3);
        $r11 = new assFormulaQuestionResult(
            '$r11',
            0,
            0,
            0,
            null,
            '$v13/$v14',
            $points,
            $precision,
            true,
            33,
            34,
            33,
            assFormulaQuestionResult::RESULT_NO_SELECTION
        );

        $v15 = new assFormulaQuestionVariable('$v15', 200, 800, null, 0);
        $v16 = new assFormulaQuestionVariable('$v16', 50, 250, null, 0);
        $v17 = new assFormulaQuestionVariable('$v17', 3, 18, null, 0);
        $v15->setIntprecision(25);
        $v16->setIntprecision(5);
        $v17->setIntprecision(1);
        $v15->setValue(225);
        $v16->setValue(85);
        $v17->setValue(10);
        $r12 = new assFormulaQuestionResult(
            '$r12',
            0,
            0,
            0,
            null,
            '1/(2*pi)*sqrt($v16*1000/$v15)+$v17-$v17',
            $points,
            1,
            true,
            33,
            34,
            33,
            assFormulaQuestionResult::RESULT_NO_SELECTION
        );

        $variables = [
            $v1->getVariable() => $v1,
            $v2->getVariable() => $v2,
            $v3->getVariable() => $v3,
            $v4->getVariable() => $v4,
            $v5->getVariable() => $v5,
            $v6->getVariable() => $v6,
            $v7->getVariable() => $v7,
            $v8->getVariable() => $v8,
            $v9->getVariable() => $v9,
            $v10->getVariable() => $v10,
            $v11->getVariable() => $v11,
            $v12->getVariable() => $v12,
            $v13->getVariable() => $v13,
            $v14->getVariable() => $v14,
            $v15->getVariable() => $v15,
            $v16->getVariable() => $v16,
            $v17->getVariable() => $v17,
        ];

        $results = [
            $r1->getResult() => $r1,
            $r2->getResult() => $r2,
            $r3->getResult() => $r3,
            $r4->getResult() => $r4,
            $r5->getResult() => $r5,
            $r6->getResult() => $r6,
            $r7->getResult() => $r7,
            $r8->getResult() => $r8,
            $r9->getResult() => $r9,
            $r10->getResult() => $r10,
            $r11->getResult() => $r11,
            $r12->getResult() => $r12,
        ];

        return [
            //result, all variables, all results, user solution, unit chosen by user for solution
            [$r1, $variables, $results, '129.36', $newtoncentimeter, true],
            [$r2, $variables, $results, '1.29', $newtonmetre, true],
            [$r3, $variables, $results, '1.29', $newtonmetre, true],
            [$r4, $variables, $results, '129.36', $newtoncentimeter, true],
            [$r5, $variables, $results, '1.29', null, true],
            [$r6, $variables, $results, '129.36', null, true],
            // RESULT_FRAC
            [$r7, $variables, $results, '1/3', null, true],
            [$r8, $variables, $results, '4/8', null, true],
            // RESULT_CO_FRAC
            [$r9, $variables, $results, '1/2', null, true],
            [$r10, $variables, $results, '4/8', null, false],
            // RESULT_NO_SELECTION
            [$r11, $variables, $results, '1/3', null, true],
            // Test for #22381
            [$r12, $variables, $results, '3.1', null, true],
        ];
    }
}
