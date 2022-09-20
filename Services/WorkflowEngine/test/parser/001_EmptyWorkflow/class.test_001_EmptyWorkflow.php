<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/WorkflowEngine/test/ilWorkflowEngineBaseTest.php';

/**
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class test_001_EmptyWorkflow extends ilWorkflowEngineBaseTest
{
    protected function setUp(): void
    {
        chdir(__DIR__);
        chdir('../../../../../');

        parent::setUp();

        require_once './Services/WorkflowEngine/classes/parser/class.ilBPMN2Parser.php';
    }

    public function test_EmptyWorkflowShouldReturnEmptyPHPBrackets(): void
    {
        $xml = file_get_contents('./Services/WorkflowEngine/test/parser/001_EmptyWorkflow/EmptyWorkflow_1.bpmn2');
        $parser = new ilBPMN2Parser();
        $parse_result = $parser->parseBPMN2XML($xml);

        $goldsample = file_get_contents('./Services/WorkflowEngine/test/parser/001_EmptyWorkflow/EmptyWorkflow_1_goldsample.php');
        $this->assertEquals($goldsample, $parse_result, 'Output does not match goldsample.');

        file_put_contents('./Services/WorkflowEngine/test/parser/001_EmptyWorkflow/EmptyWorkflow_1_output.php', $parse_result);
        $return = exec('php -l ./Services/WorkflowEngine/test/parser/001_EmptyWorkflow/EmptyWorkflow_1_output.php');
        $this->assertEquals('No syntax errors detected', substr($return, 0, 25), 'Lint of output code failed.');
        unlink('./Services/WorkflowEngine/test/parser/001_EmptyWorkflow/EmptyWorkflow_1_output.php');
    }
}
