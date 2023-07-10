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
 * Class ilDataObjectReferenceElement
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilDataObjectReferenceElement extends ilBaseElement
{
    public string $element_varname;

    public function getPHP(array $element, ilWorkflowScaffold $class_object): string
    {
        // We need to register an instance var that is a reference.
        $element_id = ilBPMN2ParserUtils::xsIDToPHPVarname($element['attributes']['id']);
        $name = $element['name'];
        $ext_name = ilBPMN2ParserUtils::extractDataNamingFromElement($element);

        if ($ext_name != null) {
            $name = $ext_name;
        }

        $code = '
			$this->defineInstanceVar("' . $element_id . '","' . $name . '");
		';

        return $code;
    }
}
