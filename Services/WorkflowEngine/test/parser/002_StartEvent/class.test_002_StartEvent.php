<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/WorkflowEngine/test/ilWorkflowEngineBaseTest.php';

/**
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class test_002_StartEvent extends ilWorkflowEngineBaseTest
{
    public function setUp()
    {
        chdir(dirname(__FILE__));
        chdir('../../../../../');

        parent::setUp();

        require_once './Services/WorkflowEngine/classes/parser/class.ilBPMN2Parser.php';
    }

    public function test_WorkflowWithBlankStartEventShouldOutputAccordingly()
    {
        $xml = file_get_contents('./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Blank.bpmn2');
        $parser = new ilBPMN2Parser();
        $parse_result = $parser->parseBPMN2XML($xml);
        
        file_put_contents('./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Blank_output.php', $parse_result);
        $return = exec('php -l ./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Blank_output.php');
        $this->assertTrue(substr($return, 0, 25) == 'No syntax errors detected', 'Lint of output code failed.');


        $goldsample = file_get_contents('./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Blank_goldsample.php');
        $this->assertEquals($goldsample, $parse_result, 'Output does not match goldsample.');

        require_once './Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Blank_output.php';
        $process = new StartEvent_Blank();
        $this->assertFalse($process->isActive());

        // Here I would start the workflow, but that leads to saving it to the database which is currently not supported.
        
        unlink('./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Blank_output.php');
    }

    public function test_WorkflowWithMessageStartEventShouldOutputAccordingly()
    {
        $xml = file_get_contents('./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Message.bpmn2');
        $parser = new ilBPMN2Parser();
        $parse_result = $parser->parseBPMN2XML($xml);

        file_put_contents('./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Message_output.php', $parse_result);
        $return = exec('php -l ./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Message_output.php');
        $this->assertTrue(substr($return, 0, 25) == 'No syntax errors detected', 'Lint of output code failed.');


        $goldsample = file_get_contents('./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Message_goldsample.php');
        $this->assertEquals($goldsample, $parse_result, 'Output does not match goldsample.');

        require_once './Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Message_output.php';
        $process = new StartEvent_Message();
        $this->assertFalse($process->isActive());
        
        unlink('./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Message_output.php');
    }

    public function test_WorkflowWithSignalStartEventShouldOutputAccordingly()
    {
        $xml = file_get_contents('./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Signal.bpmn2');
        $parser = new ilBPMN2Parser();
        $parse_result = $parser->parseBPMN2XML($xml);

        file_put_contents('./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Signal_output.php', $parse_result);
        $return = exec('php -l ./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Signal_output.php');
        $this->assertTrue(substr($return, 0, 25) == 'No syntax errors detected', 'Lint of output code failed.');

        $goldsample = file_get_contents('./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Signal_goldsample.php');
        $this->assertEquals($goldsample, $parse_result, 'Output does not match goldsample.');

        require_once './Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Signal_output.php';
        $process = new StartEvent_Signal();
        $this->assertFalse($process->isActive());

        unlink('./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Signal_output.php');
    }

    public function test_WorkflowWithTimerDateStartEventShouldOutputAccordingly()
    {
        $xml = file_get_contents('./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Timer_Date.bpmn2');
        $parser = new ilBPMN2Parser();
        $parse_result = $parser->parseBPMN2XML($xml);

        file_put_contents('./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Timer_Date_output.php', $parse_result);
        $return = exec('php -l ./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Timer_Date_output.php');
        $this->assertTrue(substr($return, 0, 25) == 'No syntax errors detected', 'Lint of output code failed.');

        $goldsample = file_get_contents('./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Timer_Date_goldsample.php');
        $this->assertEquals($goldsample, $parse_result, 'Output does not match goldsample.');

        require_once './Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Timer_Date_output.php';
        $process = new StartEvent_Timer_Date();
        $this->assertFalse($process->isActive());

        unlink('./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_Timer_Date_output.php');
    }

    public function test_WorkflowWithMultipleStartEventsShouldOutputAccordingly()
    {
        $xml = file_get_contents('./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_MultiStart.bpmn2');
        $parser = new ilBPMN2Parser();
        $parse_result = $parser->parseBPMN2XML($xml);

        file_put_contents('./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_MultiStart_output.php', $parse_result);
        $return = exec('php -l ./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_MultiStart_output.php');
        $this->assertTrue(substr($return, 0, 25) == 'No syntax errors detected', 'Lint of output code failed.');

        $goldsample = file_get_contents('./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_MultiStart_goldsample.php');
        $this->assertEquals($goldsample, $parse_result, 'Output does not match goldsample.');

        require_once './Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_MultiStart_output.php';
        $process = new StartEvent_Multistart();
        $this->assertFalse($process->isActive());

        unlink('./Services/WorkflowEngine/test/parser/002_StartEvent/StartEvent_MultiStart_output.php');
    }
}
