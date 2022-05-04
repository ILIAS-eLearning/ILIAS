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
 * Class ilReceiveTaskElement
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilReceiveTaskElement extends ilBaseElement
{
    public string $element_varname;

    /**
     * @param                     $element
     * @param ilWorkflowScaffold  $class_object
     *
     * @return string
     */
    public function getPHP($element, ilWorkflowScaffold $class_object) : string// TODO PHP8-REVIEW Type hint or corresponding PHPDoc missing
    {
        $code = "";
        $element_id = ilBPMN2ParserUtils::xsIDToPHPVarname($element['attributes']['id']);
        $this->element_varname = '$_v_' . $element_id;

        $event_definition = null;
        if (count($element['children'])) {
            foreach ($element['children'] as $child) {
                if ($child['name'] === 'messageEventDefinition') {
                    $event_definition = ilBPMN2ParserUtils::extractILIASEventDefinitionFromProcess(
                        $child['attributes']['messageRef'],
                        'message',
                        $this->bpmn2_array
                    );
                }
                if ($child['name'] === 'signalEventDefinition') {
                    $event_definition = ilBPMN2ParserUtils::extractILIASEventDefinitionFromProcess(
                        $child['attributes']['signalRef'],
                        'signal',
                        $this->bpmn2_array
                    );
                }
                if ($child['name'] === 'timerEventDefinition') {
                    $event_definition = ilBPMN2ParserUtils::extractTimeDateEventDefinitionFromElement(
                        $child['attributes']['id'],
                        'intermediateCatchEvent',
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

        if (is_array($event_definition)) {
            $class_object->registerRequire('./Services/WorkflowEngine/classes/detectors/class.ilEventDetector.php');
            $code .= '
			' . $this->element_varname . '_detector = new ilEventDetector(' . $this->element_varname . ');
			' . $this->element_varname . '_detector->setName(\'' . $this->element_varname . '\');
			' . $this->element_varname . '_detector->setEvent(			"' . $event_definition['type'] . '", 			"' . $event_definition['content'] . '");
			' . $this->element_varname . '_detector->setEventSubject(	"' . $event_definition['subject_type'] . '", 	"' . $event_definition['subject_id'] . '");
			' . $this->element_varname . '_detector->setEventContext(	"' . $event_definition['context_type'] . '", 	"' . $event_definition['context_id'] . '");
			';
            if (isset($event_definition['listening_start']) || isset($event_definition['listening_end'])) {
                $code .= $this->element_varname . '_detector->setListeningTimeframe(' . (int) $event_definition['listening_start'] .
                    ', ' . (int) $event_definition['listening_end'] . ');';
            } elseif (isset($event_definition['listening_relative'], $event_definition['listening_interval'])) {
                $code .= $this->element_varname . '_detector->setTimerRelative(true);';
                $code .= $this->element_varname . '_detector->setTimerLimit(' . (int) $event_definition['listening_interval'] . ');';
            }
        }

        $code .= $this->handleDataAssociations($element, $class_object, $this->element_varname);

        return $code;
    }
}
