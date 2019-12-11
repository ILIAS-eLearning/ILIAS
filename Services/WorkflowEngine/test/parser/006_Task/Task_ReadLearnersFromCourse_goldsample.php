<?php
require_once './Services/WorkflowEngine/classes/workflows/class.ilBaseWorkflow.php';
require_once './Services/WorkflowEngine/classes/nodes/class.ilBasicNode.php';
require_once './Services/WorkflowEngine/classes/activities/class.ilStaticMethodCallActivity.php';
require_once './Services/WorkflowEngine/classes/detectors/class.ilDataDetector.php';
require_once './Services/WorkflowEngine/classes/emitters/class.ilDataEmitter.php';
require_once './Services/WorkflowEngine/classes/emitters/class.ilActivationEmitter.php';
require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';

        class Task_ReadLearnersFromCourse extends ilBaseWorkflow
        {
            public static $startEventRequired = false;
        
            public function __construct()
            {
                $this->defineInstanceVar("DataInput_1", "dataInput", false, "", "mixed", "undefined");
                $this->registerInputVar("DataInput_1", array());

                $_v_StartEvent_1 = new ilBasicNode($this);
                $this->addNode($_v_StartEvent_1);
                $_v_StartEvent_1->setName('$_v_StartEvent_1');
        
                $this->setStartNode($_v_StartEvent_1);
            
                $_v_CallActivity_1 = new ilBasicNode($this);
                $this->addNode($_v_CallActivity_1);
                $_v_CallActivity_1->setName('$_v_CallActivity_1');
            
                $_v_CallActivity_1_callActivity = new ilStaticMethodCallActivity($_v_CallActivity_1);
                $_v_CallActivity_1_callActivity->setName('$_v_CallActivity_1_callActivity');
                $_v_CallActivity_1_callActivity->setIncludeFilename("Services/WorkflowEngine/test/parser/006_Task/class.test_006_Task.php");
                $_v_CallActivity_1_callActivity->setClassAndMethodName("test_006_Task::requestList");
                $_v_CallActivity_1_callActivity_params = array("DataInput_1");
                $_v_CallActivity_1_callActivity->setParameters($_v_CallActivity_1_callActivity_params);
                $_v_CallActivity_1_callActivity_outputs = array("DataObjectReference_1");
                $_v_CallActivity_1_callActivity->setOutputs($_v_CallActivity_1_callActivity_outputs);
                $_v_CallActivity_1->addActivity($_v_CallActivity_1_callActivity);
        
                $_v_CallActivity_1_inputDataDetector = new ilDataDetector($_v_CallActivity_1);
                $_v_CallActivity_1_inputDataDetector->setVarName("DataInput_1");
                $_v_CallActivity_1_inputDataDetector->setName($_v_CallActivity_1_inputDataDetector);
                $_v_CallActivity_1->addDetector($_v_CallActivity_1_inputDataDetector);
        
                $_v_CallActivity_1_outputDataEmitter = new ilDataEmitter($_v_CallActivity_1);
                $_v_CallActivity_1_outputDataEmitter->setVarName("DataObjectReference_1");
                $_v_CallActivity_1_outputDataEmitter->setName($_v_CallActivity_1_outputDataEmitter);
                $_v_CallActivity_1->addEmitter($_v_CallActivity_1_outputDataEmitter);
        
                $this->defineInstanceVar("DataObject_1", "dataObject", false, "", "mixed", "undefined");
        
                $_v_EndEvent_1 = new ilBasicNode($this);
                $this->addNode($_v_EndEvent_1);
                $_v_EndEvent_1->setName('$_v_EndEvent_1');
        
                $this->defineInstanceVar("DataObjectReference_1", "dataObjectReference", true, "DataObject_1");
        
                $_v_EndEvent_1_detector = new ilSimpleDetector($_v_EndEvent_1);
                $_v_EndEvent_1_detector->setName('$_v_EndEvent_1_detector');
                $_v_EndEvent_1_detector->setSourceNode($_v_CallActivity_1);
                $_v_EndEvent_1->addDetector($_v_EndEvent_1_detector);
                $_v_CallActivity_1_emitter = new ilActivationEmitter($_v_CallActivity_1);
                $_v_CallActivity_1_emitter->setName('$_v_CallActivity_1_emitter');
                $_v_CallActivity_1_emitter->setTargetDetector($_v_EndEvent_1_detector);
                $_v_CallActivity_1->addEmitter($_v_CallActivity_1_emitter);
        
                $_v_CallActivity_1_detector = new ilSimpleDetector($_v_CallActivity_1);
                $_v_CallActivity_1_detector->setName('$_v_CallActivity_1_detector');
                $_v_CallActivity_1_detector->setSourceNode($_v_StartEvent_1);
                $_v_CallActivity_1->addDetector($_v_CallActivity_1_detector);
                $_v_StartEvent_1_emitter = new ilActivationEmitter($_v_StartEvent_1);
                $_v_StartEvent_1_emitter->setName('$_v_StartEvent_1_emitter');
                $_v_StartEvent_1_emitter->setTargetDetector($_v_CallActivity_1_detector);
                $_v_StartEvent_1->addEmitter($_v_StartEvent_1_emitter);
            }
        }
