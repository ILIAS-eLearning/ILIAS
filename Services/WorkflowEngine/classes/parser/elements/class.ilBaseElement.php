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
 * Class ilBaseElement
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
abstract class ilBaseElement
{
    /** @var array $bpmn2_array */
    protected array $bpmn2_array;

    public function getBpmn2Array() : ?array
    {
        return $this->bpmn2_array;
    }

    public function setBpmn2Array($bpmn2_array) : void// TODO PHP8-REVIEW Type hint or corresponding PHPDoc missing
    {
        $this->bpmn2_array = $bpmn2_array;
    }

    public function handleDataAssociations(array $element, ilWorkflowScaffold $class_object, string $element_varname) : string
    {
        $code = '';
        if (isset($element['children']) && count($element['children'])) {
            foreach ($element['children'] as $child) {
                if ($child['name'] === 'dataInputAssociation') {
                    $class_object->registerRequire('./Services/WorkflowEngine/classes/detectors/class.ilDataDetector.php');
                    $reference_name = $child['children'][0]['content'];
                    $code .= '
			' . $element_varname . '_inputDataDetector = new ilDataDetector(' . $element_varname . ');
			' . $element_varname . '_inputDataDetector->setVarName("' . $reference_name . '");
			' . $element_varname . '_inputDataDetector->setName(' . $element_varname . '_inputDataDetector);
			' . $element_varname . '->addDetector(' . $element_varname . '_inputDataDetector);
		';
                }

                if ($child['name'] === 'dataOutputAssociation') {
                    $class_object->registerRequire('./Services/WorkflowEngine/classes/emitters/class.ilDataEmitter.php');
                    $reference_name = $child['children'][0]['content'];
                    // So we need a data emitter to the given
                    $code .= '
			' . $element_varname . '_outputDataEmitter = new ilDataEmitter(' . $element_varname . ');
			' . $element_varname . '_outputDataEmitter->setVarName("' . $reference_name . '");
			' . $element_varname . '_outputDataEmitter->setName(' . $element_varname . '_outputDataEmitter);
			' . $element_varname . '->addEmitter(' . $element_varname . '_outputDataEmitter);
		';
                }
            }
        }

        return $code;
    }

    /**
     * @param array $element
     *
     * @return array
     */
    public function getDataInputAssociationIdentifiers(array $element) : array
    {
        $retval = [];

        if (isset($element['children'])) {
            foreach ($element['children'] as $child) {
                if ($child['namespace'] === 'bpmn2' && $child['name'] === 'dataInputAssociation') {
                    foreach ($child['children'] as $reference) {
                        if ($reference['namespace'] === 'bpmn2' && $reference['name'] === 'sourceRef') {
                            $retval[] = $reference['content'];
                        }
                    }
                }
            }
        }

        return $retval;
    }

    /**
     * @param array $element
     *
     * @return array
     */
    public function getDataOutputAssociationIdentifiers(array $element) : array
    {
        $retval = [];

        if (isset($element['children'])) {
            foreach ($element['children'] as $child) {
                if ($child['namespace'] === 'bpmn2' && $child['name'] === 'dataOutputAssociation') {
                    foreach ($child['children'] as $reference) {
                        if ($reference['namespace'] === 'bpmn2' && $reference['name'] === 'targetRef') {
                            $retval[] = $reference['content'];
                        }
                    }
                }
            }
        }

        return $retval;
    }
}
