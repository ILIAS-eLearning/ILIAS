<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilStaticMethodCallActivityTest is part of the petri net based workflow engine.
 *
 * This class holds all tests for the class 
 * activities/class.ilStaticMethodCallActivity 
 * 
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilStaticMethodCallActivityTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		//ilUnitUtil::performInitialisation();
		
		// Empty workflow.
		require_once './Services/WorkflowEngine/classes/workflows/class.ilEmptyWorkflow.php';
		$this->workflow = new ilEmptyWorkflow();
		
		// Basic node
		require_once './Services/WorkflowEngine/classes/nodes/class.ilBasicNode.php';
		$this->node = new ilBasicNode($this->workflow);
		
		// Wiring up so the node is attached to the workflow.
		$this->workflow->addNode($this->node);
		
		require_once './Services/WorkflowEngine/classes/activities/class.ilStaticMethodCallActivity.php';
	}
	
	public function tearDown()
	{
		global $ilSetting;
		if ($ilSetting !=  NULL)
		{
			$ilSetting->delete('IL_PHPUNIT_TEST_TIME');
			$ilSetting->delete('IL_PHPUNIT_TEST_MICROTIME');
		}
	}
	
	public function testConstructorValidContext()
	{
		// Act
		$activity = new ilStaticMethodCallActivity($this->node);
		
		// Assert
		// No exception - good
		$this->assertTrue(
			true, 
			'Construction failed with valid context passed to constructor.'
		);
	}
	
	/**
     * @expectedException PHPUnit_Framework_Error
     */
	public function testConstructorInvalidContext()
	{
		// Act
		$activity = new ilStaticMethodCallActivity($this->workflow);

		// Assert
		$this->assertTrue(
			true, 
			'No exception thrown from constructor on invalid context object.'
		);
	}
		
	public function testSetGetIncludeFilename()
	{
		// Arrange
		$activity = new ilStaticMethodCallActivity($this->node);
		$expected = 'Services/WorkflowEngine/classes/utils/class.ilWorkflowUtils.php';
		
		// Act
		$activity->setIncludeFilename($expected);
		$actual = $activity->getIncludeFilename();
		
		// Assert
		$this->assertEquals($actual, $expected);
	}

	public function testSetGetClassAndMethodName()
	{
		// Arrange
		$activity = new ilStaticMethodCallActivity($this->node);
		$expected = 'ilWorkflowUtils::targetMethod';
		
		// Act
		$activity->setClassAndMethodName($expected);
		$actual = $activity->getClassAndMethodName();
		
		// Assert
		$this->assertEquals($actual, $expected);
	}
	
	public function testSetGetParameters()
	{
		// Arrange
		$activity = new ilStaticMethodCallActivity($this->node);
		$expected = array('homer', 'marge', 'bart', 'lisa', 'maggy');
		
		// Act
		$activity->setParameters($expected);
		$actual = $activity->getParameters();
		
		// Assert
		$this->assertEquals($actual, $expected);
	}
	
	public function testExecute()
	{
		// Arrange
		$activity = new ilStaticMethodCallActivity($this->node);
		$file = 'Services/WorkflowEngine/test/activities/ilStaticMethodCallActivityTest.php';
		$class_and_method = 'ilStaticMethodCallActivityTest::executionTargetMethod';
		$parameters = array('homer', 'marge', 'bart', 'lisa', 'maggy');
		
		// Act
		$activity->setIncludeFilename($file);
		$activity->setClassAndMethodName($class_and_method);
		$activity->setParameters($parameters);
		$activity->execute();
		
		// Assert
		$this->assertTrue(true, 'There dont seem to be problems here.');
	}
	
	public static function executionTargetMethod($context, $param)
	{
		$parameters = array('homer' => 'homer', 'marge' => 'marge', 'bart' => 'bart', 'lisa' => 'lisa', 'maggy' => 'maggy');
		
		if ($context == null)
		{
			throw new Exception('Something went wrong with the context.');
		}
		
		if ($param[0] != $parameters)
		{
			throw new Exception('Something went wrong with the parameters.');
		}
		
		return true;
	}
	
	public function testGetContext()
	{
		// Arrange
		$activity = new ilStaticMethodCallActivity($this->node);
		
		// Act
		$actual = $activity->getContext();
		
		// Assert
		if ($actual === $this->node)
		{
			$this->assertEquals($actual, $this->node);
		} else {
			$this->assertTrue(false, 'Context not identical.');
		}
	}
}