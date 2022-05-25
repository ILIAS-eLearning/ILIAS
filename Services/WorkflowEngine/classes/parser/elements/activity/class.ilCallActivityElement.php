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
 * Class ilCallActivityElement
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilCallActivityElement extends ilBaseElement
{
    public string $element_varname;

    public function getPHP($element, ilWorkflowScaffold $class_object) : string// TODO PHP8-REVIEW Type hint or corresponding PHPDoc missing
    {
        $code = "";
        $element_id = ilBPMN2ParserUtils::xsIDToPHPVarname($element['attributes']['id']);
        $this->element_varname = '$_v_' . $element_id;

        $library_definition = ilBPMN2ParserUtils::extractILIASLibraryCallDefinitionFromElement($element);

        $class_object->registerRequire('./Services/WorkflowEngine/classes/nodes/class.ilBasicNode.php');
        $class_object->registerRequire('./Services/WorkflowEngine/classes/activities/class.ilStaticMethodCallActivity.php');

        $data_inputs = $this->getDataInputAssociationIdentifiers($element);
        $activity_parameters = '';
        if (count($data_inputs)) {
            $activity_parameters = '"' . implode('","', $data_inputs) . '"';
        }

        $data_outputs = $this->getDataOutputAssociationIdentifiers($element);
        $activity_outputs = '';
        if (count($data_outputs)) {
            $activity_outputs = '"' . implode('","', $data_outputs) . '"';
        }

        $code .= '
			' . $this->element_varname . ' = new ilBasicNode($this);
			$this->addNode(' . $this->element_varname . ');
			' . $this->element_varname . '->setName(\'' . $this->element_varname . '\');
			
			' . $this->element_varname . '_callActivity = new ilStaticMethodCallActivity(' . $this->element_varname . ');
			' . $this->element_varname . '_callActivity->setName(\'' . $this->element_varname . '_callActivity\');
			' . $this->element_varname . '_callActivity->setIncludeFilename("' . $library_definition['include_filename'] . '");
			' . $this->element_varname . '_callActivity->setClassAndMethodName("' . $library_definition['class_and_method'] . '");
			' . $this->element_varname . '_callActivity_params = array(' . $activity_parameters . ');
			' . $this->element_varname . '_callActivity->setParameters(' . $this->element_varname . '_callActivity_params);
			' . $this->element_varname . '_callActivity_outputs = array(' . $activity_outputs . ');
			' . $this->element_varname . '_callActivity->setOutputs(' . $this->element_varname . '_callActivity_outputs);
			' . $this->element_varname . '->addActivity(' . $this->element_varname . '_callActivity);
		';
        $code .= $this->handleDataAssociations($element, $class_object, $this->element_varname);
        return $code;
    }
}
