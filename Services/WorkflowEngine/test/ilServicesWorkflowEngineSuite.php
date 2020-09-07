<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'libs/composer/vendor/autoload.php';
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
class ilServicesWorkflowEngineSuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        error_reporting(E_ALL ^ E_NOTICE);

        chdir(dirname(__FILE__));
        chdir('../../../');

        $suite = new ilServicesWorkflowEngineSuite();

        //
        // ---------------------------------------------------------------------
        // Activities
        // ---------------------------------------------------------------------
        //

        require_once './Services/WorkflowEngine/test/activities/ilEventRaisingActivityTest.php';
        $suite->addTestSuite('ilEventRaisingActivityTest');

        require_once './Services/WorkflowEngine/test/activities/ilLoggingActivityTest.php';
        $suite->addTestSuite('ilLoggingActivityTest');

        require_once './Services/WorkflowEngine/test/activities/ilScriptActivityTest.php';
        $suite->addTestSuite('ilScriptActivityTest');

        require_once './Services/WorkflowEngine/test/activities/ilSettingActivityTest.php';
        $suite->addTestSuite('ilSettingActivityTest');

        require_once './Services/WorkflowEngine/test/activities/ilStaticMethodCallActivityTest.php';
        $suite->addTestSuite('ilStaticMethodCallActivityTest');

        require_once './Services/WorkflowEngine/test/activities/ilStopWorkflowActivityTest.php';
        $suite->addTestSuite('ilStopWorkflowActivityTest');

        //
        // ---------------------------------------------------------------------
        // Detectors
        // ---------------------------------------------------------------------
        //

        require_once './Services/WorkflowEngine/test/detectors/ilCounterDetectorTest.php';
        $suite->addTestSuite('ilCounterDetectorTest');

        require_once './Services/WorkflowEngine/test/detectors/ilSimpleDetectorTest.php';
        $suite->addTestSuite('ilSimpleDetectorTest');

        require_once './Services/WorkflowEngine/test/detectors/ilTimerDetectorTest.php';
        $suite->addTestSuite('ilTimerDetectorTest');

        require_once './Services/WorkflowEngine/test/detectors/ilEventDetectorTest.php';
        $suite->addTestSuite('ilEventDetectorTest');

        require_once './Services/WorkflowEngine/test/detectors/ilDataDetectorTest.php';
        $suite->addTestSuite('ilDataDetectorTest');

        //
        // ---------------------------------------------------------------------
        // Emitters
        // ---------------------------------------------------------------------
        //

        require_once './Services/WorkflowEngine/test/emitters/ilSimpleEmitterTest.php';
        $suite->addTestSuite('ilSimpleEmitterTest');

        require_once './Services/WorkflowEngine/test/emitters/ilActivationEmitterTest.php';
        $suite->addTestSuite('ilActivationEmitterTest');

        require_once './Services/WorkflowEngine/test/emitters/ilDataEmitterTest.php';
        $suite->addTestSuite('ilDataEmitterTest');

        //
        // ---------------------------------------------------------------------
        // Nodes
        // ---------------------------------------------------------------------
        //

        require_once './Services/WorkflowEngine/test/nodes/ilBasicNodeTest.php';
        $suite->addTestSuite('ilBasicNodeTest');

        require_once './Services/WorkflowEngine/test/nodes/ilConditionalNodeTest.php';
        $suite->addTestSuite('ilConditionalNodeTest');

        require_once './Services/WorkflowEngine/test/nodes/ilCaseNodeTest.php';
        $suite->addTestSuite('ilCaseNodeTest');

        require_once './Services/WorkflowEngine/test/nodes/ilPluginNodeTest.php';
        $suite->addTestSuite('ilPluginNodeTest');

        //
        // ---------------------------------------------------------------------
        // Workflows
        // ---------------------------------------------------------------------
        //

        require_once './Services/WorkflowEngine/test/workflows/ilBaseWorkflowTest.php';
        $suite->addTestSuite('ilBaseWorkflowTest');

        //return $suite; // Uncomment to exclude parsertests for more meaningful coverage data.

        // ---------------------------------------------------------------------
        // Utils
        // ---------------------------------------------------------------------

        //
        // ---------------------------------------------------------------------
        // Parser - Components
        // ---------------------------------------------------------------------

        // 001_EmptyWorkflow
        require_once './Services/WorkflowEngine/test/parser/001_EmptyWorkflow/class.test_001_EmptyWorkflow.php';
        $suite->addTestSuite('test_001_EmptyWorkflow');

        // 002_StartNode
        require_once './Services/WorkflowEngine/test/parser/002_StartEvent/class.test_002_StartEvent.php';
        $suite->addTestSuite('test_002_StartEvent');

        // 003_ParallelGateway
        require_once './Services/WorkflowEngine/test/parser/003_ParallelGateway/class.test_003_ParallelGateway.php';
        $suite->addTestSuite('test_003_ParallelGateway');

        // 004_InclusiveGateway
        require_once './Services/WorkflowEngine/test/parser/004_InclusiveGateway/class.test_004_InclusiveGateway.php';
        $suite->addTestSuite('test_004_InclusiveGateway');

        // 005_ExclusiveGateway
        require_once './Services/WorkflowEngine/test/parser/005_ExclusiveGateway/class.test_005_ExclusiveGateway.php';
        $suite->addTestSuite('test_005_ExclusiveGateway');

        // 006_Task
        require_once './Services/WorkflowEngine/test/parser/006_Task/class.test_006_Task.php';
        $suite->addTestSuite('test_006_Task');

        // 007_IntermediateCatchEvent
        require_once './Services/WorkflowEngine/test/parser/007_IntermediateCatchEvent/class.test_007_IntermediateCatchEvent.php';
        $suite->addTestSuite('test_007_IntermediateCatchEvent');

        // 008_IntermediateThrowEvent
        require_once './Services/WorkflowEngine/test/parser/008_IntermediateThrowEvent/class.test_008_IntermediateThrowEvent.php';
        $suite->addTestSuite('test_008_IntermediateThrowEvent');

        // 009_EndEvent
        require_once './Services/WorkflowEngine/test/parser/009_EndEvent/class.test_009_EndEvent.php';
        $suite->addTestSuite('test_009_EndEvent');

        // 010_ComplexGateway
        require_once './Services/WorkflowEngine/test/parser/010_ComplexGateway/class.test_010_ComplexGateway.php';
        $suite->addTestSuite('test_010_ComplexGateway');

        // 011_EventBasedGateway
        require_once './Services/WorkflowEngine/test/parser/011_EventBasedGateway/class.test_011_EventBasedGateway.php';
        $suite->addTestSuite('test_011_EventBasedGateway');

        // 012_DataInput
        require_once './Services/WorkflowEngine/test/parser/012_DataInput/class.test_012_DataInput.php';
        $suite->addTestSuite('test_012_DataInput');

        // 013_DataOutput
        require_once './Services/WorkflowEngine/test/parser/013_DataOutput/class.test_013_DataOutput.php';
        $suite->addTestSuite('test_013_DataOutput');

        // 014_DataObject
        require_once './Services/WorkflowEngine/test/parser/014_DataObject/class.test_014_DataObject.php';
        $suite->addTestSuite('test_014_DataObject');

        // 015_Data_Wiring
        require_once './Services/WorkflowEngine/test/parser/015_Data_Wiring/class.test_015_Data_Wiring.php';
        $suite->addTestSuite('test_015_Data_Wiring');

        //
        // ---------------------------------------------------------------------
        // Parser Cases
        // ---------------------------------------------------------------------
        //
        // 014_DataObject
        require_once './Services/WorkflowEngine/test/parser/case_01/class.test_case_01.php';
        $suite->addTestSuite('test_case_01');

        // ---------------------------------------------------------------------
        return $suite;
    }
}
