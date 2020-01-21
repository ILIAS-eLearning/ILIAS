<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBPMN2Parser
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilBPMN2Parser
{
    /**
     * @param string      $bpmn2_xml
     * @param string|null $workflow_name
     *
     * @return string
     */
    public function parseBPMN2XML($bpmn2_xml, $workflow_name = null)
    {
        $bpmn2_array = $this->convertXmlToArray($bpmn2_xml);
        $process = $this->getProcessNodeFromArray($bpmn2_array);
        $messages = $this->getMessageNodesFromArray($bpmn2_array);

        $workflow_name = $this->determineWorkflowClassName($workflow_name, $bpmn2_array, $process);

        require_once './Services/WorkflowEngine/classes/parser/class.ilWorkflowScaffold.php';
        $class_object = new ilWorkflowScaffold($bpmn2_array);

        $constructor_method_content = '';

        $class_object->setWorkflowName($workflow_name);

        $hasChildren = (isset($process['children']) && is_array($process['children']) && count($process['children']) > 0);
        if ($hasChildren) {
            $stashed_sequence_flows = array(); // There can be no assumption, that the workflow is modeled in sequence,
                                               // so we need to stash the connectors to add them after the nodes.
            $stashed_associations = array(); // There can be no assumption, that the workflow is modeled in sequence,
                                               // so we need to stash the connectors to add them after the nodes.
            $stashed_process_extensions = array(); // It was found that modelers add extensions at process level,
                                               // they are stored for possible future use.
            require_once './Services/WorkflowEngine/classes/parser/elements/class.ilBPMN2ElementLoader.php';

            $loader = new ilBPMN2ElementLoader($bpmn2_array);

            foreach ($process['children'] as $element) {
                if ($element['name'] == 'ioSpecification') {
                    foreach ($element['children'] as $iospec_element) {
                        $element_object = $loader->load($iospec_element['name']);
                        $constructor_method_content .= $element_object->getPHP($iospec_element, $class_object);
                    }

                    continue;
                }

                if ($element['name'] == 'sequenceFlow') {
                    $stashed_sequence_flows[] = $element;
                } elseif ($element['name'] == 'association') {
                    $stashed_associations[] = $element;
                } elseif ($element['name'] == 'extensionElements') {
                    $stashed_process_extensions[] = $element;
                } else {
                    $element_object = $loader->load($element['name']);
                    $constructor_method_content .= $element_object->getPHP($element, $class_object);
                }
            }

            foreach ($stashed_sequence_flows as $element) {
                $element_object = $loader->load($element['name']);
                $constructor_method_content .= $element_object->getPHP($element, $class_object);
            }

            foreach ($stashed_associations as $element) {
                $element_object = $loader->load($element['name']);
                $constructor_method_content .= $element_object->getPHP($element, $class_object);
            }
        }

        if (count($messages)) {
            $message_definitions = array();
            foreach ($messages as $message) {
                $element_object = $loader->load('messageDefinition');
                $message_definitions[] = $element_object->getMessageDefinitionArray($message);
            }

            $code = '
			public static function getMessageDefinition($id)
			{
				$definitions = array(' . implode(',', $message_definitions) . '
				);
				return $definitions[$id];
			}
			';
            $class_object->addAuxilliaryMethod($code);
        }

        $class_object->setConstructorMethodContent($constructor_method_content);
        $class_source = '';

        if (strlen($constructor_method_content)) {
            $class_source .= $class_object->getPHP();
        }

        return "<?php\n" . $class_source . "\n?>"; // PHP Code
    }

    /**
     * @param string $xml
     *
     * @return mixed
     */
    public function convertXmlToArray($xml)
    {
        require_once './Services/WorkflowEngine/classes/parser/class.ilBPMN2ParserUtils.php';
        $xml_to_array_parser = new ilBPMN2ParserUtils();
        $bpmn2               = $xml_to_array_parser->load_string($xml);
        return $bpmn2;
    }

    /**
     * @param array $bpmn2
     *
     * @return array
     */
    public function getProcessNodeFromArray($bpmn2)
    {
        $process = array();
        foreach ((array) @$bpmn2['children'] as $bpmn2_part) {
            if ($bpmn2_part['name'] == 'process') {
                $process = $bpmn2_part;
                break;
            }
        }
        return $process;
    }

    /**
     * @param array $bpmn2
     *
     * @return array
     */
    public function getMessageNodesFromArray($bpmn2)
    {
        $messages = array();
        foreach ((array) @$bpmn2['children'] as $bpmn2_part) {
            if ($bpmn2_part['name'] == 'message') {
                $messages[] = $bpmn2_part;
                break;
            }
        }
        return $messages;
    }

    /**
     * @param string $workflow_name
     * @param array  $bpmn2_array
     * @param array  $process
     *
     * @return mixed
     */
    public function determineWorkflowClassName($workflow_name, $bpmn2_array, $process)
    {
        $hasChildren = (isset($bpmn2_array['children']) && is_array($bpmn2_array['children']) && count($bpmn2_array['children']) > 0);
        if (!$workflow_name && !$hasChildren) {
            $workflow_name = $bpmn2_array['attributes']['id'];
        }

        if (!$workflow_name) {
            $workflow_name = $process['attributes']['id'];
            return $workflow_name;
        }

        if ($workflow_name) {
            $workflow_name = substr($workflow_name, 0, strpos($workflow_name, '.'));
        }
        return $workflow_name;
    }
}
