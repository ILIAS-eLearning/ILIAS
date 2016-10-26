<?php
require_once './Services/WorkflowEngine/classes/workflows/class.ilBaseWorkflow.php';
require_once './Services/WorkflowEngine/classes/nodes/class.ilBasicNode.php';
require_once './Services/WorkflowEngine/classes/emitters/class.ilActivationEmitter.php';
require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';

		class Task_Simple extends ilBaseWorkflow
		{
		
			public static $startEventRequired = false;
		
			public function __construct()
			{
		
			$_v_StartEvent_2 = new ilBasicNode($this);
			$this->addNode($_v_StartEvent_2);
			$_v_StartEvent_2->setName('$_v_StartEvent_2');
		
			$this->setStartNode($_v_StartEvent_2);
			
			$_v_Task_1 = new ilBasicNode($this);
			$this->addNode($_v_Task_1);
			$_v_Task_1->setName('$_v_Task_1');
		
			$_v_EndEvent_2 = new ilBasicNode($this);
			$this->addNode($_v_EndEvent_2);
			$_v_EndEvent_2->setName('$_v_EndEvent_2');
		
			$_v_Task_1_detector = new ilSimpleDetector($_v_Task_1);
			$_v_Task_1_detector->setName('$_v_Task_1_detector');
			$_v_Task_1_detector->setSourceNode($_v_StartEvent_2);
			$_v_Task_1->addDetector($_v_Task_1_detector);
			$_v_StartEvent_2_emitter = new ilActivationEmitter($_v_StartEvent_2);
			$_v_StartEvent_2_emitter->setName('$_v_StartEvent_2_emitter');
			$_v_StartEvent_2_emitter->setTargetDetector($_v_Task_1_detector);
			$_v_StartEvent_2->addEmitter($_v_StartEvent_2_emitter);
		
			$_v_EndEvent_2_detector = new ilSimpleDetector($_v_EndEvent_2);
			$_v_EndEvent_2_detector->setName('$_v_EndEvent_2_detector');
			$_v_EndEvent_2_detector->setSourceNode($_v_Task_1);
			$_v_EndEvent_2->addDetector($_v_EndEvent_2_detector);
			$_v_Task_1_emitter = new ilActivationEmitter($_v_Task_1);
			$_v_Task_1_emitter->setName('$_v_Task_1_emitter');
			$_v_Task_1_emitter->setTargetDetector($_v_EndEvent_2_detector);
			$_v_Task_1->addEmitter($_v_Task_1_emitter);
		
			}
		}
		
?>