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
 * Class ilIntermediateThrowEventElement
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilIntermediateThrowEventElement extends ilBaseElement
{
    public string $element_varname;

    public function getPHP(array $element, ilWorkflowScaffold $class_object): string
    {
        $code = "";
        $element_id = ilBPMN2ParserUtils::xsIDToPHPVarname($element['attributes']['id']);
        $this->element_varname = '$_v_' . $element_id;

        $event_definition = null;
        if (count($element['children'])) {
            foreach ($element['children'] as $child) {
                if (isset($child['name']) && $child['name'] === 'messageEventDefinition') {
                    $event_definition = ilBPMN2ParserUtils::extractILIASEventDefinitionFromProcess(
                        $child['attributes']['messageRef'] ?? '',
                        'message',
                        $this->bpmn2_array
                    );
                }
                if (isset($child['name']) && $child['name'] === 'signalEventDefinition') {
                    $event_definition = ilBPMN2ParserUtils::extractILIASEventDefinitionFromProcess(
                        $child['attributes']['signalRef'] ?? '',
                        'signal',
                        $this->bpmn2_array
                    );
                }
            }
        }

        $class_object->registerRequire('./Services/WorkflowEngine/classes/nodes/class.ilBasicNode.php');
        $code .= '
			' . $this->element_varname . ' = new ilBasicNode($this);
			$this->addNode(' . $this->element_varname . ');
			' . $this->element_varname . '->setName(\'' . $this->element_varname . '\');
		';

        if (isset($event_definition['type'], $event_definition['content'])) {
            $class_object->registerRequire('./Services/WorkflowEngine/classes/activities/class.ilEventRaisingActivity.php');
            $code .= '
				' . $this->element_varname . '_throwEventActivity = new ilEventRaisingActivity(' . $this->element_varname . ');
				' . $this->element_varname . '_throwEventActivity->setName(\'' . $this->element_varname . '\');
				' . $this->element_varname . '_throwEventActivity->setEventType("' . $event_definition['type'] . '");
				' . $this->element_varname . '_throwEventActivity->setEventName("' . $event_definition['content'] . '");
				' . $this->element_varname . '->addActivity(' . $this->element_varname . '_throwEventActivity);
			';
        }

        return $code;
    }
}
