<?php
require_once './Services/WorkflowEngine/classes/workflows/class.ilBaseWorkflow.php';
require_once './Services/WorkflowEngine/classes/nodes/class.ilBasicNode.php';
require_once './Services/WorkflowEngine/classes/detectors/class.ilEventDetector.php';

        class StartEvent_Timer_Date extends ilBaseWorkflow
        {
            public static $startEventRequired = true;
            
            public static function getStartEventInfo()
            {
                $events[] = array(
                    'type' 			=> 'time_passed',
                    'content' 		=> 'time_passed',
                    'subject_type' 	=> 'none',
                    'subject_id'	=> '0',
                    'context_type'	=> 'none',
                    'context_id'	=> '0',
                );
                
                return $events;
            }
            
            public function __construct()
            {
                $_v_StartEvent_1 = new ilBasicNode($this);
                $this->addNode($_v_StartEvent_1);
                $_v_StartEvent_1->setName('$_v_StartEvent_1');
        
                $_v_StartEvent_1_detector = new ilEventDetector($_v_StartEvent_1);
                $_v_StartEvent_1_detector->setName('$_v_StartEvent_1_detector');
                $_v_StartEvent_1_detector->setEvent("time_passed", "time_passed");
                $_v_StartEvent_1_detector->setEventSubject("none", "0");
                $_v_StartEvent_1_detector->setEventContext("none", "0");
                $_v_StartEvent_1_detector->setListeningTimeframe(1399889594, 0);
            }
        }
