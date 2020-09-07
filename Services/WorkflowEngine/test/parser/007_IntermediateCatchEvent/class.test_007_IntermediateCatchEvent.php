<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/WorkflowEngine/test/ilWorkflowEngineBaseTest.php';

/**
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class test_007_IntermediateCatchEvent extends ilWorkflowEngineBaseTest
{
    #region Helper
    public $base_path = './Services/WorkflowEngine/test/parser/';
    public $suite_path = '007_IntermediateCatchEvent/';

    public function getTestInputFilename($test_name)
    {
        return $this->base_path . $this->suite_path . $test_name . '.bpmn2';
    }

    public function getTestOutputFilename($test_name)
    {
        return $this->base_path . $this->suite_path . $test_name . '_output.php';
    }

    public function getTestGoldsampleFilename($test_name)
    {
        return $this->base_path . $this->suite_path . $test_name . '_goldsample.php';
    }

    public function setUp()
    {
        chdir(dirname(__FILE__));
        chdir('../../../../../');

        parent::setUp();

        require_once './Services/WorkflowEngine/classes/parser/class.ilBPMN2Parser.php';
    }

    public function test_WorkflowWithSimpleIntermediateMessageEventShouldOutputAccordingly()
    {
        $this->markTestIncomplete(
            '$ilDB throws notices during test.'
        );

        $test_name = 'IntermediateCatchEvent_Message_Simple';
        $xml = file_get_contents($this->getTestInputFilename($test_name));
        $parser = new ilBPMN2Parser();
        $parse_result = $parser->parseBPMN2XML($xml);

        file_put_contents($this->getTestOutputFilename($test_name), $parse_result);
        $return = exec('php -l ' . $this->getTestOutputFilename($test_name));

        $this->assertTrue(substr($return, 0, 25) == 'No syntax errors detected', 'Lint of output code failed.');

        $goldsample = file_get_contents($this->getTestGoldsampleFilename($test_name));
        $this->assertEquals($goldsample, $parse_result, 'Output does not match goldsample.');

        require_once './Services/Database/classes/class.ilDB.php';
        $ildb_mock = $this->createMock('ilDBMySQL', array('nextId','quote','exec', 'insert'), array(), '', false, false);
        $ildb_mock->expects($this->any())->method('quote')->will($this->returnCallback(''));
        $i = 0;
        $ildb_mock->expects($this->any())->method('nextId')->will($this->returnValue($i++));
        $ildb_mock->expects($this->any())->method('exec')->will($this->returnValue(true));
        $ildb_mock->expects($this->any())->method('insert')->will($this->returnValue(true));

        //		$ildb_mock->expects( $this->exactly(2) )
        //				  ->method( 'fetchAssoc' )
        //				  ->with( $this->equalTo('Test') )
        //				  ->will( $this->onConsecutiveCalls(array('answer_id' => 123, 'depth' => 456), false ) );

        global $ilDB;
        $ilDB = $ildb_mock;
        $GLOBALS['ilDB'] = $ildb_mock;

        require_once $this->getTestOutputFilename($test_name);
        /** @var ilBaseWorkflow $process */
        $process = new $test_name;
        $process->startWorkflow();
        $all_triggered = true;
        foreach ($process->getNodes() as $node) {
            /** @var ilNode $node*/
            foreach ($node->getDetectors() as $detector) {
                /** @var ilSimpleDetector $detector */
                if (!$detector->getActivated()) {
                    $all_triggered = false;
                }
            }
            foreach ($node->getEmitters() as $emitter) {
                /** @var ilActivationEmitter $emitter */
                if (!$emitter->getActivated()) {
                    $all_triggered = false;
                }
            }
        }
        $this->assertFalse($all_triggered, 'All nodes were triggered (but should not since the event was not handled.');

        $process->handleEvent(
            array(
              'Course',
              'UserWasAssigned',
              'usr',
              0,
              'crs',
              0
            )
        );

        $all_triggered = true;
        foreach ($process->getNodes() as $node) {
            /** @var ilNode $node*/
            foreach ($node->getDetectors() as $detector) {
                /** @var ilSimpleDetector $detector */
                if (!$detector->getActivated()) {
                    $all_triggered = false;
                }
            }
            foreach ($node->getEmitters() as $emitter) {
                /** @var ilActivationEmitter $emitter */
                if (!$emitter->getActivated()) {
                    $all_triggered = false;
                }
            }
        }
        $this->assertTrue($all_triggered, 'Not all nodes were triggered.');

        unlink($this->getTestOutputFilename($test_name));
    }

    public function test_WorkflowWithSimpleIntermediateSignalEventShouldOutputAccordingly()
    {
        $this->markTestIncomplete(
            '$ilDB throws notices during test.'
        );

        $test_name = 'IntermediateCatchEvent_Signal_Simple';
        $xml = file_get_contents($this->getTestInputFilename($test_name));
        $parser = new ilBPMN2Parser();
        $parse_result = $parser->parseBPMN2XML($xml);

        file_put_contents($this->getTestOutputFilename($test_name), $parse_result);
        $return = exec('php -l ' . $this->getTestOutputFilename($test_name));

        $this->assertTrue(substr($return, 0, 25) == 'No syntax errors detected', 'Lint of output code failed.');

        $goldsample = file_get_contents($this->getTestGoldsampleFilename($test_name));
        $this->assertEquals($goldsample, $parse_result, 'Output does not match goldsample.');

        require_once './Services/Database/classes/class.ilDB.php';
        $ildb_mock = $this->createMock('ilDBMySQL', array('nextId','quote','exec', 'insert'), array(), '', false, false);
        $ildb_mock->expects($this->any())->method('quote')->will($this->returnCallback(''));
        $id = 0;
        $ildb_mock->expects($this->any())->method('nextId')->will($this->returnValue($i++));
        $ildb_mock->expects($this->any())->method('exec')->will($this->returnValue(true));
        $ildb_mock->expects($this->any())->method('insert')->will($this->returnValue(true));

        //		$ildb_mock->expects( $this->exactly(2) )
        //				  ->method( 'fetchAssoc' )
        //				  ->with( $this->equalTo('Test') )
        //				  ->will( $this->onConsecutiveCalls(array('answer_id' => 123, 'depth' => 456), false ) );

        global $ilDB;
        $ilDB = $ildb_mock;
        $GLOBALS['ilDB'] = $ildb_mock;

        require_once $this->getTestOutputFilename($test_name);
        /** @var ilBaseWorkflow $process */
        $process = new $test_name;
        $process->startWorkflow();
        $all_triggered = true;
        foreach ($process->getNodes() as $node) {
            /** @var ilNode $node*/
            foreach ($node->getDetectors() as $detector) {
                /** @var ilSimpleDetector $detector */
                if (!$detector->getActivated()) {
                    $all_triggered = false;
                }
            }
            foreach ($node->getEmitters() as $emitter) {
                /** @var ilActivationEmitter $emitter */
                if (!$emitter->getActivated()) {
                    $all_triggered = false;
                }
            }
        }
        $this->assertFalse($all_triggered, 'All nodes were triggered (but should not since the event was not handled.');

        $process->handleEvent(
            array(
                'Course',
                'UserLeft',
                'usr',
                0,
                'crs',
                0
            )
        );

        $all_triggered = true;
        foreach ($process->getNodes() as $node) {
            /** @var ilNode $node*/
            foreach ($node->getDetectors() as $detector) {
                /** @var ilSimpleDetector $detector */
                if (!$detector->getActivated()) {
                    $all_triggered = false;
                }
            }
            foreach ($node->getEmitters() as $emitter) {
                /** @var ilActivationEmitter $emitter */
                if (!$emitter->getActivated()) {
                    $all_triggered = false;
                }
            }
        }
        $this->assertTrue($all_triggered, 'Not all nodes were triggered.');

        unlink($this->getTestOutputFilename($test_name));
    }

    public function test_WorkflowWithSimpleIntermediateTimerEventShouldOutputAccordingly()
    {
        $this->markTestIncomplete(
            '$ilDB throws notices during test.'
        );
        $test_name = 'IntermediateCatchEvent_Timer_Simple';
        $xml = file_get_contents($this->getTestInputFilename($test_name));
        $parser = new ilBPMN2Parser();
        $parse_result = $parser->parseBPMN2XML($xml);

        file_put_contents($this->getTestOutputFilename($test_name), $parse_result);
        $return = exec('php -l ' . $this->getTestOutputFilename($test_name));

        $this->assertTrue(substr($return, 0, 25) == 'No syntax errors detected', 'Lint of output code failed.');

        $goldsample = file_get_contents($this->getTestGoldsampleFilename($test_name));
        $this->assertEquals($goldsample, $parse_result, 'Output does not match goldsample.');

        require_once './Services/Database/classes/class.ilDB.php';
        $ildb_mock = $this->createMock('ilDBMySQL', array('nextId','quote','exec', 'insert'), array(), '', false, false);
        $ildb_mock->expects($this->any())->method('quote')->will($this->returnCallback(''));
        $id = 0;
        $ildb_mock->expects($this->any())->method('nextId')->will($this->returnValue($i++));
        $ildb_mock->expects($this->any())->method('exec')->will($this->returnValue(true));
        $ildb_mock->expects($this->any())->method('insert')->will($this->returnValue(true));

        //		$ildb_mock->expects( $this->exactly(2) )
        //				  ->method( 'fetchAssoc' )
        //				  ->with( $this->equalTo('Test') )
        //				  ->will( $this->onConsecutiveCalls(array('answer_id' => 123, 'depth' => 456), false ) );

        global $ilDB;
        $ilDB = $ildb_mock;
        $GLOBALS['ilDB'] = $ildb_mock;

        require_once $this->getTestOutputFilename($test_name);
        /** @var ilBaseWorkflow $process */
        $process = new $test_name;
        $process->startWorkflow();
        $all_triggered = true;
        foreach ($process->getNodes() as $node) {
            /** @var ilNode $node*/
            foreach ($node->getDetectors() as $detector) {
                /** @var ilSimpleDetector $detector */
                if (!$detector->getActivated()) {
                    $all_triggered = false;
                }
            }
            foreach ($node->getEmitters() as $emitter) {
                /** @var ilActivationEmitter $emitter */
                if (!$emitter->getActivated()) {
                    $all_triggered = false;
                }
            }
        }
        $this->assertFalse($all_triggered, 'All nodes were triggered (but should not since the event was not handled.');

        $process->handleEvent(
            array(
                'time_passed',
                'time_passed',
                'none',
                0,
                'none',
                0
            )
        );

        $all_triggered = true;
        foreach ($process->getNodes() as $node) {
            /** @var ilNode $node*/
            foreach ($node->getDetectors() as $detector) {
                /** @var ilSimpleDetector $detector */
                if (!$detector->getActivated()) {
                    $all_triggered = false;
                }
            }
            foreach ($node->getEmitters() as $emitter) {
                /** @var ilActivationEmitter $emitter */
                if (!$emitter->getActivated()) {
                    $all_triggered = false;
                }
            }
        }
        $this->assertTrue($all_triggered, 'Not all nodes were triggered.');

        unlink($this->getTestOutputFilename($test_name));
    }
}
