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
 * Class ilSequenceFlowElement
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilSequenceFlowElement extends ilBaseElement
{
    public string $element_varname;

    /**
     * @param                     $element
     * @param ilWorkflowScaffold  $class_object
     *
     * @return string
     */
    public function getPHP(array $element, ilWorkflowScaffold $class_object) : string
    {
        $code = "";
        $element_id = ilBPMN2ParserUtils::xsIDToPHPVarname($element['attributes']['id']);
        $this->element_varname = '$_v_' . $element_id;

        $source_element = '$_v_' . $element['attributes']['sourceRef'];
        $target_element = '$_v_' . $element['attributes']['targetRef'];

        $code .= '
			' . $target_element . '_detector = new ilSimpleDetector(' . $target_element . ');
			' . $target_element . '_detector->setName(\'' . $target_element . '_detector\');
			' . $target_element . '_detector->setSourceNode(' . $source_element . ');
			' . $target_element . '->addDetector(' . $target_element . '_detector);
			' . $source_element . '_emitter = new ilActivationEmitter(' . $source_element . ');
			' . $source_element . '_emitter->setName(\'' . $source_element . '_emitter\');
			' . $source_element . '_emitter->setTargetDetector(' . $target_element . '_detector);
			' . $source_element . '->addEmitter(' . $source_element . '_emitter);
		';

        $class_object->registerRequire('./Services/WorkflowEngine/classes/emitters/class.ilActivationEmitter.php');
        $class_object->registerRequire('./Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php');

        return $code;
    }
}
