<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilWorkflowScaffold
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowScaffold
{
    #region Requires / File inclusion

    /** @var array $requires */
    public $requires = array();

    /** @var string $constructor_method_content */
    public $constructor_method_content;

    /** @var array $bpmn2_array */
    public $bpmn2_array;

    /** @var array $auxilliary_methods */
    public $auxilliary_methods;

    /** @var string $workflow_name */
    public $workflow_name;

    /**
     * @param string $require
     */
    public function registerRequire($require)
    {
        if (!in_array($require, $this->requires)) {
            $this->requires[] = $require;
        }
    }

    /**
     * @return string
     */
    public function getRequires()
    {
        $requires = '';
        foreach ($this->requires as $required_file) {
            $requires .= "require_once '" . $required_file . "';\n";
        }
        return $requires;
    }

    #endregion

    #region StartEvent Message Registration and Handling

    /** @var array $start_event_refs */
    public $start_event_refs;

    /**
     * @param string $start_event_ref
     */
    public function registerStartEventRef($start_event_ref)
    {
        $this->start_event_refs[] = array('type' => 'message', 'ref' => $start_event_ref);
    }

    /**
     * @param string $start_event_ref
     */
    public function registerStartSignalRef($start_event_ref)
    {
        $this->start_event_refs[] = array('type' => 'signal', 'ref' => $start_event_ref);
    }

    /**
     * @param string $start_event_ref
     */
    public function registerStartTimerRef($start_event_ref)
    {
        $this->start_event_refs[] = array('type' => 'timeDate', 'ref' => $start_event_ref);
    }

    /**
     * @return string
     */
    public function getStartEventInfo()
    {
        $event_definitions = array();
        foreach ((array) $this->start_event_refs as $start_event_ref) {
            $event_definition = array();
            switch ($start_event_ref['type']) {
                case 'message':
                    $event_definition = ilBPMN2ParserUtils::extractILIASEventDefinitionFromProcess(
                        $start_event_ref['ref'],
                        'message',
                        $this->bpmn2_array
                    );
                    break;
                case 'signal':
                    $event_definition = ilBPMN2ParserUtils::extractILIASEventDefinitionFromProcess(
                        $start_event_ref['ref'],
                        'signal',
                        $this->bpmn2_array
                    );
                    break;
                case 'timeDate':
                    $event_definition = $this->getTimeDateEventDefinition($start_event_ref['ref']);
                    break;
            }
            $event_definitions[] = $event_definition;
        }

        if (count($event_definitions)) {
            $code = '
			public static $startEventRequired = true;
			' . "
			public static function getStartEventInfo()
			{";
            foreach ($event_definitions as $event_definition) {
                $code .= '
				$events[] = ' . "array(
					'type' 			=> '" . $event_definition['type'] . "', 
					'content' 		=> '" . $event_definition['content'] . "', 
					'subject_type' 	=> '" . $event_definition['subject_type'] . "', 
					'subject_id'	=> '" . $event_definition['subject_id'] . "', 
					'context_type'	=> '" . $event_definition['context_type'] . "', 
					'context_id'	=> '" . $event_definition['context_id'] . "', 
				);
				";
            }
            $code .= '
				return $events; 
			}
			';
            return $code;
        } else {
            return '
			public static $startEventRequired = false;
		';
        }
    }

    #endregion

    /**
     * @param mixed $workflow_name
     */
    public function setWorkflowName($workflow_name)
    {
        $this->workflow_name = $workflow_name;
    }

    /**
     * @param string $auxilliary_method
     */
    public function addAuxilliaryMethod($auxilliary_method)
    {
        $this->auxilliary_methods[] = $auxilliary_method;
    }

    public function __construct($bpmn2_array)
    {
        $this->registerRequire('./Services/WorkflowEngine/classes/workflows/class.ilBaseWorkflow.php');
        $this->bpmn2_array = $bpmn2_array;
        $this->auxilliary_methods = array();
    }

    /**
     * @return mixed
     */
    public function getConstructorMethodContent()
    {
        return $this->constructor_method_content;
    }

    /**
     * @param mixed $constructor_method_content
     */
    public function setConstructorMethodContent($constructor_method_content)
    {
        $this->constructor_method_content = $constructor_method_content;
    }

    /**
     * @return string
     */
    public function getPHP()
    {
        $pre_constructor_content = $this->getRequires();
        $pre_constructor_content .= "
		class " . $this->workflow_name . " extends ilBaseWorkflow
		{
		" . $this->getStartEventInfo() . "
			public function __construct()
			{
		";

        $post_constructor_content = "
			}";
        foreach ($this->auxilliary_methods as $auxilliary_method) {
            $post_constructor_content .= "

			" . $auxilliary_method . "
			";
        }
        $post_constructor_content .= "
		}
		";

        return $pre_constructor_content . $this->constructor_method_content . $post_constructor_content;
    }

    /**
     * @param string $start_event_ref
     *
     * @return array
     */
    public function getTimeDateEventDefinition($start_event_ref)
    {
        $content = '';
        foreach ((array) $this->bpmn2_array['children'] as $elements) {
            foreach ((array) $elements['children'] as $element) {
                if ($element['name'] == 'startEvent' && @$element['children'][0]['name'] == 'timerEventDefinition') {
                    $timer_element = $element['children'][0];
                    $content = $timer_element['children'][0]['content'];
                }
            }
        }

        $start = date('U', strtotime($content));
        $end = 0;

        return array(
            'type' => 'time_passed',
            'content' => 'time_passed',
            'subject_type' => 'none',
            'subject_id' => 0,
            'context_type' => 'none',
            'context_id' => 0,
            'listening_start' => $start,
            'listening_end' => $end
        );
    }
}
