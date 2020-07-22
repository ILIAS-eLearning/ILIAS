<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilTimerDetectorTest is part of the petri net based workflow engine.
 *
 * This class holds all tests for the class
 * detectors/class.ilTimerDetector
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilTimerDetectorTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        //ilUnitUtil::performInitialisation();
        
        require_once './Services/WorkflowEngine/classes/utils/class.ilWorkflowUtils.php';
        
        // Empty workflow.
        require_once './Services/WorkflowEngine/classes/workflows/class.ilEmptyWorkflow.php';
        $this->workflow = new ilEmptyWorkflow();
        
        // Basic node
        require_once './Services/WorkflowEngine/classes/nodes/class.ilBasicNode.php';
        $this->node = new ilBasicNode($this->workflow);
        
        // Wiring up so the node is attached to the workflow.
        $this->workflow->addNode($this->node);
        
        require_once './Services/WorkflowEngine/classes/detectors/class.ilTimerDetector.php';
    }
    
    public function tearDown()
    {
        global $ilSetting;
        if ($ilSetting != null) {
            $ilSetting->delete('IL_PHPUNIT_TEST_TIME');
            $ilSetting->delete('IL_PHPUNIT_TEST_MICROTIME');
        }
    }
    
    public function testConstructorValidContext()
    {
        // Act
        $detector = new ilTimerDetector($this->node);
        
        // Assert
        // No exception - good
        $this->assertTrue(
            true,
            'Construction failed with valid context passed to constructor.'
        );
    }

    public function testSetGetTimerStart()
    {
        // Arrange
        $detector = new ilTimerDetector($this->node);
        $expected = ilWorkflowUtils::time();
        
        // Act
        $detector->setTimerStart($expected);
        $actual = $detector->getTimerStart();
        
        // Assert
        $this->assertEquals($actual, $expected);
    }
    
    public function testSetGetTimerLimit()
    {
        // Arrange
        $detector = new ilTimerDetector($this->node);
        $expected = 5 * 60 * 60;
        
        // Act
        $detector->setTimerLimit($expected);
        $actual = $detector->getTimerLimit();
        
        // Assert
        $this->assertEquals($actual, $expected);
    }

    public function testTriggerEarly()
    {
        // Arrange
        $detector = new ilTimerDetector($this->node);
        $timer_start = ilWorkflowUtils::time(); # +5 Minutes from here.
        $timer_limit = 5 * 60;
        $detector->setTimerStart($timer_start);
        $detector->setTimerLimit($timer_limit);
        
        // Act
        $detector->trigger(null);
        
        // Assert
        $actual = $detector->getDetectorState();
        $this->assertFalse($actual, 'Early trigger should not satisfy detector');
    }

    public function testTriggerValid()
    {
        // Arrange
        $detector = new ilTimerDetector($this->node);
        $timer_start = ilWorkflowUtils::time(); # +5 Minutes from now.
        $timer_limit = 0;
        $detector->setTimerStart($timer_start);
        $detector->setTimerLimit($timer_limit);
        
        // Act
        $detector->trigger(null);
        
        // Assert
        $actual = $detector->getDetectorState();
        $this->assertTrue($actual, 'Trigger should not satisfy detector');
    }

    public function testTriggerValidTwice()
    {
        // Arrange
        $detector = new ilTimerDetector($this->node);
        $timer_start = ilWorkflowUtils::time(); # +5 Minutes from now.
        $timer_limit = 0;
        $detector->setTimerStart($timer_start);
        $detector->setTimerLimit($timer_limit);

        // Act
        $detector->trigger(null);
        $actual = $detector->trigger(null);

        // Assert
        $this->assertFalse($actual, 'Detector should be satisfied after single trigger');
    }

    public function testIsListeningWithTimeFrame()
    {
        // Arrange
        $detector = new ilTimerDetector($this->node);
        $timer_start = ilWorkflowUtils::time() + 5 * 60; # +5 Minutes from here.
        $timer_end = 0;
        $detector->setListeningTimeframe($timer_start, $timer_end);
        
        // Act
        $actual = $detector->isListening();
        
        // Assert
        $this->assertFalse($actual, 'Detector should not be listening.');
    }
    
    public function testIsListeningWithoutTimeFrame()
    {
        // Arrange
        $detector = new ilTimerDetector($this->node);
        $timer_start = ilWorkflowUtils::time() + 5 * 60; # +5 Minutes from here.
        $timer_end = 0;
        
        // Act
        $actual = $detector->isListening();
        
        // Assert
        $this->assertTrue($actual, 'Detector should be listening.');
    }

    /**
     * @expectedException ilWorkflowInvalidArgumentException
     */
    public function testSetGetIllegalListeningTimeframe()
    {
        // Arrange
        $detector = new ilTimerDetector($this->node);
        $exp_start = 4712;
        $exp_end = 4711;

        // Act
        $detector->setListeningTimeframe($exp_start, $exp_end);
        $act = $detector->getListeningTimeframe();

        // Assert
        $this->assertEquals($exp_start . $exp_end, $act['listening_start'] . $act['listening_end']);
    }

    public function testIsListeningWithPastTimeFrame()
    {
        // Arrange
        $detector = new ilTimerDetector($this->node);
        $timer_start = ilWorkflowUtils::time() - 5 * 60; # -5 Minutes from now.
        $timer_end = ilWorkflowUtils::time() - 1 * 60; # -1 Minute from now.
        $detector->setListeningTimeframe($timer_start, $timer_end);

        // Act
        $actual = $detector->isListening();

        // Assert
        $this->assertFalse($actual, 'Detector should not be listening.');
    }

    public function testIsListeningWithWildcardEndingTimeFrame()
    {
        // Arrange
        $detector = new ilTimerDetector($this->node);
        $timer_start = ilWorkflowUtils::time() - 5 * 60; # -5 Minutes from now.
        $timer_end = 0; # Wildcard.
        $detector->setListeningTimeframe($timer_start, $timer_end);

        // Act
        $actual = $detector->isListening();

        // Assert
        $this->assertTrue($actual, 'Detector should not be listening.');
    }

    public function testIsListeningWithWildcardBeginningTimeFrame()
    {
        // Arrange
        $detector = new ilTimerDetector($this->node);
        $timer_start = 0; # Wildcard.
        $timer_end = ilWorkflowUtils::time() + 5 * 60; # +5 Minutes from now.
        $detector->setListeningTimeframe($timer_start, $timer_end);

        // Act
        $actual = $detector->isListening();

        // Assert
        $this->assertTrue($actual, 'Detector should not be listening.');
    }

    public function testSetGetListeningTimeframe()
    {
        // Arrange
        $detector = new ilTimerDetector($this->node);
        $exp_start = 4711; # +5 Minutes from here.
        $exp_end = 4712;
        
        // Act
        $detector->setListeningTimeframe($exp_start, $exp_end);
        $act = $detector->getListeningTimeframe();
        
        // Assert
        $this->assertEquals($exp_start . $exp_end, $act['listening_start'] . $act['listening_end']);
    }
    
    public function testSetGetDbId()
    {
        // Arrange
        $detector = new ilTimerDetector($this->node);
        $expected = '1234';
        
        // Act
        $detector->setDbId($expected);
        $actual = $detector->getDbId();
        
        // Assert
        $this->assertEquals($expected, $actual);
    }
    
    public function testHasDbIdSet()
    {
        // Arrange
        $detector = new ilTimerDetector($this->node);
        $expected = '1234';
        
        // Act
        $detector->setDbId($expected);
        $actual = $detector->hasDbId();
        
        // Assert
        $this->assertTrue($actual);
    }

    /**
     * @expectedException ilWorkflowObjectStateException
     */
    public function testGetNonExistingDbId()
    {
        // Arrange
        $detector = new ilTimerDetector($this->node);
        $expected = '1234';

        // Act
        $actual = $detector->getDbId();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function testHasDbIdUnset()
    {
        // Arrange
        $detector = new ilTimerDetector($this->node);
        
        // Act
        $actual = $detector->hasDbId();
        
        // Assert
        $this->assertFalse($actual);
    }

    public function testGetEvent()
    {
        // Arrange
        $detector = new ilTimerDetector($this->node);
        $exp_type = 'time_passed';
        $exp_content = 'time_passed';

        // Act
        
        // Assert
        $event = $detector->getEvent();
        $act_type = $event['type'];
        $act_content = $event['content'];
        $this->assertEquals($exp_type . $exp_content, $act_type . $act_content);
    }
    
    public function testGetEventSubject()
    {
        // Arrange
        $detector = new ilTimerDetector($this->node);
        $exp_type = 'none';
        $exp_id = '0';

        // Act
        
        // Assert
        $event = $detector->getEventSubject();
        $act_type = $event['type'];
        $act_id = $event['identifier'];
        $this->assertEquals($exp_type . $exp_id, $act_type . $act_id);
    }
    
    public function testGetEventContext()
    {
        // Arrange
        $detector = new ilTimerDetector($this->node);
        $exp_type = 'none';
        $exp_id = '0';

        // Act
        
        // Assert
        $event = $detector->getEventContext();
        $act_type = $event['type'];
        $act_id = $event['identifier'];
        $this->assertEquals($exp_type . $exp_id, $act_type . $act_id);
    }
    
    public function testGetContext()
    {
        // Arrange
        $detector = new ilTimerDetector($this->node);
        
        // Act
        $actual = $detector->getContext();
        
        // Assert
        if ($actual === $this->node) {
            $this->assertEquals($actual, $this->node);
        } else {
            $this->assertTrue(false, 'Context not identical.');
        }
    }
}
