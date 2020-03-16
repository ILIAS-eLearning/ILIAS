<?php
require_once './Services/WorkflowEngine/classes/workflows/class.ilBaseWorkflow.php';
require_once './Services/WorkflowEngine/classes/nodes/class.ilBasicNode.php';
require_once './Services/WorkflowEngine/classes/activities/class.ilEventRaisingActivity.php';
require_once './Services/WorkflowEngine/classes/emitters/class.ilActivationEmitter.php';
require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';

		class EndEvent_Message_Simple extends ilBaseWorkflow
		{
		
			public static $startEventRequired = false;
		
			public function __construct()
			{
		
			$_v_StartEvent_1 = new ilBasicNode($this);
			$this->addNode($_v_StartEvent_1);
			$_v_StartEvent_1->setName('$_v_StartEvent_1');
		
			$this->setStartNode($_v_StartEvent_1);
			
			$_v_EndEvent_2 = new ilBasicNode($this);
			$this->addNode($_v_EndEvent_2);
			$_v_EndEvent_2->setName('$_v_EndEvent_2');
		
				$_v_EndEvent_2_throwEventActivity = new ilEventRaisingActivity($_v_EndEvent_2);
				$_v_EndEvent_2_throwEventActivity->setName('$_v_EndEvent_2_throwEventActivity');
				$_v_EndEvent_2_throwEventActivity->setEventType("Course");
				$_v_EndEvent_2_throwEventActivity->setEventName("UserWasAssigned");
				$_v_EndEvent_2->addActivity($_v_EndEvent_2_throwEventActivity);
			
			$_v_EndEvent_2_detector = new ilSimpleDetector($_v_EndEvent_2);
			$_v_EndEvent_2_detector->setName('$_v_EndEvent_2_detector');
			$_v_EndEvent_2_detector->setSourceNode($_v_StartEvent_1);
			$_v_EndEvent_2->addDetector($_v_EndEvent_2_detector);
			$_v_StartEvent_1_emitter = new ilActivationEmitter($_v_StartEvent_1);
			$_v_StartEvent_1_emitter->setName('$_v_StartEvent_1_emitter');
			$_v_StartEvent_1_emitter->setTargetDetector($_v_EndEvent_2_detector);
			$_v_StartEvent_1->addEmitter($_v_StartEvent_1_emitter);
		
			}

			
			public static function getMessageDefinition($id)
			{
				$definitions = array( 'Message_1' =>  array(
		'name' => 'Message',
		'content' => '')
				);
				return $definitions[$id];
			}
			
			
		}
		
?>