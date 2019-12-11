<?php
require_once './Services/WorkflowEngine/classes/workflows/class.ilBaseWorkflow.php';
require_once './Services/WorkflowEngine/classes/nodes/class.ilBasicNode.php';
require_once './Services/WorkflowEngine/classes/emitters/class.ilDataEmitter.php';
require_once './Services/WorkflowEngine/classes/emitters/class.ilActivationEmitter.php';
require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';

        class DataObject_Role extends ilBaseWorkflow
        {
            public static $startEventRequired = false;
        
            public function __construct()
            {
                $_v_StartEvent_1 = new ilBasicNode($this);
                $this->addNode($_v_StartEvent_1);
                $_v_StartEvent_1->setName('$_v_StartEvent_1');
        
                $this->setStartNode($_v_StartEvent_1);
            
                $_v_Task_1 = new ilBasicNode($this);
                $this->addNode($_v_Task_1);
                $_v_Task_1->setName('$_v_Task_1');
        
                $_v_Task_1_outputDataEmitter = new ilDataEmitter($_v_Task_1);
                $_v_Task_1_outputDataEmitter->setVarName("DataObjectReference_1");
                $_v_Task_1_outputDataEmitter->setName($_v_Task_1_outputDataEmitter);
                $_v_Task_1->addEmitter($_v_Task_1_outputDataEmitter);
        
                $_v_EndEvent_1 = new ilBasicNode($this);
                $this->addNode($_v_EndEvent_1);
                $_v_EndEvent_1->setName('$_v_EndEvent_1');
        
                $this->defineInstanceVar("DataObject_1", "dataObject", false, "", "integer", "crsRefId");
        
                $this->defineInstanceVar("DataObjectReference_1", "dataObjectReference", true, "DataObject_1");
        
                $_v_Task_1_detector = new ilSimpleDetector($_v_Task_1);
                $_v_Task_1_detector->setName('$_v_Task_1_detector');
                $_v_Task_1_detector->setSourceNode($_v_StartEvent_1);
                $_v_Task_1->addDetector($_v_Task_1_detector);
                $_v_StartEvent_1_emitter = new ilActivationEmitter($_v_StartEvent_1);
                $_v_StartEvent_1_emitter->setName('$_v_StartEvent_1_emitter');
                $_v_StartEvent_1_emitter->setTargetDetector($_v_Task_1_detector);
                $_v_StartEvent_1->addEmitter($_v_StartEvent_1_emitter);
        
                $_v_EndEvent_1_detector = new ilSimpleDetector($_v_EndEvent_1);
                $_v_EndEvent_1_detector->setName('$_v_EndEvent_1_detector');
                $_v_EndEvent_1_detector->setSourceNode($_v_Task_1);
                $_v_EndEvent_1->addDetector($_v_EndEvent_1_detector);
                $_v_Task_1_emitter = new ilActivationEmitter($_v_Task_1);
                $_v_Task_1_emitter->setName('$_v_Task_1_emitter');
                $_v_Task_1_emitter->setTargetDetector($_v_EndEvent_1_detector);
                $_v_Task_1->addEmitter($_v_Task_1_emitter);
            }
        }
