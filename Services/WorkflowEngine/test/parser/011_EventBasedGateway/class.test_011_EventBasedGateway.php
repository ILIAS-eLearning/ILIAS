<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class test_011_EventBasedGateway extends PHPUnit_Framework_TestCase
{
	#region Helper
	public $base_path = './Services/WorkflowEngine/test/parser/';
	public $suite_path = '011_EventBasedGateway/';

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

	public function test_WorkflowWithSimpleEventGatewayShouldOutputAccordingly()
	{
		$this->markTestIncomplete(
				'$ilDB throws notices during test.'
		);

		$test_name = 'EventBasedGateway_Blanko_Simple';
		$xml = file_get_contents($this->getTestInputFilename($test_name));
		$parser = new ilBPMN2Parser();
		$parse_result = $parser->parseBPMN2XML($xml);

		file_put_contents($this->getTestOutputFilename($test_name), $parse_result);
		$return = exec('php -l ' . $this->getTestOutputFilename($test_name));

		$this->assertTrue(substr($return,0,25) == 'No syntax errors detected', 'Lint of output code failed.');

		$goldsample = file_get_contents($this->getTestGoldsampleFilename($test_name));
		$this->assertEquals($goldsample, $parse_result, 'Output does not match goldsample.');

		require_once './Services/Database/classes/class.ilDB.php';
		$ildb_mock = $this->getMock('ilDBMySQL', array('nextId','quote','exec', 'insert'), array(), '', false, false);
		$ildb_mock->expects( $this->any() )->method('quote')->will( $this->returnCallback(''));
		$i = 0;
		$ildb_mock->expects( $this->any() )->method( 'nextId' )->will( $this->returnValue($i++) );
		$ildb_mock->expects( $this->any() )->method( 'exec' )->will( $this->returnValue(true) );
		$ildb_mock->expects( $this->any() )->method( 'insert' )->will( $this->returnValue(true) );

		global $ilDB;
		$ilDB = $ildb_mock;
		$GLOBALS['ilDB'] = $ildb_mock;
		
		require_once $this->getTestOutputFilename($test_name);
		$process = new $test_name;
		$this->assertFalse($process->isActive());

		$process->startWorkflow();
		$this->assertTrue($process->isActive());
		$all_triggered = true;
		foreach($process->getNodes() as $node)
		{
			/** @var ilNode $node*/
			foreach($node->getDetectors() as $detector)
			{
				/** @var ilSimpleDetector $detector */
				if(!$detector->getActivated())
				{
					$all_triggered = false;
				}
			}
			foreach($node->getEmitters() as $emitter)
			{
				/** @var ilActivationEmitter $emitter */
				if(!$emitter->getActivated())
				{
					$all_triggered = false;
				}
			}
		}
		$this->assertFalse($all_triggered, 'All nodes were triggered.');
		$this->assertTrue($process->isActive());
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
		foreach($process->getNodes() as $node)
		{
			/** @var ilNode $node*/
			foreach($node->getDetectors() as $detector)
			{
				/** @var ilSimpleDetector $detector */
				if(!$detector->getActivated())
				{
					$all_triggered = false;
				}
			}
			foreach($node->getEmitters() as $emitter)
			{
				/** @var ilActivationEmitter $emitter */
				if(!$emitter->getActivated())
				{
					$all_triggered = false;
				}
			}
		}
		$this->assertFalse($all_triggered, 'All nodes were triggered.');
		$all_inactive = true;
		foreach($process->getNodes() as $node)
		{
			if($node->isActive())
			{
				$all_inactive = false;
			}
		}
		$this->assertTrue($all_inactive, 'Not all nodes are inactive.');
		$this->assertFalse($process->isActive(), 'Process should be inactive after processing the event. It is not.');
		unlink($this->getTestOutputFilename($test_name));
		// This test was indeed a harsh mistress.
	}

}