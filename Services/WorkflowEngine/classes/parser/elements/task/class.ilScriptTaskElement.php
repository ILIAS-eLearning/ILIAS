<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilScriptTaskElement
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilScriptTaskElement extends ilBaseElement
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

        $event_definition = null;

        $class_object->registerRequire('./Services/WorkflowEngine/classes/nodes/class.ilBasicNode.php');
        $code .= '
			' . $this->element_varname . ' = new ilBasicNode($this);
			$this->addNode(' . $this->element_varname . ');
			' . $this->element_varname . '->setName(\'' . $this->element_varname . '\');
		';
        $script_definition = ilBPMN2ParserUtils::extractScriptDefinitionFromElement($element);

        $class_object->addAuxilliaryMethod(
            "public function _v_" . $element_id . "_script(\$context)
			 {
			 " . $script_definition . "
			 }"
        );

        $class_object->registerRequire('./Services/WorkflowEngine/classes/activities/class.ilScriptActivity.php');

        $code .= "
			" . $this->element_varname . "_scriptActivity = new ilScriptActivity(" . $this->element_varname . ");
			" . $this->element_varname . "_scriptActivity->setName('" . $this->element_varname . "');
			" . $this->element_varname . "_scriptActivity->setMethod('" . '_v_' . $element_id . "_script');
			" . $this->element_varname . "->addActivity(" . $this->element_varname . "_scriptActivity);
			";

        $code .= $this->handleDataAssociations($element, $class_object, $this->element_varname);

        return $code;
    }
}
