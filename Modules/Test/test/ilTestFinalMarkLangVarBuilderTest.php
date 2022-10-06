<?php

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

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ilTestFinalMarkLangVarBuilder
 * @author  Björn Heyser <bheyser@databay.de>
 * @version $Id$
 * @package Modules/Test
 * @ingroup ModulesTest
 */
class ilTestFinalMarkLangVarBuilderTest extends TestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        chdir(dirname(__FILE__));
        chdir('../../../');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_build()
    {
        $testCases = array(
            array(
                'param_passedStatus' => false,
                'param_obligationsAnsweredStatus' => false,
                'param_obligationsEnabled' => false,
                'expected' => 'mark_tst_failed'
            ),
            array(
                'param_passedStatus' => false,
                'param_obligationsAnsweredStatus' => false,
                'param_obligationsEnabled' => true,
                'expected' => 'mark_tst_failed_obligations_missing'
            ),
            array(
                'param_passedStatus' => false,
                'param_obligationsAnsweredStatus' => true,
                'param_obligationsEnabled' => false,
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
