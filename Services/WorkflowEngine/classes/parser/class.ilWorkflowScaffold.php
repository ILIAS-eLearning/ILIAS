<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilWorkflowScaffold
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowScaffold
{
    #region Requires / File inclusion

    /** @var array $requires */
    public array $requires = [];

    public string $constructor_method_content;

    /** @var array $bpmn2_array */
    public array $bpmn2_array;

    /** @var array $auxilliary_methods */
    public array $auxilliary_methods;

    public string $workflow_name;

    public function registerRequire(string $require) : void
    {
        if (!in_array($require, $this->requires, true)) {
            $this->requires[] = $require;
        }
    }

    public function getRequires() : string
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
    public array $start_event_refs = [];

    public function registerStartEventRef(string $start_event_ref) : void
    {
        $this->start_event_refs[] = ['type' => 'message', 'ref' => $start_event_ref];
    }

    public function registerStartSignalRef(string $start_event_ref) : void
    {
        $this->start_event_refs[] = ['type' => 'signal', 'ref' => $start_event_ref];
    }

    public function registerStartTimerRef(string $start_event_ref) : void
    {
        $this->start_event_refs[] = ['type' => 'timeDate', 'ref' => $start_event_ref];
    }

    public function getStartEventInfo() : string
    {
        $event_definitions = [];
        foreach ($this->start_event_refs as $start_event_ref) {
            $event_definition = [];
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
    public function setWorkflowName($workflow_name) : void
    {
        $this->workflow_name = $workflow_name;
    }

    public function addAuxilliaryMethod(string $auxilliary_method) : void
    {
        $this->auxilliary_methods[] = $auxilliary_method;
    }

    public function __construct(array $bpmn2_array)
    {
        $this->registerRequire('./Services/WorkflowEngine/classes/workflows/class.ilBaseWorkflow.php');
        $this->bpmn2_array = $bpmn2_array;
        $this->auxilliary_methods = [];
    }

    public function getConstructorMethodContent() : ?string
    {
        return $this->constructor_method_content;
    }

    public function setConstructorMethodContent(string $constructor_method_content) : void
    {
        $this->constructor_method_content = $constructor_method_content;
    }

    public function getPHP() : string
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
     * @return array
     */
    public function getTimeDateEventDefinition(string $start_event_ref) : array
    {
        $content = '';
        foreach ((array) $this->bpmn2_array['children'] as $elements) {
            foreach ((array) $elements['children'] as $element) {
                if (
                    isset($element['name'], $element['children'][0]['name']) &&
                    $element['name'] === 'startEvent' &&
                    $element['children'][0]['name'] === 'timerEventDefinition'
                ) {
                    $timer_element = $element['children'][0];
                    $content = $timer_element['children'][0]['content'];
                }
            }
        }

        $start = date('U', strtotime($content));
        $end = 0;

        return [
            'type' => 'time_passed',
            'content' => 'time_passed',
            'subject_type' => 'none',
            'subject_id' => 0,
            'context_type' => 'none',
            'context_id' => 0,
            'listening_start' => $start,
            'listening_end' => $end
        ];
    }
}
