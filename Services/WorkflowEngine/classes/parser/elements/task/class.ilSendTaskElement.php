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
 * Class ilSendTaskElement
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilSendTaskElement extends ilBaseElement
{
    public string $element_varname;

    public function getPHP(array $element, ilWorkflowScaffold $class_object): string
    {
        $code = "";
        $element_id = ilBPMN2ParserUtils::xsIDToPHPVarname($element['attributes']['id']);
        $this->element_varname = '$_v_' . $element['attributes']['id'];
        $event_definition = null;
        if (count($element['children'])) {
            foreach ($element['children'] as $child) {
                if ($child['name'] === 'messageEventDefinition') {
                    $event_definition = ilBPMN2ParserUtils::extractILIASEventDefinitionFromProcess($child['attributes']['messageRef'], 'message', $this->bpmn2_array);
                }
                if ($child['name'] === 'signalEventDefinition') {
                    $event_definition = ilBPMN2ParserUtils::extractILIASEventDefinitionFromProcess($child['attributes']['signalRef'], 'signal', $this->bpmn2_array);
                }
            }
        }

        $message_element = $element['attributes']['ilias:message'] ?? false;

        $class_object->registerRequire('./Services/WorkflowEngine/classes/nodes/class.ilBasicNode.php');
        $code .= '
			' . $this->element_varname . ' = new ilBasicNode($this);
			$this->addNode(' . $this->element_varname . ');
			' . $this->element_varname . '->setName(\'' . $this->element_varname . '\');
		';

        if (isset($event_definition['type'], $event_definition['content'])) {
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
            if (strpos($message_name, 'ilias:') === 0) {
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
