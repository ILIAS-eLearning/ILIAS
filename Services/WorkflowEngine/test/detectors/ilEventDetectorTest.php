<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilEventDetectorTest is part of the petri net based workflow engine.
 *
 * This class holds all tests for the class
 * detectors/class.ilEventDetector
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilEventDetectorTest extends ilWorkflowEngineBaseTest
{
    private ilEmptyWorkflow $workflow;
    private ilBasicNode $node;

    protected function setUp() : void
    {
        // Empty workflow.
        $this->workflow = new ilEmptyWorkflow();
        
        // Basic node
        $this->node = new ilBasicNode($this->workflow);
        
        // Wiring up so the node is attached to the workflow.
        $this->workflow->addNode($this->node);
    }
    
    protected function tearDown() : void
    {
        global $DIC;

        if (isset($DIC['ilSetting'])) {
            $DIC['ilSetting']->delete('IL_PHPUNIT_TEST_TIME');
            $DIC['ilSetting']->delete('IL_PHPUNIT_TEST_MICROTIME');
        }
    }
    
    public function testConstructorValidContext() : void
    {
        // Act
        $detector = new ilEventDetector($this->node);
        
        // Assert
        // No exception - good
        $this->assertTrue(
            true,
            'Construction failed with valid context passed to constructor.'
        );
    }

    public function testIsListeningWithTimeFrame() : void
    {
        // Arrange
        $detector = new ilEventDetector($this->node);
        $timer_start = ilWorkflowUtils::time() + 5 * 60; # +5 Minutes from here.
        $timer_end = 0;
        $detector->setListeningTimeframe($timer_start, $timer_end);

        // Act
        $actual = $detector->isListening();

        // Assert
        $this->assertFalse($actual, 'Detector should not be listening.');
    }

    public function testIsListeningWithPastTimeFrame() : void
    {
        // Arrange
        $detector = new ilEventDetector($this->node);
        $timer_start = ilWorkflowUtils::time() - (5 * 60); # -5 Minutes from now.
        /** @noinspection PhpIdempotentOperationInspection Keeping for readability purposes*/
        $timer_end = ilWorkflowUtils::time() - (1 * 60); # -1 Minute from now.
        $detector->setListeningTimeframe($timer_start, $timer_end);

        // Act
        $actual = $detector->isListening();

        // Assert
        $this->assertFalse($actual, 'Detector should not be listening.');
    }

    public function testIsListeningWithWildcardEndingTimeFrame() : void
    {
        // Arrange
        $detector = new ilEventDetector($this->node);
        $timer_start = ilWorkflowUtils::time() - 5 * 60; # -5 Minutes from now.
        $timer_end = 0; # Wildcard.
        $detector->setListeningTimeframe($timer_start, $timer_end);

        // Act
        $actual = $detector->isListening();

        // Assert
        $this->assertTrue($actual, 'Detector should not be listening.');
    }

    public function testIsListeningWithWildcardBeginningTimeFrame() : void
    {
        // Arrange
        $detector = new ilEventDetector($this->node);
        $timer_start = 0; # Wildcard.
        $timer_end = ilWorkflowUtils::time() + 5 * 60; # +5 Minutes from now.
        $detector->setListeningTimeframe($timer_start, $timer_end);

        // Act
        $actual = $detector->isListening();

        // Assert
        $this->assertTrue($actual, 'Detector should not be listening.');
    }

    public function testIsListeningWithoutTimeFrame() : void
    {
        // Arrange
        $detector = new ilEventDetector($this->node);
        
        // Act
        $actual = $detector->isListening();
        
        // Assert
        $this->assertTrue($actual, 'Detector should be listening.');
    }

    public function testSetGetListeningTimeframe() : void
    {
        // Arrange
        $detector = new ilEventDetector($this->node);
        $exp_start = 4711;
        $exp_end = 4712;
        
        // Act
        $detector->setListeningTimeframe($exp_start, $exp_end);
        $act = $detector->getListeningTimeframe();
        
        // Assert
        $this->assertEquals($exp_start . $exp_end, $act['listening_start'] . $act['listening_end']);
    }

    /**
     *
     */
    public function testSetGetIllegalListeningTimeframe() : void
    {
        $this->expectException(ilWorkflowInvalidArgumentException::class);

        // Arrange
        $detector = new ilEventDetector($this->node);
        $exp_start = 4712; # +5 Minutes from here.
        $exp_end = 4711;

        // Act
        $detector->setListeningTimeframe($exp_start, $exp_end);
        $act = $detector->getListeningTimeframe();

        // Assert
        $this->assertEquals($exp_start . $exp_end, $act['listening_start'] . $act['listening_end']);
    }

    public function testSetGetDbId() : void
    {
        // Arrange
        $detector = new ilEventDetector($this->node);
        $expected = '1234';
        
        // Act
        $detector->setDbId($expected);
        $actual = $detector->getDbId();
        
        // Assert
        $this->assertEquals($expected, $actual);
    }

    /**
     *
     */
    public function testGetNonExistingDbId() : void
    {
        $this->expectException(ilWorkflowObjectStateException::class);

        // Arrange
        $detector = new ilEventDetector($this->node);
        $expected = '1234';

        // Act
        $actual = $detector->getDbId();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function testHasDbIdSet() : void
    {
        // Arrange
        $detector = new ilEventDetector($this->node);
        $expected = '1234';
        
        // Act
        $detector->setDbId($expected);
        $actual = $detector->hasDbId();
        
        // Assert
        $this->assertTrue($actual);
    }
    
    public function testHasDbIdUnset() : void
    {
        // Arrange
        $detector = new ilEventDetector($this->node);
        
        // Act
        $actual = $detector->hasDbId();
        
        // Assert
        $this->assertFalse($actual);
    }
    
    public function testSetGetEvent() : void
    {
        // Arrange INC
        $detector = new ilEventDetector($this->node);
        $exp_type = 'time_passed';
        $exp_content = 'time_passed';

        // Act
        $detector->setEvent($exp_type, $exp_content);
        
        // Assert
        $event = $detector->getEvent();
        $act_type = $event['type'];
        $act_content = $event['content'];
        $this->assertEquals($exp_type . $exp_content, $act_type . $act_content);
    }
    
    public function testSetGetEventSubject() : void
    {
        // Arrange INC
        $detector = new ilEventDetector($this->node);
        $exp_type = 'none';
        $exp_id = '0';

        // Act
        $detector->setEventSubject($exp_type, $exp_id);
        
        // Assert
        $event = $detector->getEventSubject();
        $act_type = $event['type'];
        $act_id = $event['identifier'];
        $this->assertEquals($exp_type . $exp_id, $act_type . $act_id);
    }
    
    public function testSetGetEventContext() : void
    {
        // Arrange INC
        $detector = new ilEventDetector($this->node);
        $exp_type = 'none';
        $exp_id = '0';

        // Act
        $detector->setEventContext($exp_type, $exp_id);
        
        // Assert
        $event = $detector->getEventContext();
        $act_type = $event['type'];
        $act_id = $event['identifier'];
        $this->assertEquals($exp_type . $exp_id, $act_type . $act_id);
    }
    
    public function testGetContext() : void
    {
        // Arrange
        $detector = new ilEventDetector($this->node);
        
        // Act
        $actual = $detector->getContext();
        
        // Assert
        if ($actual === $this->node) {
            $this->assertEquals($actual, $this->node);
        } else {
            $this->fail('Context not identical.');
        }
    }

    public function testTriggerValid() : void
    {
        // Arrange
        $detector = new ilEventDetector($this->node);
        $evt_type = 'testEvent';
        $evt_content = 'content';
        $detector->setEvent($evt_type, $evt_content);
        $subj_type = 'usr';
        $subj_id = 6;
        $detector->setEventSubject($subj_type, $subj_id);
        $ctx_type = 'crs';
        $ctx_id = 48;
        $detector->setEventContext($ctx_type, $ctx_id);
        $params = array(
            $evt_type, $evt_content,
            $subj_type, $subj_id,
            $ctx_type, $ctx_id
        );
        
        // Act
        $detector->trigger($params);
        
        // Assert
        $actual = $detector->getDetectorState();
        $this->assertTrue($actual);
    }

    public function testTriggerValidTwice() : void
    {
        // Arrange
        $detector = new ilEventDetector($this->node);
        $evt_type = 'testEvent';
        $evt_content = 'content';
        $detector->setEvent($evt_type, $evt_content);
        $subj_type = 'usr';
        $subj_id = 6;
        $detector->setEventSubject($subj_type, $subj_id);
        $ctx_type = 'crs';
        $ctx_id = 48;
        $detector->setEventContext($ctx_type, $ctx_id);
        $params = array(
            $evt_type, $evt_content,
            $subj_type, $subj_id,
            $ctx_type, $ctx_id
        );

        // Act
        $this->assertTrue($detector->trigger($params), 'First trigger should receive a true state.');
        $this->assertFalse($detector->trigger($params), 'Second trigger should receive a false state.');

        // Assert
        $actual = $detector->getDetectorState();
        $this->assertTrue($actual, 'After satisfaction of the trigger, detectorstate should be true.');
    }

    // TODO Test wildcards!

    public function testTriggerInvalidContent() : void
    {
        // Arrange
        $detector = new ilEventDetector($this->node);
        $evt_type = 'testEvent';
        $evt_content = 'content';
        $detector->setEvent($evt_type, $evt_content);
        $subj_type = 'usr';
        $subj_id = 6;
        $detector->setEventSubject($subj_type, $subj_id);
        $ctx_type = 'crs';
        $ctx_id = 48;
        $detector->setEventContext($ctx_type, $ctx_id);
        $params = array(
            $evt_type, $evt_content . 'INVALIDATE',
            $subj_type, $subj_id,
            $ctx_type, $ctx_id
        );
        
        // Act
        $detector->trigger($params);
        
        // Assert
        $actual = $detector->getDetectorState();
        $this->assertFalse($actual);
    }

    public function testTriggerInvalidType() : void
    {
        // Arrange
        $detector = new ilEventDetector($this->node);
        $evt_type = 'testEvent';
        $evt_content = 'content';
        $detector->setEvent($evt_type, $evt_content);
        $subj_type = 'usr';
        $subj_id = 6;
        $detector->setEventSubject($subj_type, $subj_id);
        $ctx_type = 'crs';
        $ctx_id = 48;
        $detector->setEventContext($ctx_type, $ctx_id);
        $params = array(
            $evt_type . 'INVALIDATE', $evt_content,
            $subj_type, $subj_id,
            $ctx_type, $ctx_id
        );

        // Act
        $detector->trigger($params);

        // Assert
        $actual = $detector->getDetectorState();
        $this->assertFalse($actual);
    }

    public function testTriggerInvalidSubjectType() : void
    {
        // Arrange
        $detector = new ilEventDetector($this->node);
        $evt_type = 'testEvent';
        $evt_content = 'content';
        $detector->setEvent($evt_type, $evt_content);
        $subj_type = 'usr';
        $subj_id = 6;
        $detector->setEventSubject($subj_type, $subj_id);
        $ctx_type = 'crs';
        $ctx_id = 48;
        $detector->setEventContext($ctx_type, $ctx_id);
        $params = array(
            $evt_type, $evt_content,
            $subj_type . 'INVALIDATE', $subj_id,
            $ctx_type, $ctx_id
        );

        // Act
        $detector->trigger($params);

        // Assert
        $actual = $detector->getDetectorState();
        $this->assertFalse($actual);
    }

    public function testTriggerInvalidSubjectId() : void
    {
        // Arrange
        $detector = new ilEventDetector($this->node);
        $evt_type = 'testEvent';
        $evt_content = 'content';
        $detector->setEvent($evt_type, $evt_content);
        $subj_type = 'usr';
        $subj_id = 6;
        $detector->setEventSubject($subj_type, $subj_id);
        $ctx_type = 'crs';
        $ctx_id = 48;
        $detector->setEventContext($ctx_type, $ctx_id);
        $params = array(
            $evt_type, $evt_content,
            $subj_type, $subj_id . 'INVALIDATE',
            $ctx_type, $ctx_id
        );

        // Act
        $detector->trigger($params);

        // Assert
        $actual = $detector->getDetectorState();
        $this->assertFalse($actual);
    }

    public function testTriggerInvalidContextType() : void
    {
        // Arrange
        $detector = new ilEventDetector($this->node);
        $evt_type = 'testEvent';
        $evt_content = 'content';
        $detector->setEvent($evt_type, $evt_content);
        $subj_type = 'usr';
        $subj_id = 6;
        $detector->setEventSubject($subj_type, $subj_id);
        $ctx_type = 'crs';
        $ctx_id = 48;
        $detector->setEventContext($ctx_type, $ctx_id);
        $params = array(
            $evt_type, $evt_content,
            $subj_type, $subj_id,
            $ctx_type . 'INVALIDATE', $ctx_id
        );

        // Act
        $detector->trigger($params);

        // Assert
        $actual = $detector->getDetectorState();
        $this->assertFalse($actual);
    }

    public function testTriggerInvalidContextId() : void
    {
        // Arrange
        $detector = new ilEventDetector($this->node);
        $evt_type = 'testEvent';
        $evt_content = 'content';
        $detector->setEvent($evt_type, $evt_content);
        $subj_type = 'usr';
        $subj_id = 6;
        $detector->setEventSubject($subj_type, $subj_id);
        $ctx_type = 'crs';
        $ctx_id = 48;
        $detector->setEventContext($ctx_type, $ctx_id);
        $params = array(
            $evt_type, $evt_content,
            $subj_type, $subj_id,
            $ctx_type, $ctx_id . 'INVALIDATE'
        );

        // Act
        $detector->trigger($params);

        // Assert
        $actual = $detector->getDetectorState();
        $this->assertFalse($actual);
    }
}
