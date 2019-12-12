<?php
require_once './Services/WorkflowEngine/classes/workflows/class.ilBaseWorkflow.php';
require_once './Services/WorkflowEngine/classes/nodes/class.ilBasicNode.php';
require_once './Services/WorkflowEngine/classes/detectors/class.ilDataDetector.php';
require_once './Services/WorkflowEngine/classes/emitters/class.ilDataEmitter.php';
require_once './Services/WorkflowEngine/classes/emitters/class.ilActivationEmitter.php';
require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';

		class DataObject_Wiring_Input_Object_Output extends ilBaseWorkflow
		{
		
			public static $startEventRequired = false;
		
			public function __construct()
			{
		
			$this->defineInstanceVar("DataInput_1", "ioval1", false, "", "mixed", "undefined" );
			$this->registerInputVar("DataInput_1", array());

			$this->defineInstanceVar("DataOutput_1","ioval1" );
			$this->registerOutputVar("DataOutput_1");

			$_v_Task_1 = new ilBasicNode($this);
			$this->addNode($_v_Task_1);
			$_v_Task_1->setName('$_v_Task_1');
		
			$_v_Task_1_inputDataDetector = new ilDataDetector($_v_Task_1);
			$_v_Task_1_inputDataDetector->setVarName("DataInput_1");
			$_v_Task_1_inputDataDetector->setName($_v_Task_1_inputDataDetector);
			$_v_Task_1->addDetector($_v_Task_1_inputDataDetector);
		
			$_v_Task_1_outputDataEmitter = new ilDataEmitter($_v_Task_1);
			$_v_Task_1_outputDataEmitter->setVarName("DataObjectReference_1");
			$_v_Task_1_outputDataEmitter->setName($_v_Task_1_outputDataEmitter);
			$_v_Task_1->addEmitter($_v_Task_1_outputDataEmitter);
		
			$this->defineInstanceVar("DataObject_1","dataObject", false, "", "mixed", "undefined" );
		
			$this->defineInstanceVar("DataObjectReference_1","ioval1", true, "DataObject_1" );
		
			$_v_Task_2 = new ilBasicNode($this);
			$this->addNode($_v_Task_2);
			$_v_Task_2->setName('$_v_Task_2');
		
			$_v_Task_2_inputDataDetector = new ilDataDetector($_v_Task_2);
			$_v_Task_2_inputDataDetector->setVarName("DataObjectReference_1");
			$_v_Task_2_inputDataDetector->setName($_v_Task_2_inputDataDetector);
			$_v_Task_2->addDetector($_v_Task_2_inputDataDetector);
		
			$_v_Task_2_outputDataEmitter = new ilDataEmitter($_v_Task_2);
			$_v_Task_2_outputDataEmitter->setVarName("DataOutput_1");
			$_v_Task_2_outputDataEmitter->setName($_v_Task_2_outputDataEmitter);
			$_v_Task_2->addEmitter($_v_Task_2_outputDataEmitter);
		
			$_v_EndEvent_1 = new ilBasicNode($this);
			$this->addNode($_v_EndEvent_1);
			$_v_EndEvent_1->setName('$_v_EndEvent_1');
		
			$_v_StartEvent_1 = new ilBasicNode($this);
			$this->addNode($_v_StartEvent_1);
			$_v_StartEvent_1->setName('$_v_StartEvent_1');
		
			$this->setStartNode($_v_StartEvent_1);
			
			$_v_Task_2_detector = new ilSimpleDetector($_v_Task_2);
			$_v_Task_2_detector->setName('$_v_Task_2_detector');
			$_v_Task_2_detector->setSourceNode($_v_Task_1);
			$_v_Task_2->addDetector($_v_Task_2_detector);
			$_v_Task_1_emitter = new ilActivationEmitter($_v_Task_1);
			$_v_Task_1_emitter->setName('$_v_Task_1_emitter');
			$_v_Task_1_emitter->setTargetDetector($_v_Task_2_detector);
			$_v_Task_1->addEmitter($_v_Task_1_emitter);
		
			$_v_EndEvent_1_detector = new ilSimpleDetector($_v_EndEvent_1);
			$_v_EndEvent_1_detector->setName('$_v_EndEvent_1_detector');
			$_v_EndEvent_1_detector->setSourceNode($_v_Task_2);
			$_v_EndEvent_1->addDetector($_v_EndEvent_1_detector);
			$_v_Task_2_emitter = new ilActivationEmitter($_v_Task_2);
			$_v_Task_2_emitter->setName('$_v_Task_2_emitter');
			$_v_Task_2_emitter->setTargetDetector($_v_EndEvent_1_detector);
			$_v_Task_2->addEmitter($_v_Task_2_emitter);
		
			$_v_Task_1_detector = new ilSimpleDetector($_v_Task_1);
			$_v_Task_1_detector->setName('$_v_Task_1_detector');
			$_v_Task_1_detector->setSourceNode($_v_StartEvent_1);
			$_v_Task_1->addDetector($_v_Task_1_detector);
			$_v_StartEvent_1_emitter = new ilActivationEmitter($_v_StartEvent_1);
			$_v_StartEvent_1_emitter->setName('$_v_StartEvent_1_emitter');
			$_v_StartEvent_1_emitter->setTargetDetector($_v_Task_1_detector);
			$_v_StartEvent_1->addEmitter($_v_StartEvent_1_emitter);
		
			}
		}
		
?>