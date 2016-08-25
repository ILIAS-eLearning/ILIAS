<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilInclusiveGatewayElement
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilInclusiveGatewayElement extends ilBaseElement
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
		$this->element_varname = '$_v_'.$element_id;

		$event_definition = null;

		$class_object->registerRequire('./Services/WorkflowEngine/classes/nodes/class.ilCaseNode.php');
		$code .= '
			' . $this->element_varname . ' = new ilCaseNode($this);
			' . $this->element_varname . '->setName(\'' . $this->element_varname . '\');
			$this->addNode(' . $this->element_varname . ');
		';

		$code .= $this->handleDataAssociations($element, $class_object, $this->element_varname);

		return $code;
	}
}