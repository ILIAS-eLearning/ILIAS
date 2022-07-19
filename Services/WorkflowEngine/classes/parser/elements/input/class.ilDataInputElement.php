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
 * Class ilDataInputElement
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilDataInputElement extends ilBaseElement
{
    public string $element_varname;

    public function getPHP(array $element, ilWorkflowScaffold $class_object) : string
    {
        $name = $element['name'];
        $element_id = ilBPMN2ParserUtils::xsIDToPHPVarname($element['attributes']['id']);
        $ext_name = ilBPMN2ParserUtils::extractDataNamingFromElement($element);

        if ($ext_name != null) {
            $name = $ext_name;
        }

        $input_properties = ilBPMN2ParserUtils::extractILIASInputPropertiesFromElement($element);
        $array_elements = [];
        foreach ((array) $input_properties as $key => $value) {
            $array_elements[] = '"' . $key . '" => "' . $value . '"';
        }

        $definition = 'array(' . implode(',', $array_elements) . ')';

        $object_definition = ilBPMN2ParserUtils::extractILIASDataObjectDefinitionFromElement($element);

        if ($object_definition != null) {
            $type = $object_definition['type'];
            $role = $object_definition['role'];
        } else {
            $type = 'mixed';
            $role = 'undefined';
        }

        $code = '
			$this->defineInstanceVar("' . $element_id . '", "' . $name . '");
			$this->registerInputVar("' . $element_id . '", ' . $definition . ');
';

        return $code;
    }
}
