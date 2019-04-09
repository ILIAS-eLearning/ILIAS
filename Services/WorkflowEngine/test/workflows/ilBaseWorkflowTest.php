<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * ilBaseWorkflowTest is part of the petri net based workflow engine.
 *
 * This class holds all tests for the class 
 * workflows/class.BaseWorkflow 
 * 
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilBaseWorkflowTest extends TestCase
{
	public function setUp(): void
	{
		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		//ilUnitUtil::performInitialisation();
		
		// Empty workflow as test fixture for the abstract class.
		require_once './Services/WorkflowEngine/classes/workflows/class.ilEmptyWorkflow.php';	
	}
	
	public function tearDown(): void
	{
		global $ilSetting;
		if ($ilSetting !=  NULL)
		{
			$ilSetting->delete( 'IL_PHPUNIT_TEST_TIME' );
			$ilSetting->delete( 'IL_PHPUNIT_TEST_MICROTIME' );
		}
	}
}