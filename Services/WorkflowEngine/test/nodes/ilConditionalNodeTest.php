<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilConditionalNodeTest is part of the petri net based workflow engine.
 *
 * This class holds all tests for the class
 * nodes/class.ilConditionalNode
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilConditionalNodeTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        //ilUnitUtil::performInitialisation();
        
        // Empty workflow.
        require_once './Services/WorkflowEngine/classes/workflows/class.ilEmptyWorkflow.php';
        $this->workflow = new ilEmptyWorkflow();
    }
    
    public function tearDown()
    {
        global $ilSetting;
        if ($ilSetting !=  null) {
            $ilSetting->delete('IL_PHPUNIT_TEST_TIME');
            $ilSetting->delete('IL_PHPUNIT_TEST_MICROTIME');
        }
    }
    
    public function testConstructorValidContext()
    {
        // Act
        $node = new ilConditionalNode($this->workflow);
        
        // Assert
        // No exception - good
        $this->assertTrue(
            true,
            'Construction failed with valid context passed to constructor.'
        );
    }

    public function testGetContext()
    {
        // Arrange
        $node = new ilConditionalNode($this->workflow);
        
        // Act
        $actual = $node->getContext();
        
        // Assert
        if ($actual === $this->workflow) {
            $this->assertEquals($actual, $this->workflow);
        } else {
            $this->assertTrue(false, 'Context not identical.');
        }
    }
    
    public function testIsActiveAndActivate()
    {
        // Arrange
        $node = new ilConditionalNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector = new ilSimpleDetector($node);
        $node->addDetector($detector);
        $evaluation_expression = 'return null;';
        $node->setEvaluationExpression($evaluation_expression);

        // Act
        $node->activate();
        
        // Assert
        $actual = $node->isActive();
        $this->assertTrue($actual);
    }
    
    public function testDeactivate()
    {
        // Arrange
        $node = new ilConditionalNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector = new ilSimpleDetector($node);
        $node->addDetector($detector);
        $evaluation_expression = 'return null;';
        $node->setEvaluationExpression($evaluation_expression);
        
        // Act
        $node->activate();
        $was_activated = $node->isActive();
        $node->deactivate();
        $was_deactivated = !$node->isActive();
        
        // Assert
        $this->assertEquals($was_activated, $was_deactivated);
    }
    
    public function testCheckTransitionPreconditionsValidThen()
    {
        // Arrange
        $node = new ilConditionalNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector1 = new ilSimpleDetector($node);
        $node->addDetector($detector1);
        $detector2 = new ilSimpleDetector($node);
        $node->addDetector($detector2);

        $evaluation_expression =
            '	
	 		$retval = null;
	 		if ($detectors[0]->getDetectorState() == false 
	 			&& $detectors[1]->getDetectorState() == true)
	 		{
	 			$retval = false; 
	 		}
	 		
	 		if ($detectors[0]->getDetectorState() == true
	 			&& $detectors[1]->getDetectorState() == false)
	 		{
	 			$retval = true;
	 		}
	 		return $retval;
			';
        
        $node->setEvaluationExpression($evaluation_expression);
        $detector1->trigger(null);
        
        // Act
        $preconditions = $node->checkTransitionPreconditions();
        
        // Assert
        $this->assertTrue($preconditions);
    }

    public function testCheckTransitionPreconditionsValidElse()
    {
        // Arrange
        $node = new ilConditionalNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector1 = new ilSimpleDetector($node);
        $node->addDetector($detector1);
        $detector2 = new ilSimpleDetector($node);
        $node->addDetector($detector2);

        $evaluation_expression =
            '	
	 		$retval = null;
	 		if ($detectors[0]->getDetectorState() == false 
	 			&& $detectors[1]->getDetectorState() == true)
	 		{
	 			$retval = false; 
	 		}
	 		
	 		if ($detectors[0]->getDetectorState() == true
	 			&& $detectors[1]->getDetectorState() == false)
	 		{
	 			$retval = true;
	 		}
	 		return $retval;
			';
        
        $node->setEvaluationExpression($evaluation_expression);
        $detector2->trigger(null);
        
        // Act
        $preconditions = $node->checkTransitionPreconditions();
        
        // Assert
        $this->assertTrue($preconditions);
    }
    
    public function testCheckTransitionPreconditionsInvalid()
    {
        // Arrange
        $node = new ilConditionalNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector1 = new ilSimpleDetector($node);
        $node->addDetector($detector1);
        $detector2 = new ilSimpleDetector($node);
        $node->addDetector($detector2);
        
        $evaluation_expression =
            '	
	 		$retval = null;
	 		if ($detectors[0]->getDetectorState() == false 
	 			&& $detectors[1]->getDetectorState() == true)
	 		{
	 			$retval = false; 
	 		}
	 		
	 		if ($detectors[0]->getDetectorState() == true
	 			&& $detectors[1]->getDetectorState() == false)
	 		{
	 			$retval = true;
	 		}
	 		return $retval;
			';
        
        $node->setEvaluationExpression($evaluation_expression);
        
        // Act
        $preconditions = $node->checkTransitionPreconditions();
        
        // Assert
        $this->assertFalse($preconditions);
    }
        
    public function testExecuteTransitionThen()
    {
        // Arrange
        $node = new ilConditionalNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector = new ilSimpleDetector($node);
        $node->addDetector($detector);
        $node->activate();
        
        // Act
        $node->executeTransition();
        $state = $node->isActive();
        
        // Assert
        $this->assertFalse($state);
    }

    public function testExecuteTransitionElse()
    { // This is test #100 of the WorkflowEngine, written on 9th of May, 2012
        // @ 14:15

        // Arrange
        $node = new ilConditionalNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector = new ilSimpleDetector($node);
        $node->addDetector($detector);
        $node->activate();
        
        // Act
        $node->executeElseTransition();
        $state = $node->isActive();
        
        // Assert
        $this->assertFalse($state);
    }
    
    public function testExecuteActivitiesViaExecuteTransition()
    {
        // Arrange
        $node = new ilConditionalNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector = new ilSimpleDetector($node);
        $node->addDetector($detector);

        require_once './Services/WorkflowEngine/classes/activities/class.ilLoggingActivity.php';
        $activity = new ilLoggingActivity($node);
        $activity->setLogFile('ilTransitionLog.txt');
        $activity->setLogLevel('MESSAGE');
        $activity->setLogMessage('TEST');
        $node->addActivity($activity);
        
        // Act
        $node->activate();
        $node->executeTransition();
    
        // Assert
        $expected = ' :: MESSAGE :: TEST';
        $fp = fopen('ilTransitionLog.txt', 'r');
        $line = fgets($fp);
        $actual = substr($line, 25, strlen($line)-27);
        @unlink('ilTransitionLog.txt'); // TODO: Use vfsStream
        $this->assertEquals(
            $actual,
            $expected
        );
    }
    
    public function testExecuteElseActivitiesViaExecuteTransition()
    {
        // Arrange
        $node = new ilConditionalNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector = new ilSimpleDetector($node);
        $node->addDetector($detector);

        require_once './Services/WorkflowEngine/classes/activities/class.ilLoggingActivity.php';
        $activity = new ilLoggingActivity($node);
        $activity->setLogFile('ilTransitionLog.txt');
        $activity->setLogLevel('MESSAGE');
        $activity->setLogMessage('TEST');
        $node->addActivity($activity, true);
        
        // Act
        $node->activate();
        $node->executeElseTransition();
    
        // Assert
        $expected = ' :: MESSAGE :: TEST';
        $fp = fopen('ilTransitionLog.txt', 'r');
        $line = fgets($fp);
        $actual = substr($line, 25, strlen($line)-27);
        @unlink('ilTransitionLog.txt'); // TODO: Use vfsStream
        $this->assertEquals(
            $actual,
            $expected
        );
    }
    
    public function testExecuteEmitterViaExecuteTransition()
    {
        // Arrange
        $node = new ilConditionalNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector = new ilSimpleDetector($node);
        $node->addDetector($detector);

        require_once './Services/WorkflowEngine/classes/emitters/class.ilActivationEmitter.php';
        $t_node = new ilBasicNode($this->workflow);
        $t_detector = new ilSimpleDetector($t_node);
        $t_node->addDetector($t_detector);
        $foo_detector = new ilSimpleDetector($t_node);
        $t_node->addDetector($foo_detector);
        // again a foo_detector to keep the t_node from transitting
        
        $emitter = new ilActivationEmitter($node);
        $emitter->setTargetDetector($t_detector);
        $node->addEmitter($emitter);

        // Act
        $node->activate();
        $node->executeTransition();
    
        // Assert
        $this->assertTrue($t_node->isActive());
    }

    public function testExecuteElseEmitterViaExecuteTransition()
    {
        // Arrange
        $node = new ilConditionalNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector = new ilSimpleDetector($node);
        $node->addDetector($detector);

        require_once './Services/WorkflowEngine/classes/emitters/class.ilActivationEmitter.php';
        $t_node = new ilBasicNode($this->workflow);
        $t_detector = new ilSimpleDetector($t_node);
        $t_node->addDetector($t_detector);
        $foo_detector = new ilSimpleDetector($t_node);
        $t_node->addDetector($foo_detector);
        // again a foo_detector to keep the t_node from transitting

        $emitter = new ilActivationEmitter($node);
        $emitter->setTargetDetector($t_detector);
        $node->addEmitter($emitter, true);

        // Act
        $node->activate();
        $node->executeElseTransition();

        // Assert
        $this->assertTrue($t_node->isActive());
    }

    public function testExecuteElseEmitterViaAttemptTransition()
    {
        // Arrange
        $node = new ilConditionalNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector = new ilSimpleDetector($node);
        $node->addDetector($detector);

        require_once './Services/WorkflowEngine/classes/emitters/class.ilActivationEmitter.php';
        $t_node = new ilBasicNode($this->workflow);
        $t_detector = new ilSimpleDetector($t_node);
        $t_node->addDetector($t_detector);
        $foo_detector = new ilSimpleDetector($t_node);
        $t_node->addDetector($foo_detector);
        // again a foo_detector to keep the t_node from transitting

        $emitter = new ilActivationEmitter($node);
        $emitter->setTargetDetector($t_detector);
        $node->addEmitter($emitter, true);
        $node->setEvaluationExpression("return false;");
        // Act
        $node->activate();
        $node->AttemptTransition();

        // Assert
        $this->assertTrue($t_node->isActive());
    }

    public function testAddDetectorFirst()
    {
        // Arrange
        $node = new ilConditionalNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector = new ilSimpleDetector($node);

        // Act
        $node->addDetector($detector);
        $detectors = $node->getDetectors();
        
        // Assert
        $this->assertEquals($detector, $detectors[0]);
    }

    public function testAddEmitterFirst()
    {
        // Arrange
        $node = new ilConditionalNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/emitters/class.ilSimpleEmitter.php';
        $emitter = new ilSimpleEmitter($node);

        // Act
        $node->addEmitter($emitter);
        $emitters = $node->getEmitters();
        
        // Assert
        $this->assertEquals($emitter, $emitters[0]);
    }

    public function testAddElseEmitterFirst()
    {
        // Arrange
        $node = new ilConditionalNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/emitters/class.ilSimpleEmitter.php';
        $emitter = new ilSimpleEmitter($node);

        // Act
        $node->addEmitter($emitter, true);
        $emitters = $node->getEmitters(true);
        
        // Assert
        $this->assertEquals($emitter, $emitters[0]);
    }

    public function testAddActivityFirst()
    {
        // Arrange
        $node = new ilConditionalNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/activities/class.ilLoggingActivity.php';
        $activity = new ilLoggingActivity($node);

        // Act
        $node->addActivity($activity);
        $activities = $node->getActivities();
        
        // Assert
        $this->assertEquals($activity, $activities[0]);
    }

    public function testAddElseActivityFirst()
    {
        // Arrange
        $node = new ilConditionalNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/activities/class.ilLoggingActivity.php';
        $activity = new ilLoggingActivity($node);

        // Act
        $node->addActivity($activity, true);
        $activities = $node->getActivities(true);
        
        // Assert
        $this->assertEquals($activity, $activities[0]);
    }

    public function testNotifyDetectorSatisfaction()
    {
        // Arrange
        $node = new ilConditionalNode($this->workflow);
        
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector = new ilSimpleDetector($node);
        $node->addDetector($detector);
        $detector->setDetectorState(true);
        $node->setEvaluationExpression('return true;');
        
        $node->activate();
        /* Setting the detector to true will actually be reported
         * with notifyDetectorSatisfaction.
         * To isolate this call, we need to reset the node back
         * to active prior to evaluating if it successfully executes
         * the transition and sets itself to inactive.
         */

        // Act
        $node->notifyDetectorSatisfaction($detector);

        // Assert
        $this->assertFalse($node->isActive());
    }

    public function testNotifyDetectorSatisfactionAndTransit()
    {
        // Arrange
        $node = new ilConditionalNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector1 = new ilSimpleDetector($node);
        $node->addDetector($detector1);
        $detector2 = new ilSimpleDetector($node);
        $node->addDetector($detector2);
        $node->setEvaluationExpression('return $GLOBALS["expr_retval"];');
        $GLOBALS['expr_retval'] = null;

        // Act
        $node->activate();
        $this->assertTrue($node->isActive());
        $GLOBALS['expr_retval'] = true;

        $detector1->trigger(null);
        $detector2->trigger(null);
        // TODO: Assert something more meaningful here.
        $this->assertFalse($node->isActive());
    }
}
