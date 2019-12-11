<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilStartEventElement
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilStartEventElement extends ilBaseElement
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
        $this->element_varname = '$_v_' . $element_id; // TODO: xsd:ID allows hyphens and periods. Deal with it!

        $event_definition = null;

        $hasChildren = (isset($element['children']) && is_array($element['children']) && count($element['children']) > 0);
        if ($hasChildren) {
            foreach ($element['children'] as $child) {
                if ($child['name'] == 'messageEventDefinition') {
                    $class_object->registerStartEventRef($child['attributes']['messageRef']);
                    $event_definition = ilBPMN2ParserUtils::extractILIASEventDefinitionFromProcess(
                        $child['attributes']['messageRef'],
                        'message',
                        $this->bpmn2_array
                    );
                }
                if ($child['name'] == 'signalEventDefinition') {
                    $class_object->registerStartSignalRef($child['attributes']['signalRef']);
                    $event_definition = ilBPMN2ParserUtils::extractILIASEventDefinitionFromProcess(
                        $child['attributes']['signalRef'],
                        'signal',
                        $this->bpmn2_array
                    );
                }
                if ($child['name'] == 'timerEventDefinition') {
                    $class_object->registerStartTimerRef($child['attributes']['id']);
                    $event_definition = ilBPMN2ParserUtils::extractTimeDateEventDefinitionFromElement(
                        $child['attributes']['id'],
                        'startEvent',
                        $this->bpmn2_array
                    );
                    //$event_definition = $class_object->getTimeDateEventDefinition($child['attributes']['id']);
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
            } elseif (isset($event_definition['listening_relative']) && isset($event_definition['listening_interval'])) {
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
