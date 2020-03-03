<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilParserTestSuite
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilParserTestSuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        error_reporting(E_ALL ^ E_NOTICE);

        chdir(dirname(__FILE__));
        chdir('../../../../../');

        $suite = new ilParserTestSuite();

        // 001_EmptyWorkflow
        require_once '001_EmptyWorkflow/class.test_001_EmptyWorkflow.php';
        $suite->addTestSuite('test_001_EmptyWorkflow');

        // 002_StartEvent
        require_once '002_StartEvent/class.test_002_StartEvent.php';
        $suite->addTestSuite('test_002_StartEvent');

        // 003_ParallelGateway
        require_once '003_ParallelGateway/class.test_003_ParallelGateway.php';
        $suite->addTestSuite('test_003_ParallelGateway');

        // 004_InclusiveGateway
        require_once '004_InclusiveGateway/class.test_004_InclusiveGateway.php';
        $suite->addTestSuite('test_004_InclusiveGateway');

        // 005_ExclusiveGateway
        require_once '005_ExclusiveGateway/class.test_005_ExclusiveGateway.php';
        $suite->addTestSuite('test_005_ExclusiveGateway');

        // 006_Task
        require_once '006_Task/class.test_006_Task.php';
        $suite->addTestSuite('test_006_Task');

        // 007_IntermediateCatchEvent
        require_once '007_IntermediateCatchEvent/class.test_007_IntermediateCatchEvent.php';
        $suite->addTestSuite('test_007_IntermediateCatchEvent');

        // 008_IntermediateThrowEvent
        require_once '008_IntermediateThrowEvent/class.test_008_IntermediateThrowEvent.php';
        $suite->addTestSuite('test_008_IntermediateThrowEvent');

        // 009_EndEvent
        require_once '009_EndEvent/class.test_009_EndEvent.php';
        $suite->addTestSuite('test_009_EndEvent');

        // 010_ComplexGateway
        require_once '010_ComplexGateway/class.test_010_ComplexGateway.php';
        $suite->addTestSuite('test_010_ComplexGateway');

        // 011_EventBasedGateway
        require_once '011_EventBasedGateway/class.test_011_EventBasedGateway.php';
        $suite->addTestSuite('test_011_EventBasedGateway');

        // 012_DataInput
        require_once '012_DataInput/class.test_012_DataInput.php';
        $suite->addTestSuite('test_012_DataInput');

        // 013_DataOutput
        require_once '013_DataOutput/class.test_013_DataOutput.php';
        $suite->addTestSuite('test_013_DataOutput');

        // 014_DataObject
        require_once '014_DataObject/class.test_014_DataObject.php';
        $suite->addTestSuite('test_014_DataObject');

        // 015_Data_Wiring
        require_once '015_Data_Wiring/class.test_015_Data_Wiring.php';
        $suite->addTestSuite('test_015_Data_Wiring');

        //
        // --------------------------------------------------------------------
        // Cases
        // --------------------------------------------------------------------
        //

        // case_01 - Booking System
        require_once 'case_01/class.test_case_01.php';
        $suite->addTestSuite('test_case_01');

        return $suite;
    }
}
