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
 * Class ilDataOutputElement
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilDataOutputElement extends ilBaseElement
{
    public string $element_varname;

    /**
     * @param                     $element
     * @param ilWorkflowScaffold  $class_object
     *
     * @return string
     */
    public function getPHP($element, ilWorkflowScaffold $class_object) : string// TODO PHP8-REVIEW Type hint or corresponding PHPDoc missing
    {
        $name = $element['name'];
        $element_id = ilBPMN2ParserUtils::xsIDToPHPVarname($element['attributes']['id']);
        $ext_name = ilBPMN2ParserUtils::extractDataNamingFromElement($element);

        if ($ext_name != null) {
            $name = $ext_name;
        }
        $code = '
			$this->defineInstanceVar("' . $element_id . '","' . $name . '" );
			$this->registerOutputVar("' . $element_id . '");
';

        return $code;
    }
}
