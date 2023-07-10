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
 * Class ilDataObjectElement
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilDataObjectElement extends ilBaseElement
{
    public string $element_varname;

    public function getPHP(array $element, ilWorkflowScaffold $class_object): string
    {
        $name = $element['name'];
        $element_id = ilBPMN2ParserUtils::xsIDToPHPVarname($element['attributes']['id']);
        $ext_name = ilBPMN2ParserUtils::extractDataNamingFromElement($element);

        $object_definition = ilBPMN2ParserUtils::extractILIASDataObjectDefinitionFromElement($element);
        if ($object_definition != null) {
            $type = $object_definition['type'];
            $role = $object_definition['role'];
        } else {
            $type = 'mixed';
            $role = 'undefined';
        }

        if ($ext_name != null) {
            $name = $ext_name;
        }
        $code = '
			$this->defineInstanceVar("' . $element_id . '","' . $name . '");
		';

        return $code;
    }
}
