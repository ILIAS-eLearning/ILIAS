<?php
require_once './Services/WorkflowEngine/classes/workflows/class.ilBaseWorkflow.php';
require_once './Services/WorkflowEngine/classes/nodes/class.ilBasicNode.php';
require_once './Services/WorkflowEngine/classes/emitters/class.ilActivationEmitter.php';
require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';

		class ParallelGateway_Joining extends ilBaseWorkflow
		{
		
			public static $startEventRequired = false;
		
			public function __construct()
			{
		
			$_v_StartEvent_1 = new ilBasicNode($this);
			$this->addNode($_v_StartEvent_1);
			$_v_StartEvent_1->setName('$_v_StartEvent_1');
		
			$this->setStartNode($_v_StartEvent_1);
			
			$_v_ParallelGateway_1 = new ilBasicNode($this);
			$_v_ParallelGateway_1->setName('$_v_ParallelGateway_1');
			$this->addNode($_v_ParallelGateway_1);
		
			$_v_IntermediateThrowEvent_1 = new ilBasicNode($this);
			$this->addNode($_v_IntermediateThrowEvent_1);
			$_v_IntermediateThrowEvent_1->setName('$_v_IntermediateThrowEvent_1');
		
			$_v_IntermediateThrowEvent_3 = new ilBasicNode($this);
			$this->addNode($_v_IntermediateThrowEvent_3);
			$_v_IntermediateThrowEvent_3->setName('$_v_IntermediateThrowEvent_3');
		
			$_v_ParallelGateway_2 = new ilBasicNode($this);
			$_v_ParallelGateway_2->setName('$_v_ParallelGateway_2');
			$this->addNode($_v_ParallelGateway_2);
		
			$_v_IntermediateThrowEvent_2 = new ilBasicNode($this);
			$this->addNode($_v_IntermediateThrowEvent_2);
			$_v_IntermediateThrowEvent_2->setName('$_v_IntermediateThrowEvent_2');
		
			$_v_EndEvent_6 = new ilBasicNode($this);
			$this->addNode($_v_EndEvent_6);
			$_v_EndEvent_6->setName('$_v_EndEvent_6');
		
			$_v_ParallelGateway_1_detector = new ilSimpleDetector($_v_ParallelGateway_1);
			$_v_ParallelGateway_1_detector->setName('$_v_ParallelGateway_1_detector');
			$_v_ParallelGateway_1_detector->setSourceNode($_v_StartEvent_1);
			$_v_ParallelGateway_1->addDetector($_v_ParallelGateway_1_detector);
			$_v_StartEvent_1_emitter = new ilActivationEmitter($_v_StartEvent_1);
			$_v_StartEvent_1_emitter->setName('$_v_StartEvent_1_emitter');
			$_v_StartEvent_1_emitter->setTargetDetector($_v_ParallelGateway_1_detector);
			$_v_StartEvent_1->addEmitter($_v_StartEvent_1_emitter);
		
			$_v_IntermediateThrowEvent_1_detector = new ilSimpleDetector($_v_IntermediateThrowEvent_1);
			$_v_IntermediateThrowEvent_1_detector->setName('$_v_IntermediateThrowEvent_1_detector');
			$_v_IntermediateThrowEvent_1_detector->setSourceNode($_v_ParallelGateway_1);
			$_v_IntermediateThrowEvent_1->addDetector($_v_IntermediateThrowEvent_1_detector);
			$_v_ParallelGateway_1_emitter = new ilActivationEmitter($_v_ParallelGateway_1);
			$_v_ParallelGateway_1_emitter->setName('$_v_ParallelGateway_1_emitter');
			$_v_ParallelGateway_1_emitter->setTargetDetector($_v_IntermediateThrowEvent_1_detector);
			$_v_ParallelGateway_1->addEmitter($_v_ParallelGateway_1_emitter);
		
			$_v_IntermediateThrowEvent_2_detector = new ilSimpleDetector($_v_IntermediateThrowEvent_2);
			$_v_IntermediateThrowEvent_2_detector->setName('$_v_IntermediateThrowEvent_2_detector');
			$_v_IntermediateThrowEvent_2_detector->setSourceNode($_v_ParallelGateway_1);
			$_v_IntermediateThrowEvent_2->addDetector($_v_IntermediateThrowEvent_2_detector);
			$_v_ParallelGateway_1_emitter = new ilActivationEmitter($_v_ParallelGateway_1);
			$_v_ParallelGateway_1_emitter->setName('$_v_ParallelGateway_1_emitter');
			$_v_ParallelGateway_1_emitter->setTargetDetector($_v_IntermediateThrowEvent_2_detector);
			$_v_ParallelGateway_1->addEmitter($_v_ParallelGateway_1_emitter);
		
			$_v_IntermediateThrowEvent_3_detector = new ilSimpleDetector($_v_IntermediateThrowEvent_3);
			$_v_IntermediateThrowEvent_3_detector->setName('$_v_IntermediateThrowEvent_3_detector');
			$_v_IntermediateThrowEvent_3_detector->setSourceNode($_v_ParallelGateway_1);
			$_v_IntermediateThrowEvent_3->addDetector($_v_IntermediateThrowEvent_3_detector);
			$_v_ParallelGateway_1_emitter = new ilActivationEmitter($_v_ParallelGateway_1);
			$_v_ParallelGateway_1_emitter->setName('$_v_ParallelGateway_1_emitter');
			$_v_ParallelGateway_1_emitter->setTargetDetector($_v_IntermediateThrowEvent_3_detector);
			$_v_ParallelGateway_1->addEmitter($_v_ParallelGateway_1_emitter);
		
			$_v_ParallelGateway_2_detector = new ilSimpleDetector($_v_ParallelGateway_2);
			$_v_ParallelGateway_2_detector->setName('$_v_ParallelGateway_2_detector');
			$_v_ParallelGateway_2_detector->setSourceNode($_v_IntermediateThrowEvent_1);
			$_v_ParallelGateway_2->addDetector($_v_ParallelGateway_2_detector);
			$_v_IntermediateThrowEvent_1_emitter = new ilActivationEmitter($_v_IntermediateThrowEvent_1);
			$_v_IntermediateThrowEvent_1_emitter->setName('$_v_IntermediateThrowEvent_1_emitter');
			$_v_IntermediateThrowEvent_1_emitter->setTargetDetector($_v_ParallelGateway_2_detector);
			$_v_IntermediateThrowEvent_1->addEmitter($_v_IntermediateThrowEvent_1_emitter);
		
			$_v_ParallelGateway_2_detector = new ilSimpleDetector($_v_ParallelGateway_2);
			$_v_ParallelGateway_2_detector->setName('$_v_ParallelGateway_2_detector');
			$_v_ParallelGateway_2_detector->setSourceNode($_v_IntermediateThrowEvent_3);
			$_v_ParallelGateway_2->addDetector($_v_ParallelGateway_2_detector);
			$_v_IntermediateThrowEvent_3_emitter = new ilActivationEmitter($_v_IntermediateThrowEvent_3);
			$_v_IntermediateThrowEvent_3_emitter->setName('$_v_IntermediateThrowEvent_3_emitter');
			$_v_IntermediateThrowEvent_3_emitter->setTargetDetector($_v_ParallelGateway_2_detector);
			$_v_IntermediateThrowEvent_3->addEmitter($_v_IntermediateThrowEvent_3_emitter);
		
			$_v_ParallelGateway_2_detector = new ilSimpleDetector($_v_ParallelGateway_2);
			$_v_ParallelGateway_2_detector->setName('$_v_ParallelGateway_2_detector');
			$_v_ParallelGateway_2_detector->setSourceNode($_v_IntermediateThrowEvent_2);
			$_v_ParallelGateway_2->addDetector($_v_ParallelGateway_2_detector);
			$_v_IntermediateThrowEvent_2_emitter = new ilActivationEmitter($_v_IntermediateThrowEvent_2);
			$_v_IntermediateThrowEvent_2_emitter->setName('$_v_IntermediateThrowEvent_2_emitter');
			$_v_IntermediateThrowEvent_2_emitter->setTargetDetector($_v_ParallelGateway_2_detector);
			$_v_IntermediateThrowEvent_2->addEmitter($_v_IntermediateThrowEvent_2_emitter);
		
			$_v_EndEvent_6_detector = new ilSimpleDetector($_v_EndEvent_6);
			$_v_EndEvent_6_detector->setName('$_v_EndEvent_6_detector');
			$_v_EndEvent_6_detector->setSourceNode($_v_ParallelGateway_2);
			$_v_EndEvent_6->addDetector($_v_EndEvent_6_detector);
			$_v_ParallelGateway_2_emitter = new ilActivationEmitter($_v_ParallelGateway_2);
			$_v_ParallelGateway_2_emitter->setName('$_v_ParallelGateway_2_emitter');
			$_v_ParallelGateway_2_emitter->setTargetDetector($_v_EndEvent_6_detector);
			$_v_ParallelGateway_2->addEmitter($_v_ParallelGateway_2_emitter);
		
			}
		}
		
?>