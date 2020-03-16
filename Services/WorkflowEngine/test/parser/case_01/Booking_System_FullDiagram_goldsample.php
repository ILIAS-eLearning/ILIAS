<?php
require_once './Services/WorkflowEngine/classes/workflows/class.ilBaseWorkflow.php';
require_once './Services/WorkflowEngine/classes/nodes/class.ilBasicNode.php';
require_once './Services/WorkflowEngine/classes/detectors/class.ilEventDetector.php';
require_once './Services/WorkflowEngine/classes/nodes/class.ilCaseNode.php';
require_once './Services/WorkflowEngine/classes/activities/class.ilScriptActivity.php';
require_once './Services/WorkflowEngine/classes/emitters/class.ilActivationEmitter.php';
require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';

		class Booking_System_FullDiagram extends ilBaseWorkflow
		{
		
			public static $startEventRequired = true;
			
			public static function getStartEventInfo()
			{
				$events[] = array(
					'type' 			=> '', 
					'content' 		=> '', 
					'subject_type' 	=> '', 
					'subject_id'	=> '', 
					'context_type'	=> '', 
					'context_id'	=> '', 
				);
				
				$events[] = array(
					'type' 			=> '', 
					'content' 		=> '', 
					'subject_type' 	=> '', 
					'subject_id'	=> '', 
					'context_type'	=> '', 
					'context_id'	=> '', 
				);
				
				$events[] = array(
					'type' 			=> '', 
					'content' 		=> '', 
					'subject_type' 	=> '', 
					'subject_id'	=> '', 
					'context_type'	=> '', 
					'context_id'	=> '', 
				);
				
				$events[] = array(
					'type' 			=> '', 
					'content' 		=> '', 
					'subject_type' 	=> '', 
					'subject_id'	=> '', 
					'context_type'	=> '', 
					'context_id'	=> '', 
				);
				
				$events[] = array(
					'type' 			=> '', 
					'content' 		=> '', 
					'subject_type' 	=> '', 
					'subject_id'	=> '', 
					'context_type'	=> '', 
					'context_id'	=> '', 
				);
				
				$events[] = array(
					'type' 			=> '', 
					'content' 		=> '', 
					'subject_type' 	=> '', 
					'subject_id'	=> '', 
					'context_type'	=> '', 
					'context_id'	=> '', 
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
			$_v_StartEvent_2_detector->setEvent(			"", 			"");
			$_v_StartEvent_2_detector->setEventSubject(	"", 	"");
			$_v_StartEvent_2_detector->setEventContext(	"", 	"");
			
			$this->defineInstanceVar("DataObject_1","dataObject", false, "", "mixed", "undefined" );

			$this->defineInstanceVar("DataObject_2","dataObject", false, "", "mixed", "undefined" );

			$_v_ServiceTask_1 = new ilBasicNode($this);
			$this->addNode($_v_ServiceTask_1);
			$_v_ServiceTask_1->setName('$_v_ServiceTask_1');

			$_v_ExclusiveGateway_1 = new ilCaseNode($this);
			$_v_ExclusiveGateway_1->setName('$_v_ExclusiveGateway_1');
			$_v_ExclusiveGateway_1->setIsExclusiveJoin(true);
			$this->addNode($_v_ExclusiveGateway_1);

			$_v_SendTask_1 = new ilBasicNode($this);
			$this->addNode($_v_SendTask_1);
			$_v_SendTask_1->setName('$_v_SendTask_1');

			$_v_ServiceTask_2 = new ilBasicNode($this);
			$this->addNode($_v_ServiceTask_2);
			$_v_ServiceTask_2->setName('$_v_ServiceTask_2');

			$this->defineInstanceVar("DataObject_3","dataObject", false, "", "mixed", "undefined" );

			$_v_SendTask_2 = new ilBasicNode($this);
			$this->addNode($_v_SendTask_2);
			$_v_SendTask_2->setName('$_v_SendTask_2');

			$_v_ExclusiveGateway_3 = new ilCaseNode($this);
			$_v_ExclusiveGateway_3->setName('$_v_ExclusiveGateway_3');
			$_v_ExclusiveGateway_3->setIsExclusiveJoin(true);
			$this->addNode($_v_ExclusiveGateway_3);

			$_v_EndEvent_3 = new ilBasicNode($this);
			$this->addNode($_v_EndEvent_3);
			$_v_EndEvent_3->setName('$_v_EndEvent_3');

			$_v_StartEvent_3 = new ilBasicNode($this);
			$this->addNode($_v_StartEvent_3);
			$_v_StartEvent_3->setName('$_v_StartEvent_3');

			$_v_StartEvent_3_detector = new ilEventDetector($_v_StartEvent_3);
			$_v_StartEvent_3_detector->setName('$_v_StartEvent_3_detector');
			$_v_StartEvent_3_detector->setEvent(			"", 			"");
			$_v_StartEvent_3_detector->setEventSubject(	"", 	"");
			$_v_StartEvent_3_detector->setEventContext(	"", 	"");

			$_v_ScriptTask_1 = new ilBasicNode($this);
			$this->addNode($_v_ScriptTask_1);
			$_v_ScriptTask_1->setName('$_v_ScriptTask_1');

			$_v_ScriptTask_1_scriptActivity = new ilScriptActivity($_v_ScriptTask_1);
			$_v_ScriptTask_1_scriptActivity->setName('$_v_ScriptTask_1');
			$_v_ScriptTask_1_scriptActivity->setMethod('_v_ScriptTask_1_script');
			$_v_ScriptTask_1->addActivity($_v_ScriptTask_1_scriptActivity);

			$_v_SendTask_3 = new ilBasicNode($this);
			$this->addNode($_v_SendTask_3);
			$_v_SendTask_3->setName('$_v_SendTask_3');

			$_v_EndEvent_4 = new ilBasicNode($this);
			$this->addNode($_v_EndEvent_4);
			$_v_EndEvent_4->setName('$_v_EndEvent_4');

			$_v_StartEvent_4 = new ilBasicNode($this);
			$this->addNode($_v_StartEvent_4);
			$_v_StartEvent_4->setName('$_v_StartEvent_4');

			$_v_StartEvent_4_detector = new ilEventDetector($_v_StartEvent_4);
			$_v_StartEvent_4_detector->setName('$_v_StartEvent_4_detector');
			$_v_StartEvent_4_detector->setEvent(			"", 			"");
			$_v_StartEvent_4_detector->setEventSubject(	"", 	"");
			$_v_StartEvent_4_detector->setEventContext(	"", 	"");

			$_v_ScriptTask_2 = new ilBasicNode($this);
			$this->addNode($_v_ScriptTask_2);
			$_v_ScriptTask_2->setName('$_v_ScriptTask_2');

			$_v_ScriptTask_2_scriptActivity = new ilScriptActivity($_v_ScriptTask_2);
			$_v_ScriptTask_2_scriptActivity->setName('$_v_ScriptTask_2');
			$_v_ScriptTask_2_scriptActivity->setMethod('_v_ScriptTask_2_script');
			$_v_ScriptTask_2->addActivity($_v_ScriptTask_2_scriptActivity);

			$_v_SendTask_4 = new ilBasicNode($this);
			$this->addNode($_v_SendTask_4);
			$_v_SendTask_4->setName('$_v_SendTask_4');

			$_v_IntermediateThrowEvent_1 = new ilBasicNode($this);
			$this->addNode($_v_IntermediateThrowEvent_1);
			$_v_IntermediateThrowEvent_1->setName('$_v_IntermediateThrowEvent_1');

			$_v_EndEvent_6 = new ilBasicNode($this);
			$this->addNode($_v_EndEvent_6);
			$_v_EndEvent_6->setName('$_v_EndEvent_6');

			$_v_StartEvent_5 = new ilBasicNode($this);
			$this->addNode($_v_StartEvent_5);
			$_v_StartEvent_5->setName('$_v_StartEvent_5');

			$_v_StartEvent_5_detector = new ilEventDetector($_v_StartEvent_5);
			$_v_StartEvent_5_detector->setName('$_v_StartEvent_5_detector');
			$_v_StartEvent_5_detector->setEvent(			"", 			"");
			$_v_StartEvent_5_detector->setEventSubject(	"", 	"");
			$_v_StartEvent_5_detector->setEventContext(	"", 	"");

			$_v_ScriptTask_3 = new ilBasicNode($this);
			$this->addNode($_v_ScriptTask_3);
			$_v_ScriptTask_3->setName('$_v_ScriptTask_3');

			$_v_ScriptTask_3_scriptActivity = new ilScriptActivity($_v_ScriptTask_3);
			$_v_ScriptTask_3_scriptActivity->setName('$_v_ScriptTask_3');
			$_v_ScriptTask_3_scriptActivity->setMethod('_v_ScriptTask_3_script');
			$_v_ScriptTask_3->addActivity($_v_ScriptTask_3_scriptActivity);

			$_v_ExclusiveGateway_4 = new ilCaseNode($this);
			$_v_ExclusiveGateway_4->setName('$_v_ExclusiveGateway_4');
			$_v_ExclusiveGateway_4->setIsExclusiveJoin(true);
			$this->addNode($_v_ExclusiveGateway_4);

			$_v_ScriptTask_5 = new ilBasicNode($this);
			$this->addNode($_v_ScriptTask_5);
			$_v_ScriptTask_5->setName('$_v_ScriptTask_5');

			$_v_ScriptTask_5_scriptActivity = new ilScriptActivity($_v_ScriptTask_5);
			$_v_ScriptTask_5_scriptActivity->setName('$_v_ScriptTask_5');
			$_v_ScriptTask_5_scriptActivity->setMethod('_v_ScriptTask_5_script');
			$_v_ScriptTask_5->addActivity($_v_ScriptTask_5_scriptActivity);

			$_v_SendTask_5 = new ilBasicNode($this);
			$this->addNode($_v_SendTask_5);
			$_v_SendTask_5->setName('$_v_SendTask_5');

			$_v_ExclusiveGateway_5 = new ilCaseNode($this);
			$_v_ExclusiveGateway_5->setName('$_v_ExclusiveGateway_5');
			$_v_ExclusiveGateway_5->setIsExclusiveJoin(true);
			$this->addNode($_v_ExclusiveGateway_5);

			$_v_EndEvent_7 = new ilBasicNode($this);
			$this->addNode($_v_EndEvent_7);
			$_v_EndEvent_7->setName('$_v_EndEvent_7');

			$_v_StartEvent_6 = new ilBasicNode($this);
			$this->addNode($_v_StartEvent_6);
			$_v_StartEvent_6->setName('$_v_StartEvent_6');

			$_v_StartEvent_6_detector = new ilEventDetector($_v_StartEvent_6);
			$_v_StartEvent_6_detector->setName('$_v_StartEvent_6_detector');
			$_v_StartEvent_6_detector->setEvent(			"", 			"");
			$_v_StartEvent_6_detector->setEventSubject(	"", 	"");
			$_v_StartEvent_6_detector->setEventContext(	"", 	"");

			$this->defineInstanceVar("DataObject_4","dataObject", false, "", "mixed", "undefined" );
		
			$_v_IntermediateCatchEvent_1 = new ilBasicNode($this);
			$this->addNode($_v_IntermediateCatchEvent_1);
			$_v_IntermediateCatchEvent_1->setName('$_v_IntermediateCatchEvent_1');
		
			$_v_IntermediateCatchEvent_1_detector = new ilEventDetector($_v_IntermediateCatchEvent_1);
			$_v_IntermediateCatchEvent_1_detector->setName('$_v_IntermediateCatchEvent_1_detector');
			$_v_IntermediateCatchEvent_1_detector->setEvent(			"time_passed", 			"time_passed");
			$_v_IntermediateCatchEvent_1_detector->setEventSubject(	"none", 	"0");
			$_v_IntermediateCatchEvent_1_detector->setEventContext(	"none", 	"0");
			$_v_IntermediateCatchEvent_1_detector->setListeningTimeframe(0, 0);
			$_v_IntermediateCatchEvent_1->addDetector($_v_IntermediateCatchEvent_1_detector);
			
			$_v_ExclusiveGateway_6 = new ilCaseNode($this);
			$_v_ExclusiveGateway_6->setName('$_v_ExclusiveGateway_6');
			$_v_ExclusiveGateway_6->setIsExclusiveJoin(true);
			$this->addNode($_v_ExclusiveGateway_6);
		
			$_v_SendTask_6 = new ilBasicNode($this);
			$this->addNode($_v_SendTask_6);
			$_v_SendTask_6->setName('$_v_SendTask_6');
		
			$_v_ExclusiveGateway_7 = new ilCaseNode($this);
			$_v_ExclusiveGateway_7->setName('$_v_ExclusiveGateway_7');
			$_v_ExclusiveGateway_7->setIsExclusiveJoin(true);
			$this->addNode($_v_ExclusiveGateway_7);
		
			$_v_EndEvent_9 = new ilBasicNode($this);
			$this->addNode($_v_EndEvent_9);
			$_v_EndEvent_9->setName('$_v_EndEvent_9');
		
			$_v_StartEvent_7 = new ilBasicNode($this);
			$this->addNode($_v_StartEvent_7);
			$_v_StartEvent_7->setName('$_v_StartEvent_7');
		
			$_v_StartEvent_7_detector = new ilEventDetector($_v_StartEvent_7);
			$_v_StartEvent_7_detector->setName('$_v_StartEvent_7_detector');
			$_v_StartEvent_7_detector->setEvent(			"", 			"");
			$_v_StartEvent_7_detector->setEventSubject(	"", 	"");
			$_v_StartEvent_7_detector->setEventContext(	"", 	"");
			
			$_v_SendTask_7 = new ilBasicNode($this);
			$this->addNode($_v_SendTask_7);
			$_v_SendTask_7->setName('$_v_SendTask_7');
		
			$_v_ScriptTask_7 = new ilBasicNode($this);
			$this->addNode($_v_ScriptTask_7);
			$_v_ScriptTask_7->setName('$_v_ScriptTask_7');
		
			$_v_ScriptTask_7_scriptActivity = new ilScriptActivity($_v_ScriptTask_7);
			$_v_ScriptTask_7_scriptActivity->setName('$_v_ScriptTask_7');
			$_v_ScriptTask_7_scriptActivity->setMethod('_v_ScriptTask_7_script');
			$_v_ScriptTask_7->addActivity($_v_ScriptTask_7_scriptActivity);
			
			$_v_ScriptTask_8 = new ilBasicNode($this);
			$this->addNode($_v_ScriptTask_8);
			$_v_ScriptTask_8->setName('$_v_ScriptTask_8');
		
			$_v_ScriptTask_8_scriptActivity = new ilScriptActivity($_v_ScriptTask_8);
			$_v_ScriptTask_8_scriptActivity->setName('$_v_ScriptTask_8');
			$_v_ScriptTask_8_scriptActivity->setMethod('_v_ScriptTask_8_script');
			$_v_ScriptTask_8->addActivity($_v_ScriptTask_8_scriptActivity);
			
			$_v_ParallelGateway_1 = new ilBasicNode($this);
			$_v_ParallelGateway_1->setName('$_v_ParallelGateway_1');
			$this->addNode($_v_ParallelGateway_1);
		
			$_v_SendTask_8 = new ilBasicNode($this);
			$this->addNode($_v_SendTask_8);
			$_v_SendTask_8->setName('$_v_SendTask_8');
		
			$_v_InclusiveGateway_1 = new ilCaseNode($this);
			$_v_InclusiveGateway_1->setName('$_v_InclusiveGateway_1');
			$this->addNode($_v_InclusiveGateway_1);
		
			$_v_EndEvent_10 = new ilBasicNode($this);
			$this->addNode($_v_EndEvent_10);
			$_v_EndEvent_10->setName('$_v_EndEvent_10');
		
			$_v_ServiceTask_1_detector = new ilSimpleDetector($_v_ServiceTask_1);
			$_v_ServiceTask_1_detector->setName('$_v_ServiceTask_1_detector');
			$_v_ServiceTask_1_detector->setSourceNode($_v_StartEvent_2);
			$_v_ServiceTask_1->addDetector($_v_ServiceTask_1_detector);
			$_v_StartEvent_2_emitter = new ilActivationEmitter($_v_StartEvent_2);
			$_v_StartEvent_2_emitter->setName('$_v_StartEvent_2_emitter');
			$_v_StartEvent_2_emitter->setTargetDetector($_v_ServiceTask_1_detector);
			$_v_StartEvent_2->addEmitter($_v_StartEvent_2_emitter);
		
			$_v_ExclusiveGateway_1_detector = new ilSimpleDetector($_v_ExclusiveGateway_1);
			$_v_ExclusiveGateway_1_detector->setName('$_v_ExclusiveGateway_1_detector');
			$_v_ExclusiveGateway_1_detector->setSourceNode($_v_ServiceTask_1);
			$_v_ExclusiveGateway_1->addDetector($_v_ExclusiveGateway_1_detector);
			$_v_ServiceTask_1_emitter = new ilActivationEmitter($_v_ServiceTask_1);
			$_v_ServiceTask_1_emitter->setName('$_v_ServiceTask_1_emitter');
			$_v_ServiceTask_1_emitter->setTargetDetector($_v_ExclusiveGateway_1_detector);
			$_v_ServiceTask_1->addEmitter($_v_ServiceTask_1_emitter);
		
			$_v_SendTask_1_detector = new ilSimpleDetector($_v_SendTask_1);
			$_v_SendTask_1_detector->setName('$_v_SendTask_1_detector');
			$_v_SendTask_1_detector->setSourceNode($_v_ExclusiveGateway_1);
			$_v_SendTask_1->addDetector($_v_SendTask_1_detector);
			$_v_ExclusiveGateway_1_emitter = new ilActivationEmitter($_v_ExclusiveGateway_1);
			$_v_ExclusiveGateway_1_emitter->setName('$_v_ExclusiveGateway_1_emitter');
			$_v_ExclusiveGateway_1_emitter->setTargetDetector($_v_SendTask_1_detector);
			$_v_ExclusiveGateway_1->addEmitter($_v_ExclusiveGateway_1_emitter);
		
			$_v_ServiceTask_2_detector = new ilSimpleDetector($_v_ServiceTask_2);
			$_v_ServiceTask_2_detector->setName('$_v_ServiceTask_2_detector');
			$_v_ServiceTask_2_detector->setSourceNode($_v_ExclusiveGateway_1);
			$_v_ServiceTask_2->addDetector($_v_ServiceTask_2_detector);
			$_v_ExclusiveGateway_1_emitter = new ilActivationEmitter($_v_ExclusiveGateway_1);
			$_v_ExclusiveGateway_1_emitter->setName('$_v_ExclusiveGateway_1_emitter');
			$_v_ExclusiveGateway_1_emitter->setTargetDetector($_v_ServiceTask_2_detector);
			$_v_ExclusiveGateway_1->addEmitter($_v_ExclusiveGateway_1_emitter);
		
			$_v_SendTask_2_detector = new ilSimpleDetector($_v_SendTask_2);
			$_v_SendTask_2_detector->setName('$_v_SendTask_2_detector');
			$_v_SendTask_2_detector->setSourceNode($_v_ServiceTask_2);
			$_v_SendTask_2->addDetector($_v_SendTask_2_detector);
			$_v_ServiceTask_2_emitter = new ilActivationEmitter($_v_ServiceTask_2);
			$_v_ServiceTask_2_emitter->setName('$_v_ServiceTask_2_emitter');
			$_v_ServiceTask_2_emitter->setTargetDetector($_v_SendTask_2_detector);
			$_v_ServiceTask_2->addEmitter($_v_ServiceTask_2_emitter);
		
			$_v_ExclusiveGateway_3_detector = new ilSimpleDetector($_v_ExclusiveGateway_3);
			$_v_ExclusiveGateway_3_detector->setName('$_v_ExclusiveGateway_3_detector');
			$_v_ExclusiveGateway_3_detector->setSourceNode($_v_SendTask_2);
			$_v_ExclusiveGateway_3->addDetector($_v_ExclusiveGateway_3_detector);
			$_v_SendTask_2_emitter = new ilActivationEmitter($_v_SendTask_2);
			$_v_SendTask_2_emitter->setName('$_v_SendTask_2_emitter');
			$_v_SendTask_2_emitter->setTargetDetector($_v_ExclusiveGateway_3_detector);
			$_v_SendTask_2->addEmitter($_v_SendTask_2_emitter);
		
			$_v_ExclusiveGateway_3_detector = new ilSimpleDetector($_v_ExclusiveGateway_3);
			$_v_ExclusiveGateway_3_detector->setName('$_v_ExclusiveGateway_3_detector');
			$_v_ExclusiveGateway_3_detector->setSourceNode($_v_SendTask_1);
			$_v_ExclusiveGateway_3->addDetector($_v_ExclusiveGateway_3_detector);
			$_v_SendTask_1_emitter = new ilActivationEmitter($_v_SendTask_1);
			$_v_SendTask_1_emitter->setName('$_v_SendTask_1_emitter');
			$_v_SendTask_1_emitter->setTargetDetector($_v_ExclusiveGateway_3_detector);
			$_v_SendTask_1->addEmitter($_v_SendTask_1_emitter);
		
			$_v_ScriptTask_1_detector = new ilSimpleDetector($_v_ScriptTask_1);
			$_v_ScriptTask_1_detector->setName('$_v_ScriptTask_1_detector');
			$_v_ScriptTask_1_detector->setSourceNode($_v_StartEvent_3);
			$_v_ScriptTask_1->addDetector($_v_ScriptTask_1_detector);
			$_v_StartEvent_3_emitter = new ilActivationEmitter($_v_StartEvent_3);
			$_v_StartEvent_3_emitter->setName('$_v_StartEvent_3_emitter');
			$_v_StartEvent_3_emitter->setTargetDetector($_v_ScriptTask_1_detector);
			$_v_StartEvent_3->addEmitter($_v_StartEvent_3_emitter);
		
			$_v_SendTask_3_detector = new ilSimpleDetector($_v_SendTask_3);
			$_v_SendTask_3_detector->setName('$_v_SendTask_3_detector');
			$_v_SendTask_3_detector->setSourceNode($_v_ScriptTask_1);
			$_v_SendTask_3->addDetector($_v_SendTask_3_detector);
			$_v_ScriptTask_1_emitter = new ilActivationEmitter($_v_ScriptTask_1);
			$_v_ScriptTask_1_emitter->setName('$_v_ScriptTask_1_emitter');
			$_v_ScriptTask_1_emitter->setTargetDetector($_v_SendTask_3_detector);
			$_v_ScriptTask_1->addEmitter($_v_ScriptTask_1_emitter);
		
			$_v_EndEvent_4_detector = new ilSimpleDetector($_v_EndEvent_4);
			$_v_EndEvent_4_detector->setName('$_v_EndEvent_4_detector');
			$_v_EndEvent_4_detector->setSourceNode($_v_SendTask_3);
			$_v_EndEvent_4->addDetector($_v_EndEvent_4_detector);
			$_v_SendTask_3_emitter = new ilActivationEmitter($_v_SendTask_3);
			$_v_SendTask_3_emitter->setName('$_v_SendTask_3_emitter');
			$_v_SendTask_3_emitter->setTargetDetector($_v_EndEvent_4_detector);
			$_v_SendTask_3->addEmitter($_v_SendTask_3_emitter);
		
			$_v_ScriptTask_2_detector = new ilSimpleDetector($_v_ScriptTask_2);
			$_v_ScriptTask_2_detector->setName('$_v_ScriptTask_2_detector');
			$_v_ScriptTask_2_detector->setSourceNode($_v_StartEvent_4);
			$_v_ScriptTask_2->addDetector($_v_ScriptTask_2_detector);
			$_v_StartEvent_4_emitter = new ilActivationEmitter($_v_StartEvent_4);
			$_v_StartEvent_4_emitter->setName('$_v_StartEvent_4_emitter');
			$_v_StartEvent_4_emitter->setTargetDetector($_v_ScriptTask_2_detector);
			$_v_StartEvent_4->addEmitter($_v_StartEvent_4_emitter);
		
			$_v_SendTask_4_detector = new ilSimpleDetector($_v_SendTask_4);
			$_v_SendTask_4_detector->setName('$_v_SendTask_4_detector');
			$_v_SendTask_4_detector->setSourceNode($_v_ScriptTask_2);
			$_v_SendTask_4->addDetector($_v_SendTask_4_detector);
			$_v_ScriptTask_2_emitter = new ilActivationEmitter($_v_ScriptTask_2);
			$_v_ScriptTask_2_emitter->setName('$_v_ScriptTask_2_emitter');
			$_v_ScriptTask_2_emitter->setTargetDetector($_v_SendTask_4_detector);
			$_v_ScriptTask_2->addEmitter($_v_ScriptTask_2_emitter);
		
			$_v_IntermediateThrowEvent_1_detector = new ilSimpleDetector($_v_IntermediateThrowEvent_1);
			$_v_IntermediateThrowEvent_1_detector->setName('$_v_IntermediateThrowEvent_1_detector');
			$_v_IntermediateThrowEvent_1_detector->setSourceNode($_v_SendTask_4);
			$_v_IntermediateThrowEvent_1->addDetector($_v_IntermediateThrowEvent_1_detector);
			$_v_SendTask_4_emitter = new ilActivationEmitter($_v_SendTask_4);
			$_v_SendTask_4_emitter->setName('$_v_SendTask_4_emitter');
			$_v_SendTask_4_emitter->setTargetDetector($_v_IntermediateThrowEvent_1_detector);
			$_v_SendTask_4->addEmitter($_v_SendTask_4_emitter);
		
			$_v_EndEvent_6_detector = new ilSimpleDetector($_v_EndEvent_6);
			$_v_EndEvent_6_detector->setName('$_v_EndEvent_6_detector');
			$_v_EndEvent_6_detector->setSourceNode($_v_IntermediateThrowEvent_1);
			$_v_EndEvent_6->addDetector($_v_EndEvent_6_detector);
			$_v_IntermediateThrowEvent_1_emitter = new ilActivationEmitter($_v_IntermediateThrowEvent_1);
			$_v_IntermediateThrowEvent_1_emitter->setName('$_v_IntermediateThrowEvent_1_emitter');
			$_v_IntermediateThrowEvent_1_emitter->setTargetDetector($_v_EndEvent_6_detector);
			$_v_IntermediateThrowEvent_1->addEmitter($_v_IntermediateThrowEvent_1_emitter);
		
			$_v_ExclusiveGateway_4_detector = new ilSimpleDetector($_v_ExclusiveGateway_4);
			$_v_ExclusiveGateway_4_detector->setName('$_v_ExclusiveGateway_4_detector');
			$_v_ExclusiveGateway_4_detector->setSourceNode($_v_ScriptTask_3);
			$_v_ExclusiveGateway_4->addDetector($_v_ExclusiveGateway_4_detector);
			$_v_ScriptTask_3_emitter = new ilActivationEmitter($_v_ScriptTask_3);
			$_v_ScriptTask_3_emitter->setName('$_v_ScriptTask_3_emitter');
			$_v_ScriptTask_3_emitter->setTargetDetector($_v_ExclusiveGateway_4_detector);
			$_v_ScriptTask_3->addEmitter($_v_ScriptTask_3_emitter);
		
			$_v_ScriptTask_5_detector = new ilSimpleDetector($_v_ScriptTask_5);
			$_v_ScriptTask_5_detector->setName('$_v_ScriptTask_5_detector');
			$_v_ScriptTask_5_detector->setSourceNode($_v_ExclusiveGateway_4);
			$_v_ScriptTask_5->addDetector($_v_ScriptTask_5_detector);
			$_v_ExclusiveGateway_4_emitter = new ilActivationEmitter($_v_ExclusiveGateway_4);
			$_v_ExclusiveGateway_4_emitter->setName('$_v_ExclusiveGateway_4_emitter');
			$_v_ExclusiveGateway_4_emitter->setTargetDetector($_v_ScriptTask_5_detector);
			$_v_ExclusiveGateway_4->addEmitter($_v_ExclusiveGateway_4_emitter);
		
			$_v_SendTask_5_detector = new ilSimpleDetector($_v_SendTask_5);
			$_v_SendTask_5_detector->setName('$_v_SendTask_5_detector');
			$_v_SendTask_5_detector->setSourceNode($_v_ScriptTask_5);
			$_v_SendTask_5->addDetector($_v_SendTask_5_detector);
			$_v_ScriptTask_5_emitter = new ilActivationEmitter($_v_ScriptTask_5);
			$_v_ScriptTask_5_emitter->setName('$_v_ScriptTask_5_emitter');
			$_v_ScriptTask_5_emitter->setTargetDetector($_v_SendTask_5_detector);
			$_v_ScriptTask_5->addEmitter($_v_ScriptTask_5_emitter);
		
			$_v_ExclusiveGateway_5_detector = new ilSimpleDetector($_v_ExclusiveGateway_5);
			$_v_ExclusiveGateway_5_detector->setName('$_v_ExclusiveGateway_5_detector');
			$_v_ExclusiveGateway_5_detector->setSourceNode($_v_StartEvent_5);
			$_v_ExclusiveGateway_5->addDetector($_v_ExclusiveGateway_5_detector);
			$_v_StartEvent_5_emitter = new ilActivationEmitter($_v_StartEvent_5);
			$_v_StartEvent_5_emitter->setName('$_v_StartEvent_5_emitter');
			$_v_StartEvent_5_emitter->setTargetDetector($_v_ExclusiveGateway_5_detector);
			$_v_StartEvent_5->addEmitter($_v_StartEvent_5_emitter);
		
			$_v_ScriptTask_3_detector = new ilSimpleDetector($_v_ScriptTask_3);
			$_v_ScriptTask_3_detector->setName('$_v_ScriptTask_3_detector');
			$_v_ScriptTask_3_detector->setSourceNode($_v_ExclusiveGateway_5);
			$_v_ScriptTask_3->addDetector($_v_ScriptTask_3_detector);
			$_v_ExclusiveGateway_5_emitter = new ilActivationEmitter($_v_ExclusiveGateway_5);
			$_v_ExclusiveGateway_5_emitter->setName('$_v_ExclusiveGateway_5_emitter');
			$_v_ExclusiveGateway_5_emitter->setTargetDetector($_v_ScriptTask_3_detector);
			$_v_ExclusiveGateway_5->addEmitter($_v_ExclusiveGateway_5_emitter);
		
			$_v_ExclusiveGateway_5_detector = new ilSimpleDetector($_v_ExclusiveGateway_5);
			$_v_ExclusiveGateway_5_detector->setName('$_v_ExclusiveGateway_5_detector');
			$_v_ExclusiveGateway_5_detector->setSourceNode($_v_SendTask_5);
			$_v_ExclusiveGateway_5->addDetector($_v_ExclusiveGateway_5_detector);
			$_v_SendTask_5_emitter = new ilActivationEmitter($_v_SendTask_5);
			$_v_SendTask_5_emitter->setName('$_v_SendTask_5_emitter');
			$_v_SendTask_5_emitter->setTargetDetector($_v_ExclusiveGateway_5_detector);
			$_v_SendTask_5->addEmitter($_v_SendTask_5_emitter);
		
			$_v_EndEvent_7_detector = new ilSimpleDetector($_v_EndEvent_7);
			$_v_EndEvent_7_detector->setName('$_v_EndEvent_7_detector');
			$_v_EndEvent_7_detector->setSourceNode($_v_ExclusiveGateway_4);
			$_v_EndEvent_7->addDetector($_v_EndEvent_7_detector);
			$_v_ExclusiveGateway_4_emitter = new ilActivationEmitter($_v_ExclusiveGateway_4);
			$_v_ExclusiveGateway_4_emitter->setName('$_v_ExclusiveGateway_4_emitter');
			$_v_ExclusiveGateway_4_emitter->setTargetDetector($_v_EndEvent_7_detector);
			$_v_ExclusiveGateway_4->addEmitter($_v_ExclusiveGateway_4_emitter);
		
			$_v_IntermediateCatchEvent_1_detector = new ilSimpleDetector($_v_IntermediateCatchEvent_1);
			$_v_IntermediateCatchEvent_1_detector->setName('$_v_IntermediateCatchEvent_1_detector');
			$_v_IntermediateCatchEvent_1_detector->setSourceNode($_v_StartEvent_6);
			$_v_IntermediateCatchEvent_1->addDetector($_v_IntermediateCatchEvent_1_detector);
			$_v_StartEvent_6_emitter = new ilActivationEmitter($_v_StartEvent_6);
			$_v_StartEvent_6_emitter->setName('$_v_StartEvent_6_emitter');
			$_v_StartEvent_6_emitter->setTargetDetector($_v_IntermediateCatchEvent_1_detector);
			$_v_StartEvent_6->addEmitter($_v_StartEvent_6_emitter);
		
			$_v_ExclusiveGateway_6_detector = new ilSimpleDetector($_v_ExclusiveGateway_6);
			$_v_ExclusiveGateway_6_detector->setName('$_v_ExclusiveGateway_6_detector');
			$_v_ExclusiveGateway_6_detector->setSourceNode($_v_IntermediateCatchEvent_1);
			$_v_ExclusiveGateway_6->addDetector($_v_ExclusiveGateway_6_detector);
			$_v_IntermediateCatchEvent_1_emitter = new ilActivationEmitter($_v_IntermediateCatchEvent_1);
			$_v_IntermediateCatchEvent_1_emitter->setName('$_v_IntermediateCatchEvent_1_emitter');
			$_v_IntermediateCatchEvent_1_emitter->setTargetDetector($_v_ExclusiveGateway_6_detector);
			$_v_IntermediateCatchEvent_1->addEmitter($_v_IntermediateCatchEvent_1_emitter);
		
			$_v_SendTask_6_detector = new ilSimpleDetector($_v_SendTask_6);
			$_v_SendTask_6_detector->setName('$_v_SendTask_6_detector');
			$_v_SendTask_6_detector->setSourceNode($_v_ExclusiveGateway_6);
			$_v_SendTask_6->addDetector($_v_SendTask_6_detector);
			$_v_ExclusiveGateway_6_emitter = new ilActivationEmitter($_v_ExclusiveGateway_6);
			$_v_ExclusiveGateway_6_emitter->setName('$_v_ExclusiveGateway_6_emitter');
			$_v_ExclusiveGateway_6_emitter->setTargetDetector($_v_SendTask_6_detector);
			$_v_ExclusiveGateway_6->addEmitter($_v_ExclusiveGateway_6_emitter);
		
			$_v_ExclusiveGateway_7_detector = new ilSimpleDetector($_v_ExclusiveGateway_7);
			$_v_ExclusiveGateway_7_detector->setName('$_v_ExclusiveGateway_7_detector');
			$_v_ExclusiveGateway_7_detector->setSourceNode($_v_ExclusiveGateway_6);
			$_v_ExclusiveGateway_7->addDetector($_v_ExclusiveGateway_7_detector);
			$_v_ExclusiveGateway_6_emitter = new ilActivationEmitter($_v_ExclusiveGateway_6);
			$_v_ExclusiveGateway_6_emitter->setName('$_v_ExclusiveGateway_6_emitter');
			$_v_ExclusiveGateway_6_emitter->setTargetDetector($_v_ExclusiveGateway_7_detector);
			$_v_ExclusiveGateway_6->addEmitter($_v_ExclusiveGateway_6_emitter);
		
			$_v_ExclusiveGateway_7_detector = new ilSimpleDetector($_v_ExclusiveGateway_7);
			$_v_ExclusiveGateway_7_detector->setName('$_v_ExclusiveGateway_7_detector');
			$_v_ExclusiveGateway_7_detector->setSourceNode($_v_SendTask_6);
			$_v_ExclusiveGateway_7->addDetector($_v_ExclusiveGateway_7_detector);
			$_v_SendTask_6_emitter = new ilActivationEmitter($_v_SendTask_6);
			$_v_SendTask_6_emitter->setName('$_v_SendTask_6_emitter');
			$_v_SendTask_6_emitter->setTargetDetector($_v_ExclusiveGateway_7_detector);
			$_v_SendTask_6->addEmitter($_v_SendTask_6_emitter);
		
			$_v_EndEvent_9_detector = new ilSimpleDetector($_v_EndEvent_9);
			$_v_EndEvent_9_detector->setName('$_v_EndEvent_9_detector');
			$_v_EndEvent_9_detector->setSourceNode($_v_ExclusiveGateway_7);
			$_v_EndEvent_9->addDetector($_v_EndEvent_9_detector);
			$_v_ExclusiveGateway_7_emitter = new ilActivationEmitter($_v_ExclusiveGateway_7);
			$_v_ExclusiveGateway_7_emitter->setName('$_v_ExclusiveGateway_7_emitter');
			$_v_ExclusiveGateway_7_emitter->setTargetDetector($_v_EndEvent_9_detector);
			$_v_ExclusiveGateway_7->addEmitter($_v_ExclusiveGateway_7_emitter);
		
			$_v_EndEvent_3_detector = new ilSimpleDetector($_v_EndEvent_3);
			$_v_EndEvent_3_detector->setName('$_v_EndEvent_3_detector');
			$_v_EndEvent_3_detector->setSourceNode($_v_ExclusiveGateway_3);
			$_v_EndEvent_3->addDetector($_v_EndEvent_3_detector);
			$_v_ExclusiveGateway_3_emitter = new ilActivationEmitter($_v_ExclusiveGateway_3);
			$_v_ExclusiveGateway_3_emitter->setName('$_v_ExclusiveGateway_3_emitter');
			$_v_ExclusiveGateway_3_emitter->setTargetDetector($_v_EndEvent_3_detector);
			$_v_ExclusiveGateway_3->addEmitter($_v_ExclusiveGateway_3_emitter);
		
			$_v_ScriptTask_7_detector = new ilSimpleDetector($_v_ScriptTask_7);
			$_v_ScriptTask_7_detector->setName('$_v_ScriptTask_7_detector');
			$_v_ScriptTask_7_detector->setSourceNode($_v_StartEvent_7);
			$_v_ScriptTask_7->addDetector($_v_ScriptTask_7_detector);
			$_v_StartEvent_7_emitter = new ilActivationEmitter($_v_StartEvent_7);
			$_v_StartEvent_7_emitter->setName('$_v_StartEvent_7_emitter');
			$_v_StartEvent_7_emitter->setTargetDetector($_v_ScriptTask_7_detector);
			$_v_StartEvent_7->addEmitter($_v_StartEvent_7_emitter);
		
			$_v_ScriptTask_8_detector = new ilSimpleDetector($_v_ScriptTask_8);
			$_v_ScriptTask_8_detector->setName('$_v_ScriptTask_8_detector');
			$_v_ScriptTask_8_detector->setSourceNode($_v_ScriptTask_7);
			$_v_ScriptTask_8->addDetector($_v_ScriptTask_8_detector);
			$_v_ScriptTask_7_emitter = new ilActivationEmitter($_v_ScriptTask_7);
			$_v_ScriptTask_7_emitter->setName('$_v_ScriptTask_7_emitter');
			$_v_ScriptTask_7_emitter->setTargetDetector($_v_ScriptTask_8_detector);
			$_v_ScriptTask_7->addEmitter($_v_ScriptTask_7_emitter);
		
			$_v_ParallelGateway_1_detector = new ilSimpleDetector($_v_ParallelGateway_1);
			$_v_ParallelGateway_1_detector->setName('$_v_ParallelGateway_1_detector');
			$_v_ParallelGateway_1_detector->setSourceNode($_v_ScriptTask_8);
			$_v_ParallelGateway_1->addDetector($_v_ParallelGateway_1_detector);
			$_v_ScriptTask_8_emitter = new ilActivationEmitter($_v_ScriptTask_8);
			$_v_ScriptTask_8_emitter->setName('$_v_ScriptTask_8_emitter');
			$_v_ScriptTask_8_emitter->setTargetDetector($_v_ParallelGateway_1_detector);
			$_v_ScriptTask_8->addEmitter($_v_ScriptTask_8_emitter);
		
			$_v_SendTask_7_detector = new ilSimpleDetector($_v_SendTask_7);
			$_v_SendTask_7_detector->setName('$_v_SendTask_7_detector');
			$_v_SendTask_7_detector->setSourceNode($_v_ParallelGateway_1);
			$_v_SendTask_7->addDetector($_v_SendTask_7_detector);
			$_v_ParallelGateway_1_emitter = new ilActivationEmitter($_v_ParallelGateway_1);
			$_v_ParallelGateway_1_emitter->setName('$_v_ParallelGateway_1_emitter');
			$_v_ParallelGateway_1_emitter->setTargetDetector($_v_SendTask_7_detector);
			$_v_ParallelGateway_1->addEmitter($_v_ParallelGateway_1_emitter);
		
			$_v_SendTask_8_detector = new ilSimpleDetector($_v_SendTask_8);
			$_v_SendTask_8_detector->setName('$_v_SendTask_8_detector');
			$_v_SendTask_8_detector->setSourceNode($_v_ParallelGateway_1);
			$_v_SendTask_8->addDetector($_v_SendTask_8_detector);
			$_v_ParallelGateway_1_emitter = new ilActivationEmitter($_v_ParallelGateway_1);
			$_v_ParallelGateway_1_emitter->setName('$_v_ParallelGateway_1_emitter');
			$_v_ParallelGateway_1_emitter->setTargetDetector($_v_SendTask_8_detector);
			$_v_ParallelGateway_1->addEmitter($_v_ParallelGateway_1_emitter);
		
			$_v_InclusiveGateway_1_detector = new ilSimpleDetector($_v_InclusiveGateway_1);
			$_v_InclusiveGateway_1_detector->setName('$_v_InclusiveGateway_1_detector');
			$_v_InclusiveGateway_1_detector->setSourceNode($_v_SendTask_7);
			$_v_InclusiveGateway_1->addDetector($_v_InclusiveGateway_1_detector);
			$_v_SendTask_7_emitter = new ilActivationEmitter($_v_SendTask_7);
			$_v_SendTask_7_emitter->setName('$_v_SendTask_7_emitter');
			$_v_SendTask_7_emitter->setTargetDetector($_v_InclusiveGateway_1_detector);
			$_v_SendTask_7->addEmitter($_v_SendTask_7_emitter);
		
			$_v_InclusiveGateway_1_detector = new ilSimpleDetector($_v_InclusiveGateway_1);
			$_v_InclusiveGateway_1_detector->setName('$_v_InclusiveGateway_1_detector');
			$_v_InclusiveGateway_1_detector->setSourceNode($_v_SendTask_8);
			$_v_InclusiveGateway_1->addDetector($_v_InclusiveGateway_1_detector);
			$_v_SendTask_8_emitter = new ilActivationEmitter($_v_SendTask_8);
			$_v_SendTask_8_emitter->setName('$_v_SendTask_8_emitter');
			$_v_SendTask_8_emitter->setTargetDetector($_v_InclusiveGateway_1_detector);
			$_v_SendTask_8->addEmitter($_v_SendTask_8_emitter);
		
			$_v_EndEvent_10_detector = new ilSimpleDetector($_v_EndEvent_10);
			$_v_EndEvent_10_detector->setName('$_v_EndEvent_10_detector');
			$_v_EndEvent_10_detector->setSourceNode($_v_InclusiveGateway_1);
			$_v_EndEvent_10->addDetector($_v_EndEvent_10_detector);
			$_v_InclusiveGateway_1_emitter = new ilActivationEmitter($_v_InclusiveGateway_1);
			$_v_InclusiveGateway_1_emitter->setName('$_v_InclusiveGateway_1_emitter');
			$_v_InclusiveGateway_1_emitter->setTargetDetector($_v_EndEvent_10_detector);
			$_v_InclusiveGateway_1->addEmitter($_v_InclusiveGateway_1_emitter);
		
			// association_missing
		
			}
			
			public function _v_ScriptTask_1_script($context)
			 {
			 
			 }
			
			
			public function _v_ScriptTask_2_script($context)
			 {
			 
			 }
			
			
			public function _v_ScriptTask_3_script($context)
			 {
			 
			 }
			
			
			public function _v_ScriptTask_5_script($context)
			 {
			 
			 }
			
			
			public function _v_ScriptTask_7_script($context)
			 {
			 
			 }
			
			
			public function _v_ScriptTask_8_script($context)
			 {
			 
			 }
			
		}
		
?>