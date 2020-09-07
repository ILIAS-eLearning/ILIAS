<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/WorkflowEngine/test/ilWorkflowEngineBaseTest.php';

/**
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class test_012_DataInput extends ilWorkflowEngineBaseTest
{
    #region Helper
    public $base_path = './Services/WorkflowEngine/test/parser/';
    public $suite_path = '012_DataInput/';

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

    public function test_WorkflowWithSimpleDataInputShouldOutputAccordingly()
    {
        $test_name = 'DataInput_Simple';
        $xml = file_get_contents($this->getTestInputFilename($test_name));
        $parser = new ilBPMN2Parser();
        $parse_result = $parser->parseBPMN2XML($xml);

        file_put_contents($this->getTestOutputFilename($test_name), $parse_result);
        $return = exec('php -l ' . $this->getTestOutputFilename($test_name));

        $this->assertTrue(substr($return, 0, 25) == 'No syntax errors detected', 'Lint of output code failed.');

        $goldsample = file_get_contents($this->getTestGoldsampleFilename($test_name));
        $this->assertEquals($goldsample, $parse_result, 'Output does not match goldsample.');

        require_once $this->getTestOutputFilename($test_name);
        /** @var ilBaseWorkflow $process */
        $process = new $test_name;
        $process->setInstanceVarById('DataInput_1', 'YaddaYadda');
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
        $this->assertEquals('YaddaYadda', $process->getInstanceVarById('DataInput_1'), 'Inputvar was not kept.');
        $this->assertTrue($all_triggered, 'Not all nodes were triggered.');

        unlink($this->getTestOutputFilename($test_name));
    }

    public function test_WorkflowWithDataInputPropertiesShouldOutputAccordingly()
    {
        $test_name = 'DataInput_WithProperties';
        $xml = file_get_contents($this->getTestInputFilename($test_name));
        $parser = new ilBPMN2Parser();
        $parse_result = $parser->parseBPMN2XML($xml);

        file_put_contents($this->getTestOutputFilename($test_name), $parse_result);
        $return = exec('php -l ' . $this->getTestOutputFilename($test_name));

        $this->assertTrue(substr($return, 0, 25) == 'No syntax errors detected', 'Lint of output code failed.');

        $goldsample = file_get_contents($this->getTestGoldsampleFilename($test_name));
        $this->assertEquals($goldsample, $parse_result, 'Output does not match goldsample.');

        require_once $this->getTestOutputFilename($test_name);
        /** @var ilBaseWorkflow $process */
        $process = new $test_name;
        $process->setInstanceVarById('DataInput_1', 'YaddaYadda');
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
        $this->assertEquals('YaddaYadda', $process->getInstanceVarById('DataInput_1'), 'Inputvar was not kept.');
        $this->assertTrue($all_triggered, 'Not all nodes were triggered.');

        unlink($this->getTestOutputFilename($test_name));
    }

    public function test_WorkflowWithRepositoryObjectSelectorShouldOutputAccordingly()
    {
        $test_name = 'DataInput_RepositoryObjectSelector';
        $xml = file_get_contents($this->getTestInputFilename($test_name));
        $parser = new ilBPMN2Parser();
        $parse_result = $parser->parseBPMN2XML($xml);

        file_put_contents($this->getTestOutputFilename($test_name), $parse_result);
        $return = exec('php -l ' . $this->getTestOutputFilename($test_name));

        $this->assertTrue(substr($return, 0, 25) == 'No syntax errors detected', 'Lint of output code failed.');

        $goldsample = file_get_contents($this->getTestGoldsampleFilename($test_name));
        $this->assertEquals($goldsample, $parse_result, 'Output does not match goldsample.');

        require_once $this->getTestOutputFilename($test_name);
        /** @var ilBaseWorkflow $process */
        $process = new $test_name;
        $process->setInstanceVarById('DataInput_1', 'YaddaYadda');
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
        $this->assertEquals('YaddaYadda', $process->getInstanceVarById('DataInput_1'), 'Inputvar was not kept.');
        $this->assertTrue($all_triggered, 'Not all nodes were triggered.');

        unlink($this->getTestOutputFilename($test_name));
    }
}
