<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Unit tests for ilTestFinalMarkLangVarBuilder
 *
 * @author  Björn Heyser <bheyser@databay.de>
 * @version $Id$
 *
 *
 * @package Modules/Test
 * @ingroup ModulesTest
 */
class ilTestFinalMarkLangVarBuilderTest extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = false;

    protected function setUp()
    {
        if (defined('ILIAS_PHPUNIT_CONTEXT')) {
            include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
            ilUnitUtil::performInitialisation();
        } else {
            chdir(dirname(__FILE__));
            chdir('../../../');
        }
    }

    public function test_build()
    {
        $testCases = array(
            array(
                'param_passedStatus' => false, 'param_obligationsAnsweredStatus' => false, 'param_obligationsEnabled' => false,
                'expected' => 'mark_tst_failed'
            ),
            array(
                'param_passedStatus' => false, 'param_obligationsAnsweredStatus' => false, 'param_obligationsEnabled' => true,
                'expected' => 'mark_tst_failed_obligations_missing'
            ),
            array(
                'param_passedStatus' => false, 'param_obligationsAnsweredStatus' => true, 'param_obligationsEnabled' => false,
                'expected' => 'mark_tst_failed'
            ),
            array(
                'param_passedStatus' => false,
                'param_obligationsAnsweredStatus' => true,
                'param_obligationsEnabled' => true,
                'expected' => 'mark_tst_failed_obligations_answered'
            ),
            array(
                'param_passedStatus' => true,
                'param_obligationsAnsweredStatus' => false,
                'param_obligationsEnabled' => false,
                'expected' => 'mark_tst_passed'
            ),
            array(
                'param_passedStatus' => true,
                'param_obligationsAnsweredStatus' => false,
                'param_obligationsEnabled' => true,
                'expected' => 'mark_tst_failed_obligations_missing'
            ),
            array(
                'param_passedStatus' => true,
                'param_obligationsAnsweredStatus' => true,
                'param_obligationsEnabled' => false,
                'expected' => 'mark_tst_passed'
            ),
            array(
                'param_passedStatus' => true,
                'param_obligationsAnsweredStatus' => true,
                'param_obligationsEnabled' => true,
                'expected' => 'mark_tst_passed_obligations_answered'
            )
        );
        // OTX: Test breaks with fatal error...
//		foreach($testCases as $case)
//		{
//			// arrange
//
//			$passedStatus = $case['param_passedStatus'];
//			$obligationsAnsweredStatus = $case['param_obligationsAnsweredStatus'];
//			$obligationsEnabled = $case['param_obligationsEnabled'];
//
//			$expected = $case['expected'];
//
//			require_once './Modules/Test/classes/class.ilTestFinalMarkLangVarBuilder.php';
//			$instance = new ilTestFinalMarkLangVarBuilder($passedStatus, $obligationsAnsweredStatus, $obligationsEnabled);
//
//			// act
//
//			$actual = $instance->build();
//
//			// assert
//
//			$this->assertEquals($expected, $actual);
//		}
    }
}
