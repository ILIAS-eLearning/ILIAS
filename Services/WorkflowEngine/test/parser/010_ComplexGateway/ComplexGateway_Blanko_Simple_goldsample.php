<?php
require_once './Services/WorkflowEngine/classes/workflows/class.ilBaseWorkflow.php';
require_once './Services/WorkflowEngine/classes/nodes/class.ilBasicNode.php';
require_once './Services/WorkflowEngine/classes/nodes/class.ilPluginNode.php';
require_once './Services/WorkflowEngine/classes/emitters/class.ilActivationEmitter.php';
require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';

		class ComplexGateway_Blanko_Simple extends ilBaseWorkflow
		{
		
			public static $startEventRequired = false;
		
			public function __construct()
			{
		
			$_v_StartEvent_1 = new ilBasicNode($this);
			$this->addNode($_v_StartEvent_1);
		
			$this->setStartNode($_v_StartEvent_1);
			
			$_v_ComplexGateway_2 = new ilPluginNode($this);
			// Details how this works need to be further carved out.
			$this->addNode($_v_ComplexGateway_2);
		
			$_v_EndEvent_2 = new ilBasicNode($this);
			$this->addNode($_v_EndEvent_2);
		
			$_v_ComplexGateway_2_detector = new ilSimpleDetector($_v_ComplexGateway_2);
			$_v_ComplexGateway_2->addDetector($_v_ComplexGateway_2_detector);
			$_v_StartEvent_1_emitter = new ilActivationEmitter($_v_StartEvent_1);
			$_v_StartEvent_1_emitter->setTargetDetector($_v_ComplexGateway_2_detector);
			$_v_StartEvent_1->addEmitter($_v_StartEvent_1_emitter);
		
			$_v_EndEvent_2_detector = new ilSimpleDetector($_v_EndEvent_2);
			$_v_EndEvent_2->addDetector($_v_EndEvent_2_detector);
			$_v_ComplexGateway_2_emitter = new ilActivationEmitter($_v_ComplexGateway_2);
			$_v_ComplexGateway_2_emitter->setTargetDetector($_v_EndEvent_2_detector);
			$_v_ComplexGateway_2->addEmitter($_v_ComplexGateway_2_emitter);
		
			}
		}
		
?>