<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDataObjectElement
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilDataObjectElement extends ilBaseElement
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
        $code = "";
        $code .= '
			$this->defineInstanceVar("' . $element_id . '","' . $name . '", false, "", "' . $type . '", "' . $role . '" );
		';

        return $code;
    }
}
