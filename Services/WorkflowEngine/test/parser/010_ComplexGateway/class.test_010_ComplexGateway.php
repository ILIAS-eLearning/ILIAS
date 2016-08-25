<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class test_010_ComplexGateway extends PHPUnit_Framework_TestCase
{
	#region Helper
	public $base_path = './Services/WorkflowEngine/test/parser/';
	public $suite_path = '010_ComplexGateway/';

	public function getTestInputFilename($test_name)
	{
		return $this->base_path . $this->suite_path  . $test_name . '.bpmn2';
	}

	public function getTestOutputFilename($test_name)
	{
		return $this->base_path . $this->suite_path  . $test_name . '_output.php';
	}

	public function getTestGoldsampleFilename($test_name)
	{
		return $this->base_path . $this->suite_path  . $test_name . '_goldsample.php';
	}

	public function setUp()
	{
		chdir( dirname( __FILE__ ) );
		chdir( '../../../../../' );

		require_once './Services/WorkflowEngine/classes/parser/class.ilBPMN2Parser.php';
	}

	public function test_WorkflowWithSimpleEndEventShouldOutputAccordingly()
	{
		$this->markTestIncomplete('The complex gateway has conceptual issues. Proper modeling of a default outgoing 
		sequence flows seems to be impossible with available modelling tools. Once this is resolved, testing makes
		sense, not before.');
		// TODO: Resolve conceptual issues.

		$test_name = 'ComplexGateway_Blanko_Simple';
		$xml = file_get_contents($this->getTestInputFilename($test_name));
		$parser = new ilBPMN2Parser();
		$parse_result = $parser->parseBPMN2XML($xml);

		file_put_contents($this->getTestOutputFilename($test_name), $parse_result);
		$return = exec('php -l ' . $this->getTestOutputFilename($test_name));

		$this->assertTrue(substr($return,0,25) == 'No syntax errors detected', 'Lint of output code failed.');

		$goldsample = file_get_contents($this->getTestGoldsampleFilename($test_name));
		$this->assertEquals($goldsample, $parse_result, 'Output does not match goldsample.');

		require_once $this->getTestOutputFilename($test_name);
		$process = new $test_name;
		$this->assertFalse($process->isActive());

		unlink($this->getTestOutputFilename($test_name));
	}

}