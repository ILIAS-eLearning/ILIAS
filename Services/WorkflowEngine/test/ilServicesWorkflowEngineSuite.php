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

use PHPUnit\Framework\TestSuite;

require_once __DIR__ . '/bootstrap.php';

$GLOBALS["DIC"] = new \ILIAS\DI\Container();

/**
 * ilServicesWorkflowEngineSuite is part of the petri net based workflow engine.
 *
 * This class collects all unit tests/suites for the workflow engine and returns
 * it's insights to the Ilias test suite generator.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilServicesWorkflowEngineSuite extends TestSuite
{
    public static function suite() : ilServicesWorkflowEngineSuite
    {
        error_reporting(E_ALL ^ E_NOTICE);

        chdir(__DIR__);
        chdir('../../../');

        $suite = new ilServicesWorkflowEngineSuite();

        //
        // ---------------------------------------------------------------------
        // Activities
        // ---------------------------------------------------------------------
        //

        require_once './Services/WorkflowEngine/test/activities/ilEventRaisingActivityTest.php';
        $suite->addTestSuite(ilEventRaisingActivityTest::class);

        require_once './Services/WorkflowEngine/test/activities/ilLoggingActivityTest.php';
        $suite->addTestSuite(ilLoggingActivityTest::class);

        require_once './Services/WorkflowEngine/test/activities/ilScriptActivityTest.php';
        $suite->addTestSuite(ilScriptActivityTest::class);

        require_once './Services/WorkflowEngine/test/activities/ilSettingActivityTest.php';
        $suite->addTestSuite(ilSettingActivityTest::class);

        require_once './Services/WorkflowEngine/test/activities/ilStaticMethodCallActivityTest.php';
        $suite->addTestSuite(ilStaticMethodCallActivityTest::class);

        require_once './Services/WorkflowEngine/test/activities/ilStopWorkflowActivityTest.php';
        $suite->addTestSuite(ilStopWorkflowActivityTest::class);

        //
        // ---------------------------------------------------------------------
        // Detectors
        // ---------------------------------------------------------------------
        //

        require_once './Services/WorkflowEngine/test/detectors/ilCounterDetectorTest.php';
        $suite->addTestSuite(ilCounterDetectorTest::class);

        require_once './Services/WorkflowEngine/test/detectors/ilSimpleDetectorTest.php';
        $suite->addTestSuite(ilSimpleDetectorTest::class);

        require_once './Services/WorkflowEngine/test/detectors/ilTimerDetectorTest.php';
        $suite->addTestSuite(ilTimerDetectorTest::class);

        require_once './Services/WorkflowEngine/test/detectors/ilEventDetectorTest.php';
        $suite->addTestSuite(ilEventDetectorTest::class);

        require_once './Services/WorkflowEngine/test/detectors/ilDataDetectorTest.php';
        $suite->addTestSuite(ilDataDetectorTest::class);

        //
        // ---------------------------------------------------------------------
        // Emitters
        // ---------------------------------------------------------------------
        //

        require_once './Services/WorkflowEngine/test/emitters/ilSimpleEmitterTest.php';
        $suite->addTestSuite(ilSimpleEmitterTest::class);

        require_once './Services/WorkflowEngine/test/emitters/ilActivationEmitterTest.php';
        $suite->addTestSuite(ilActivationEmitterTest::class);

        require_once './Services/WorkflowEngine/test/emitters/ilDataEmitterTest.php';
        $suite->addTestSuite(ilDataEmitterTest::class);

        //
        // ---------------------------------------------------------------------
        // Nodes
        // ---------------------------------------------------------------------
        //

        require_once './Services/WorkflowEngine/test/nodes/ilBasicNodeTest.php';
        $suite->addTestSuite(ilBasicNodeTest::class);

        require_once './Services/WorkflowEngine/test/nodes/ilConditionalNodeTest.php';
        $suite->addTestSuite(ilConditionalNodeTest::class);

        require_once './Services/WorkflowEngine/test/nodes/ilCaseNodeTest.php';
        $suite->addTestSuite(ilCaseNodeTest::class);

        require_once './Services/WorkflowEngine/test/nodes/ilPluginNodeTest.php';
        $suite->addTestSuite(ilPluginNodeTest::class);

        //
        // ---------------------------------------------------------------------
        // Workflows
        // ---------------------------------------------------------------------
        //

        // ---------------------------------------------------------------------
        // Utils
        // ---------------------------------------------------------------------

        //
        // ---------------------------------------------------------------------
        // Parser - Components
        // ---------------------------------------------------------------------

        // 001_EmptyWorkflow
        require_once './Services/WorkflowEngine/test/parser/001_EmptyWorkflow/class.test_001_EmptyWorkflow.php';
        $suite->addTestSuite(test_001_EmptyWorkflow::class);

        // 002_StartNode
        require_once './Services/WorkflowEngine/test/parser/002_StartEvent/class.test_002_StartEvent.php';
        $suite->addTestSuite(test_002_StartEvent::class);

        // 003_ParallelGateway
        require_once './Services/WorkflowEngine/test/parser/003_ParallelGateway/class.test_003_ParallelGateway.php';
        $suite->addTestSuite(test_003_ParallelGateway::class);

        // 004_InclusiveGateway
        require_once './Services/WorkflowEngine/test/parser/004_InclusiveGateway/class.test_004_InclusiveGateway.php';
        $suite->addTestSuite(test_004_InclusiveGateway::class);

        // 005_ExclusiveGateway
        require_once './Services/WorkflowEngine/test/parser/005_ExclusiveGateway/class.test_005_ExclusiveGateway.php';
        $suite->addTestSuite(test_005_ExclusiveGateway::class);

        // 006_Task
        require_once './Services/WorkflowEngine/test/parser/006_Task/class.test_006_Task.php';
        $suite->addTestSuite(test_006_Task::class);

        // 007_IntermediateCatchEvent
        require_once './Services/WorkflowEngine/test/parser/007_IntermediateCatchEvent/class.test_007_IntermediateCatchEvent.php';
        $suite->addTestSuite(test_007_IntermediateCatchEvent::class);

        // 008_IntermediateThrowEvent
        require_once './Services/WorkflowEngine/test/parser/008_IntermediateThrowEvent/class.test_008_IntermediateThrowEvent.php';
        $suite->addTestSuite(test_008_IntermediateThrowEvent::class);

        // 009_EndEvent
        require_once './Services/WorkflowEngine/test/parser/009_EndEvent/class.test_009_EndEvent.php';
        $suite->addTestSuite(test_009_EndEvent::class);

        // 012_DataInput
        require_once './Services/WorkflowEngine/test/parser/012_DataInput/class.test_012_DataInput.php';
        $suite->addTestSuite(test_012_DataInput::class);

        // 014_DataObject
        require_once './Services/WorkflowEngine/test/parser/014_DataObject/class.test_014_DataObject.php';
        $suite->addTestSuite(test_014_DataObject::class);

        // 015_Data_Wiring
        require_once './Services/WorkflowEngine/test/parser/015_Data_Wiring/class.test_015_Data_Wiring.php';
        $suite->addTestSuite(test_015_Data_Wiring::class);

        //
        // ---------------------------------------------------------------------
        // Parser Cases
        // ---------------------------------------------------------------------
        //
        // 014_DataObject
        require_once './Services/WorkflowEngine/test/parser/case_01/class.test_case_01.php';
        $suite->addTestSuite(test_case_01::class);

        // ---------------------------------------------------------------------
        return $suite;
    }
}
