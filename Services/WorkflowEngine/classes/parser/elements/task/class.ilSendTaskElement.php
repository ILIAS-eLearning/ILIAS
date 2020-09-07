<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSendTaskElement
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilSendTaskElement extends ilBaseElement
{
    /** @var string $element_varname */
    public $element_varname;

    /**
     * @param                     $element
     * @param \ilWorkflowScaffold $class_object
     *
     * @return string
     */
    public function getPHP($element, ilWorkflowScaffold $class_object)
    {
        $code = "";
        $element_id = ilBPMN2ParserUtils::xsIDToPHPVarname($element['attributes']['id']);
        $this->element_varname = '$_v_' . $element['attributes']['id'];
        $event_definition = null;
        if (count($element['children'])) {
            foreach ($element['children'] as $child) {
                if ($child['name'] == 'messageEventDefinition') {
                    $event_definition = ilBPMN2ParserUtils::extractILIASEventDefinitionFromProcess($child['attributes']['messageRef'], 'message', $this->bpmn2_array);
                }
                if ($child['name'] == 'signalEventDefinition') {
                    $event_definition = ilBPMN2ParserUtils::extractILIASEventDefinitionFromProcess($child['attributes']['signalRef'], 'signal', $this->bpmn2_array);
                }
            }
        }

        $message_element = false;
        if (isset($element['attributes']['ilias:message'])) {
            $message_element = $element['attributes']['ilias:message'];
        }

        $class_object->registerRequire('./Services/WorkflowEngine/classes/nodes/class.ilBasicNode.php');
        $code .= '
			' . $this->element_varname . ' = new ilBasicNode($this);
			$this->addNode(' . $this->element_varname . ');
			' . $this->element_varname . '->setName(\'' . $this->element_varname . '\');
		';

        if (isset($event_definition['type']) && isset($event_definition['content'])) {
            $class_object->registerRequire('./Services/WorkflowEngine/classes/activities/class.ilEventRaisingActivity.php');
            $code .= '
				' . $this->element_varname . '_sendTaskActivity = new ilEventRaisingActivity(' . $this->element_varname . ');
				' . $this->element_varname . '_sendTaskActivity->setName(\'' . $this->element_varname . '_sendTaskActivity\');
				' . $this->element_varname . '_sendTaskActivity->setEventType("' . $event_definition['type'] . '");
				' . $this->element_varname . '_sendTaskActivity->setEventName("' . $event_definition['content'] . '");
				' . $this->element_varname . '->addActivity(' . $this->element_varname . '_sendTaskActivity);
			';
        }

        if (isset($element['attributes']['message'])) {
            $data_inputs = $this->getDataInputAssociationIdentifiers($element);
            $task_parameters = '';
            $message_name = $element['attributes']['message'];
            if (substr($message_name, 0, 6) == 'ilias:') {
                $message_name = substr($message_name, 6);
            }
            if (count($data_inputs)) {
                $task_parameters = '"' . implode('","', $data_inputs) . '"';
            }

            $class_object->registerRequire('./Services/WorkflowEngine/classes/activities/class.ilSendMailActivity.php');
            $code .= '
				' . $this->element_varname . '_sendTaskActivity = new ilSendMailActivity(' . $this->element_varname . ');
				' . $this->element_varname . '_sendTaskActivity->setMessageName(\'' . $message_name . '\');
				' . $this->element_varname . '_sendTaskActivity_params = array(' . $task_parameters . ');
				' . $this->element_varname . '_sendTaskActivity->setParameters(' . $this->element_varname . '_sendTaskActivity_params);
				' . $this->element_varname . '->addActivity(' . $this->element_varname . '_sendTaskActivity);
			';
        }

        $code .= $this->handleDataAssociations($element, $class_object, $this->element_varname);

        return $code;
    }
}
