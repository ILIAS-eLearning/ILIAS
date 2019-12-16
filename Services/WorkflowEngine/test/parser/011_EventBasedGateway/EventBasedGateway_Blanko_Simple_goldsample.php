<?php
require_once './Services/WorkflowEngine/classes/workflows/class.ilBaseWorkflow.php';
require_once './Services/WorkflowEngine/classes/nodes/class.ilBasicNode.php';
require_once './Services/WorkflowEngine/classes/detectors/class.ilEventDetector.php';
require_once './Services/WorkflowEngine/classes/nodes/class.ilCaseNode.php';
require_once './Services/WorkflowEngine/classes/emitters/class.ilActivationEmitter.php';
require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';

		class EventBasedGateway_Blanko_Simple extends ilBaseWorkflow
		{
		
			public static $startEventRequired = false;
		
			public function __construct()
			{
		
			$_v_EventBasedGateway_1 = new ilBasicNode($this);
			$_v_EventBasedGateway_1->setName('$_v_EventBasedGateway_1');
			$_v_EventBasedGateway_1->setIsForwardConditionNode(true);
			$this->addNode($_v_EventBasedGateway_1);
		
			$_v_IntermediateCatchEvent_1 = new ilBasicNode($this);
			$this->addNode($_v_IntermediateCatchEvent_1);
			$_v_IntermediateCatchEvent_1->setName('$_v_IntermediateCatchEvent_1');
		
			$_v_IntermediateCatchEvent_1_detector = new ilEventDetector($_v_IntermediateCatchEvent_1);
			$_v_IntermediateCatchEvent_1_detector->setName('$_v_IntermediateCatchEvent_1_detector');
			$_v_IntermediateCatchEvent_1_detector->setEvent(			"Course", 			"UserWasAssigned");
			$_v_IntermediateCatchEvent_1_detector->setEventSubject(	"usr", 	"0");
			$_v_IntermediateCatchEvent_1_detector->setEventContext(	"crs", 	"0");
			$_v_IntermediateCatchEvent_1_detector->setListeningTimeframe(0, 0);
			$_v_IntermediateCatchEvent_1->addDetector($_v_IntermediateCatchEvent_1_detector);
			
			$_v_IntermediateCatchEvent_2 = new ilBasicNode($this);
			$this->addNode($_v_IntermediateCatchEvent_2);
			$_v_IntermediateCatchEvent_2->setName('$_v_IntermediateCatchEvent_2');
		
			$_v_IntermediateCatchEvent_2_detector = new ilEventDetector($_v_IntermediateCatchEvent_2);
			$_v_IntermediateCatchEvent_2_detector->setName('$_v_IntermediateCatchEvent_2_detector');
			$_v_IntermediateCatchEvent_2_detector->setEvent(			"Course", 			"UserWasDeassigned");
			$_v_IntermediateCatchEvent_2_detector->setEventSubject(	"usr", 	"0");
			$_v_IntermediateCatchEvent_2_detector->setEventContext(	"crs", 	"0");
			$_v_IntermediateCatchEvent_2_detector->setListeningTimeframe(0, 0);
			$_v_IntermediateCatchEvent_2->addDetector($_v_IntermediateCatchEvent_2_detector);
			
			$_v_IntermediateCatchEvent_3 = new ilBasicNode($this);
			$this->addNode($_v_IntermediateCatchEvent_3);
			$_v_IntermediateCatchEvent_3->setName('$_v_IntermediateCatchEvent_3');
		
			$_v_IntermediateCatchEvent_3_detector = new ilEventDetector($_v_IntermediateCatchEvent_3);
			$_v_IntermediateCatchEvent_3_detector->setName('$_v_IntermediateCatchEvent_3_detector');
			$_v_IntermediateCatchEvent_3_detector->setEvent(			"Course", 			"UserAassignmentChanged");
			$_v_IntermediateCatchEvent_3_detector->setEventSubject(	"usr", 	"0");
			$_v_IntermediateCatchEvent_3_detector->setEventContext(	"crs", 	"0");
			$_v_IntermediateCatchEvent_3_detector->setListeningTimeframe(0, 0);
			$_v_IntermediateCatchEvent_3->addDetector($_v_IntermediateCatchEvent_3_detector);
			
			$_v_StartEvent_1 = new ilBasicNode($this);
			$this->addNode($_v_StartEvent_1);
			$_v_StartEvent_1->setName('$_v_StartEvent_1');
		
			$this->setStartNode($_v_StartEvent_1);
			
			$_v_ExclusiveGateway_1 = new ilCaseNode($this);
			$_v_ExclusiveGateway_1->setName('$_v_ExclusiveGateway_1');
			$_v_ExclusiveGateway_1->setIsExclusiveJoin(true);
			$this->addNode($_v_ExclusiveGateway_1);
		
			$_v_EndEvent_1 = new ilBasicNode($this);
			$this->addNode($_v_EndEvent_1);
			$_v_EndEvent_1->setName('$_v_EndEvent_1');
		
			$_v_IntermediateCatchEvent_1_detector = new ilSimpleDetector($_v_IntermediateCatchEvent_1);
			$_v_IntermediateCatchEvent_1_detector->setName('$_v_IntermediateCatchEvent_1_detector');
			$_v_IntermediateCatchEvent_1_detector->setSourceNode($_v_EventBasedGateway_1);
			$_v_IntermediateCatchEvent_1->addDetector($_v_IntermediateCatchEvent_1_detector);
			$_v_EventBasedGateway_1_emitter = new ilActivationEmitter($_v_EventBasedGateway_1);
			$_v_EventBasedGateway_1_emitter->setName('$_v_EventBasedGateway_1_emitter');
			$_v_EventBasedGateway_1_emitter->setTargetDetector($_v_IntermediateCatchEvent_1_detector);
			$_v_EventBasedGateway_1->addEmitter($_v_EventBasedGateway_1_emitter);
		
			$_v_IntermediateCatchEvent_2_detector = new ilSimpleDetector($_v_IntermediateCatchEvent_2);
			$_v_IntermediateCatchEvent_2_detector->setName('$_v_IntermediateCatchEvent_2_detector');
			$_v_IntermediateCatchEvent_2_detector->setSourceNode($_v_EventBasedGateway_1);
			$_v_IntermediateCatchEvent_2->addDetector($_v_IntermediateCatchEvent_2_detector);
			$_v_EventBasedGateway_1_emitter = new ilActivationEmitter($_v_EventBasedGateway_1);
			$_v_EventBasedGateway_1_emitter->setName('$_v_EventBasedGateway_1_emitter');
			$_v_EventBasedGateway_1_emitter->setTargetDetector($_v_IntermediateCatchEvent_2_detector);
			$_v_EventBasedGateway_1->addEmitter($_v_EventBasedGateway_1_emitter);
		
			$_v_IntermediateCatchEvent_3_detector = new ilSimpleDetector($_v_IntermediateCatchEvent_3);
			$_v_IntermediateCatchEvent_3_detector->setName('$_v_IntermediateCatchEvent_3_detector');
			$_v_IntermediateCatchEvent_3_detector->setSourceNode($_v_EventBasedGateway_1);
			$_v_IntermediateCatchEvent_3->addDetector($_v_IntermediateCatchEvent_3_detector);
			$_v_EventBasedGateway_1_emitter = new ilActivationEmitter($_v_EventBasedGateway_1);
			$_v_EventBasedGateway_1_emitter->setName('$_v_EventBasedGateway_1_emitter');
			$_v_EventBasedGateway_1_emitter->setTargetDetector($_v_IntermediateCatchEvent_3_detector);
			$_v_EventBasedGateway_1->addEmitter($_v_EventBasedGateway_1_emitter);
		
			$_v_EventBasedGateway_1_detector = new ilSimpleDetector($_v_EventBasedGateway_1);
			$_v_EventBasedGateway_1_detector->setName('$_v_EventBasedGateway_1_detector');
			$_v_EventBasedGateway_1_detector->setSourceNode($_v_StartEvent_1);
			$_v_EventBasedGateway_1->addDetector($_v_EventBasedGateway_1_detector);
			$_v_StartEvent_1_emitter = new ilActivationEmitter($_v_StartEvent_1);
			$_v_StartEvent_1_emitter->setName('$_v_StartEvent_1_emitter');
			$_v_StartEvent_1_emitter->setTargetDetector($_v_EventBasedGateway_1_detector);
			$_v_StartEvent_1->addEmitter($_v_StartEvent_1_emitter);
		
			$_v_ExclusiveGateway_1_detector = new ilSimpleDetector($_v_ExclusiveGateway_1);
			$_v_ExclusiveGateway_1_detector->setName('$_v_ExclusiveGateway_1_detector');
			$_v_ExclusiveGateway_1_detector->setSourceNode($_v_IntermediateCatchEvent_1);
			$_v_ExclusiveGateway_1->addDetector($_v_ExclusiveGateway_1_detector);
			$_v_IntermediateCatchEvent_1_emitter = new ilActivationEmitter($_v_IntermediateCatchEvent_1);
			$_v_IntermediateCatchEvent_1_emitter->setName('$_v_IntermediateCatchEvent_1_emitter');
			$_v_IntermediateCatchEvent_1_emitter->setTargetDetector($_v_ExclusiveGateway_1_detector);
			$_v_IntermediateCatchEvent_1->addEmitter($_v_IntermediateCatchEvent_1_emitter);
		
			$_v_ExclusiveGateway_1_detector = new ilSimpleDetector($_v_ExclusiveGateway_1);
			$_v_ExclusiveGateway_1_detector->setName('$_v_ExclusiveGateway_1_detector');
			$_v_ExclusiveGateway_1_detector->setSourceNode($_v_IntermediateCatchEvent_2);
			$_v_ExclusiveGateway_1->addDetector($_v_ExclusiveGateway_1_detector);
			$_v_IntermediateCatchEvent_2_emitter = new ilActivationEmitter($_v_IntermediateCatchEvent_2);
			$_v_IntermediateCatchEvent_2_emitter->setName('$_v_IntermediateCatchEvent_2_emitter');
			$_v_IntermediateCatchEvent_2_emitter->setTargetDetector($_v_ExclusiveGateway_1_detector);
			$_v_IntermediateCatchEvent_2->addEmitter($_v_IntermediateCatchEvent_2_emitter);
		
			$_v_ExclusiveGateway_1_detector = new ilSimpleDetector($_v_ExclusiveGateway_1);
			$_v_ExclusiveGateway_1_detector->setName('$_v_ExclusiveGateway_1_detector');
			$_v_ExclusiveGateway_1_detector->setSourceNode($_v_IntermediateCatchEvent_3);
			$_v_ExclusiveGateway_1->addDetector($_v_ExclusiveGateway_1_detector);
			$_v_IntermediateCatchEvent_3_emitter = new ilActivationEmitter($_v_IntermediateCatchEvent_3);
			$_v_IntermediateCatchEvent_3_emitter->setName('$_v_IntermediateCatchEvent_3_emitter');
			$_v_IntermediateCatchEvent_3_emitter->setTargetDetector($_v_ExclusiveGateway_1_detector);
			$_v_IntermediateCatchEvent_3->addEmitter($_v_IntermediateCatchEvent_3_emitter);
		
			$_v_EndEvent_1_detector = new ilSimpleDetector($_v_EndEvent_1);
			$_v_EndEvent_1_detector->setName('$_v_EndEvent_1_detector');
			$_v_EndEvent_1_detector->setSourceNode($_v_ExclusiveGateway_1);
			$_v_EndEvent_1->addDetector($_v_EndEvent_1_detector);
			$_v_ExclusiveGateway_1_emitter = new ilActivationEmitter($_v_ExclusiveGateway_1);
			$_v_ExclusiveGateway_1_emitter->setName('$_v_ExclusiveGateway_1_emitter');
			$_v_ExclusiveGateway_1_emitter->setTargetDetector($_v_EndEvent_1_detector);
			$_v_ExclusiveGateway_1->addEmitter($_v_ExclusiveGateway_1_emitter);
		
			}
		}
		
?>