<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/WorkflowEngine/test/ilWorkflowEngineBaseTest.php';

/**
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class test_015_Data_Wiring extends ilWorkflowEngineBaseTest
{
    #region Helper
    public $base_path = './Services/WorkflowEngine/test/parser/';
    public $suite_path = '015_Data_Wiring/';

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

    public function test_WorkflowWithInputTaskWiredDataIOShouldOutputAccordingly()
    {
        $test_name = 'Data_Wiring_Input_Task';
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
        $process->setInstanceVarById('DataInput_1', 234);
        $process->startWorkflow();

        foreach ($process->getNodes() as $node) {
            if ($node->getName() == '$_v_Task_1') {
                $runtime_vars = $node->getRuntimeVars();
            }
        }
        $this->assertEquals(234, $runtime_vars['user_id'], 'IO data was not forwarded from input to task runtime var.');
        $this->assertEquals(234, $process->getInstanceVarById('DataInput_1'), 'IO data was not kept as input var.');
        unlink($this->getTestOutputFilename($test_name));
    }


    public function test_WorkflowWithInputObjectOutputWiredDataIOShouldOutputAccordingly()
    {
        $test_name = 'DataObject_Wiring_Input_Object_Output';
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

        //$this->assertEquals('YaddaYadda', $process->getInstanceVarById('DataOutput_1'),  'IO data was not forwarded through process.');
        unlink($this->getTestOutputFilename($test_name));
    }
}
