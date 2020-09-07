<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSequenceFlowElement
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilSequenceFlowElement extends ilBaseElement
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
