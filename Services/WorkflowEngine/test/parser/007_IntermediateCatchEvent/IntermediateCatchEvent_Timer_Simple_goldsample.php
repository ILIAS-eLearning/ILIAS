<?php
require_once './Services/WorkflowEngine/classes/workflows/class.ilBaseWorkflow.php';
require_once './Services/WorkflowEngine/classes/nodes/class.ilBasicNode.php';
require_once './Services/WorkflowEngine/classes/detectors/class.ilEventDetector.php';
require_once './Services/WorkflowEngine/classes/emitters/class.ilActivationEmitter.php';
require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';

        class IntermediateCatchEvent_Timer_Simple extends ilBaseWorkflow
        {
            public static $startEventRequired = false;
        
            public function __construct()
            {
                $_v_EndEvent_1 = new ilBasicNode($this);
                $this->addNode($_v_EndEvent_1);
                $_v_EndEvent_1->setName('$_v_EndEvent_1');
        
                $_v_StartEvent_1 = new ilBasicNode($this);
                $this->addNode($_v_StartEvent_1);
                $_v_StartEvent_1->setName('$_v_StartEvent_1');
        
                $this->setStartNode($_v_StartEvent_1);
            
                $_v_IntermediateCatchEvent_2 = new ilBasicNode($this);
                $this->addNode($_v_IntermediateCatchEvent_2);
                $_v_IntermediateCatchEvent_2->setName('$_v_IntermediateCatchEvent_2');
        
                $_v_IntermediateCatchEvent_2_detector = new ilEventDetector($_v_IntermediateCatchEvent_2);
                $_v_IntermediateCatchEvent_2_detector->setName('$_v_IntermediateCatchEvent_2_detector');
                $_v_IntermediateCatchEvent_2_detector->setEvent("time_passed", "time_passed");
                $_v_IntermediateCatchEvent_2_detector->setEventSubject("none", "0");
                $_v_IntermediateCatchEvent_2_detector->setEventContext("none", "0");
                $_v_IntermediateCatchEvent_2_detector->setListeningTimeframe(1299841994, 0);
                $_v_IntermediateCatchEvent_2->addDetector($_v_IntermediateCatchEvent_2_detector);
            
                $_v_IntermediateCatchEvent_2_detector = new ilSimpleDetector($_v_IntermediateCatchEvent_2);
                $_v_IntermediateCatchEvent_2_detector->setName('$_v_IntermediateCatchEvent_2_detector');
                $_v_IntermediateCatchEvent_2_detector->setSourceNode($_v_StartEvent_1);
                $_v_IntermediateCatchEvent_2->addDetector($_v_IntermediateCatchEvent_2_detector);
                $_v_StartEvent_1_emitter = new ilActivationEmitter($_v_StartEvent_1);
                $_v_StartEvent_1_emitter->setName('$_v_StartEvent_1_emitter');
                $_v_StartEvent_1_emitter->setTargetDetector($_v_IntermediateCatchEvent_2_detector);
                $_v_StartEvent_1->addEmitter($_v_StartEvent_1_emitter);
        
                $_v_EndEvent_1_detector = new ilSimpleDetector($_v_EndEvent_1);
                $_v_EndEvent_1_detector->setName('$_v_EndEvent_1_detector');
                $_v_EndEvent_1_detector->setSourceNode($_v_IntermediateCatchEvent_2);
                $_v_EndEvent_1->addDetector($_v_EndEvent_1_detector);
                $_v_IntermediateCatchEvent_2_emitter = new ilActivationEmitter($_v_IntermediateCatchEvent_2);
                $_v_IntermediateCatchEvent_2_emitter->setName('$_v_IntermediateCatchEvent_2_emitter');
                $_v_IntermediateCatchEvent_2_emitter->setTargetDetector($_v_EndEvent_1_detector);
                $_v_IntermediateCatchEvent_2->addEmitter($_v_IntermediateCatchEvent_2_emitter);
            }
        }
