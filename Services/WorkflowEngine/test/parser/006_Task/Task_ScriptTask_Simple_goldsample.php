<?php
require_once './Services/WorkflowEngine/classes/workflows/class.ilBaseWorkflow.php';
require_once './Services/WorkflowEngine/classes/nodes/class.ilBasicNode.php';
require_once './Services/WorkflowEngine/classes/activities/class.ilScriptActivity.php';
require_once './Services/WorkflowEngine/classes/emitters/class.ilActivationEmitter.php';
require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';

        class Task_ScriptTask_Simple extends ilBaseWorkflow
        {
            public static $startEventRequired = false;
        
            public function __construct()
            {
                $_v_StartEvent_1 = new ilBasicNode($this);
                $this->addNode($_v_StartEvent_1);
                $_v_StartEvent_1->setName('$_v_StartEvent_1');
        
                $this->setStartNode($_v_StartEvent_1);
            
                $_v_ScriptTask_1 = new ilBasicNode($this);
                $this->addNode($_v_ScriptTask_1);
                $_v_ScriptTask_1->setName('$_v_ScriptTask_1');
        
                $_v_ScriptTask_1_scriptActivity = new ilScriptActivity($_v_ScriptTask_1);
                $_v_ScriptTask_1_scriptActivity->setName('$_v_ScriptTask_1');
                $_v_ScriptTask_1_scriptActivity->setMethod('_v_ScriptTask_1_script');
                $_v_ScriptTask_1->addActivity($_v_ScriptTask_1_scriptActivity);
            
                $_v_EndEvent_1 = new ilBasicNode($this);
                $this->addNode($_v_EndEvent_1);
                $_v_EndEvent_1->setName('$_v_EndEvent_1');
        
                $_v_ScriptTask_1_detector = new ilSimpleDetector($_v_ScriptTask_1);
                $_v_ScriptTask_1_detector->setName('$_v_ScriptTask_1_detector');
                $_v_ScriptTask_1_detector->setSourceNode($_v_StartEvent_1);
                $_v_ScriptTask_1->addDetector($_v_ScriptTask_1_detector);
                $_v_StartEvent_1_emitter = new ilActivationEmitter($_v_StartEvent_1);
                $_v_StartEvent_1_emitter->setName('$_v_StartEvent_1_emitter');
                $_v_StartEvent_1_emitter->setTargetDetector($_v_ScriptTask_1_detector);
                $_v_StartEvent_1->addEmitter($_v_StartEvent_1_emitter);
        
                $_v_EndEvent_1_detector = new ilSimpleDetector($_v_EndEvent_1);
                $_v_EndEvent_1_detector->setName('$_v_EndEvent_1_detector');
                $_v_EndEvent_1_detector->setSourceNode($_v_ScriptTask_1);
                $_v_EndEvent_1->addDetector($_v_EndEvent_1_detector);
                $_v_ScriptTask_1_emitter = new ilActivationEmitter($_v_ScriptTask_1);
                $_v_ScriptTask_1_emitter->setName('$_v_ScriptTask_1_emitter');
                $_v_ScriptTask_1_emitter->setTargetDetector($_v_EndEvent_1_detector);
                $_v_ScriptTask_1->addEmitter($_v_ScriptTask_1_emitter);
            }

            public function _v_ScriptTask_1_script($context)
            {
                require_once './Services/WorkflowEngine/test/parser/006_Task/class.test_006_Task.php';
                test_006_Task::triggerMe();
            }
        }
