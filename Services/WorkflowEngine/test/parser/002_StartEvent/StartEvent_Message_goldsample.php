<?php
require_once './Services/WorkflowEngine/classes/workflows/class.ilBaseWorkflow.php';
require_once './Services/WorkflowEngine/classes/nodes/class.ilBasicNode.php';
require_once './Services/WorkflowEngine/classes/detectors/class.ilEventDetector.php';

        class StartEvent_Message extends ilBaseWorkflow
        {
            public static $startEventRequired = true;
            
            public static function getStartEventInfo()
            {
                $events[] = array(
                    'type' 			=> 'Course',
                    'content' 		=> 'UserWasAssigned',
                    'subject_type' 	=> 'usr',
                    'subject_id'	=> '0',
                    'context_type'	=> 'crs',
                    'context_id'	=> '0',
                );
                
                return $events;
            }
            
            public function __construct()
            {
                $_v_StartEvent_2 = new ilBasicNode($this);
                $this->addNode($_v_StartEvent_2);
                $_v_StartEvent_2->setName('$_v_StartEvent_2');
        
                $_v_StartEvent_2_detector = new ilEventDetector($_v_StartEvent_2);
                $_v_StartEvent_2_detector->setName('$_v_StartEvent_2_detector');
                $_v_StartEvent_2_detector->setEvent("Course", "UserWasAssigned");
                $_v_StartEvent_2_detector->setEventSubject("usr", "0");
                $_v_StartEvent_2_detector->setEventContext("crs", "0");
                $_v_StartEvent_2_detector->setListeningTimeframe(0, 0);
            }

            
            public static function getMessageDefinition($id)
            {
                $definitions = array( 'Message_3' =>  array(
        'name' => 'ILIASEvent::Course::UserWasAssigned',
        'content' => '')
                );
                return $definitions[$id];
            }
        }
