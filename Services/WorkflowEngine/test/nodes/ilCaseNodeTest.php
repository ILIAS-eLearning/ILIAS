<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilCaseNodeTest is part of the petri net based workflow engine.
 *
 * This class holds all tests for the class
 * nodes/class.ilCaseNode
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilCaseNodeTest extends PHPUnit_Framework_TestCase
{
    /** @var ilEmptyWorkflow $workflow */
    public $workflow;

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
        $node = new ilCaseNode($this->workflow);
        
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
        $node = new ilCaseNode($this->workflow);
        
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
        $node = new ilCaseNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector = new ilSimpleDetector($node);
        $node->addDetector($detector);

        // Act
        $node->activate();
        
        // Assert
        $actual = $node->isActive();
        $this->assertTrue($actual);
    }
    
    public function testDeactivate()
    {
        // Arrange
        $node = new ilCaseNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector = new ilSimpleDetector($node);
        $node->addDetector($detector);

        // Act
        $node->activate();
        $was_activated = $node->isActive();
        $node->deactivate();
        $was_deactivated = !$node->isActive();
        
        // Assert
        $this->assertEquals($was_activated, $was_deactivated);
    }

    public function testCheckTransitionPreconditionsValid()
    {
        // Arrange
        $node = new ilCaseNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector1 = new ilSimpleDetector($node);
        $node->addDetector($detector1);
        $detector2 = new ilSimpleDetector($node);
        $node->addDetector($detector2);

        $detector1->trigger(null);
        $detector2->trigger(null);
        
        // Act
        $preconditions = $node->checkTransitionPreconditions();
        
        // Assert
        $this->assertTrue($preconditions);
    }

    public function testCheckTransitionPreconditionsValidOnExclusiveJoin()
    {
        // Arrange
        $node = new ilCaseNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector1 = new ilSimpleDetector($node);
        $node->addDetector($detector1);
        $detector2 = new ilSimpleDetector($node);
        $node->addDetector($detector2);
        $node->setIsExclusiveJoin(true);
        $detector1->trigger(null);
        $detector2->trigger(null);

        // Act
        $preconditions = $node->checkTransitionPreconditions();

        // Assert
        $this->assertTrue($preconditions);
    }

    public function testNotifyDetectorSatisfactionAndTransit()
    {
        // Arrange
        $node = new ilCaseNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector1 = new ilSimpleDetector($node);
        $node->addDetector($detector1);
        $detector2 = new ilSimpleDetector($node);
        $node->addDetector($detector2);

        // Act
        $node->activate();
        $this->assertTrue($node->isActive());
        $detector1->trigger(null);
        $detector2->trigger(null);
        // TODO: Assert something more meaningful here.
        $this->assertFalse($node->isActive());
    }

    public function testCheckTransitionPreconditionsInvalid()
    {
        // Arrange
        $node = new ilCaseNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector1 = new ilSimpleDetector($node);
        $node->addDetector($detector1);
        $detector2 = new ilSimpleDetector($node);
        $node->addDetector($detector2);

        $detector1->trigger(null);
        //$detector2->trigger(null);

        // Act
        $preconditions = $node->checkTransitionPreconditions();

        // Assert
        $this->assertFalse($preconditions);
    }

    public function testAttemptTransitionPreconditionsValidOnExclusiveJoin()
    {
        // Arrange
        $node = new ilCaseNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector1 = new ilSimpleDetector($node);
        $node->addDetector($detector1);
        $detector2 = new ilSimpleDetector($node);
        $node->addDetector($detector2);
        $node->setIsExclusiveJoin(true);
        $detector1->trigger(null);
        $detector2->trigger(null);

        // Act
        $success = $node->attemptTransition();

        // Assert
        $this->assertTrue($success);
    }

    public function testExecuteTransition()
    {
        // Arrange
        $node = new ilCaseNode($this->workflow);
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

    public function testExecuteTransitionExclusiveFork()
    {
        // Arrange
        $node = new ilCaseNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector = new ilSimpleDetector($node);
        $node->addDetector($detector);
        $node->activate();
        $node->setIsExclusiveFork(true);

        // Act
        $node->executeTransition();
        $state = $node->isActive();

        // Assert
        $this->assertFalse($state);
    }

    public function testExecuteActivitiesViaExecuteTransition()
    {
        // Arrange
        $node = new ilCaseNode($this->workflow);
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
        @unlink('ilTransitionLog.txt'); // TODO: Use vfsStrream
        $this->assertEquals(
            $expected,
            $actual
        );
    }

    public function testExecuteEmitterViaExecuteTransition()
    {
        // Arrange
        $node = new ilCaseNode($this->workflow);
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

    public function testExecuteEmitterViaExecuteTransitionExclusiveFork()
    {
        // Arrange
        $node = new ilCaseNode($this->workflow);
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
        $node->setIsExclusiveFork(true);

        // Act
        $node->activate();
        $node->executeTransition();

        // Assert
        $this->assertTrue($t_node->isActive());
    }

    public function testAddDetectorFirst()
    {
        // Arrange
        $node = new ilCaseNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector = new ilSimpleDetector($node);

        // Act
        $node->addDetector($detector);
        $detectors = $node->getDetectors();
        
        // Assert
        $this->assertEquals($detector, $detectors[0]);
    }

    public function testAddGetActivity()
    {
        // Arrange
        $node = new ilCaseNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/activities/class.ilLoggingActivity.php';
        $activity = new ilLoggingActivity($node);

        // Act
        $node->addActivity($activity);
        $activities = $node->getActivities();

        // Assert
        $this->assertEquals($activity, $activities[0]);
    }
}
