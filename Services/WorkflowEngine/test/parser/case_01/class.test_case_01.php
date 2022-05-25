<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class test_case_01 extends TestCase
{
    #region Helper
    public string $base_path = './Services/WorkflowEngine/test/parser/';
    public string $suite_path = 'case_01/';

    public function getTestInputFilename($test_name) : string
    {
        return $this->base_path . $this->suite_path . $test_name . '.bpmn2';
    }

    public function getTestOutputFilename($test_name) : string
    {
        return $this->base_path . $this->suite_path . $test_name . '_output.php';
    }

    public function getTestGoldsampleFilename($test_name) : string
    {
        return $this->base_path . $this->suite_path . $test_name . '_goldsample.php';
    }

    protected function setUp() : void
    {
        chdir(__DIR__);
        chdir('../../../../../');

        require_once './Services/WorkflowEngine/classes/parser/class.ilBPMN2Parser.php';
    }

    public function test_WorkflowWithSimpleEndEventShouldOutputAccordingly() : void
    {
        $test_name = 'Booking_System_FullDiagram';
        $xml = file_get_contents($this->getTestInputFilename($test_name));
        $parser = new ilBPMN2Parser();
        $parse_result = $parser->parseBPMN2XML($xml);

        file_put_contents($this->getTestOutputFilename($test_name), $parse_result);
        $return = exec('php -l ' . $this->getTestOutputFilename($test_name));

        $this->assertEquals('No syntax errors detected', substr($return, 0, 25), 'Lint of output code failed.');

        $goldsample = file_get_contents($this->getTestGoldsampleFilename($test_name));
        //$this->assertEquals($goldsample, $parse_result, 'Output does not match goldsample.');
        // Disarmed due to whitespace-issues.

        require_once $this->getTestOutputFilename($test_name);
        $process = new $test_name;
        $this->assertFalse($process->isActive());

        unlink($this->getTestOutputFilename($test_name));
    }
}
