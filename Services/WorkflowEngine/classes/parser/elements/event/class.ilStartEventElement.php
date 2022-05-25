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
 * Class ilStartEventElement
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilStartEventElement extends ilBaseElement
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
        $this->element_varname = '$_v_' . $element_id; // TODO: xsd:ID allows hyphens and periods. Deal with it!

        $event_definition = null;

        $hasChildren = (isset($element['children']) && is_array($element['children']) && count($element['children']) > 0);
        if ($hasChildren) {
            foreach ($element['children'] as $child) {
                if (isset($child['name']) && $child['name'] === 'messageEventDefinition') {
                    $class_object->registerStartEventRef($child['attributes']['messageRef'] ?? '');
                    $event_definition = ilBPMN2ParserUtils::extractILIASEventDefinitionFromProcess(
                        $child['attributes']['messageRef'] ?? '',
                        'message',
                        $this->bpmn2_array
                    );
                }
                if (isset($child['name']) && $child['name'] === 'signalEventDefinition') {
                    $class_object->registerStartSignalRef($child['attributes']['signalRef'] ?? '');
                    $event_definition = ilBPMN2ParserUtils::extractILIASEventDefinitionFromProcess(
                        $child['attributes']['signalRef'] ?? '',
                        'signal',
                        $this->bpmn2_array
                    );
                }
                if (isset($child['name']) && $child['name'] === 'timerEventDefinition') {
                    $class_object->registerStartTimerRef($child['attributes']['id'] ?? '');
                    $event_definition = ilBPMN2ParserUtils::extractTimeDateEventDefinitionFromElement(
                        $child['attributes']['id'] ?? '',
                        'startEvent',
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
			' . $this->element_varname . '_detector->setName(\'' . $this->element_varname . '_detector\');
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
        } else {
            $code .= '
			$this->setStartNode(' . $this->element_varname . ');
			';
        }

        $code .= $this->handleDataAssociations($element, $class_object, $this->element_varname);

        return $code;
    }
}
