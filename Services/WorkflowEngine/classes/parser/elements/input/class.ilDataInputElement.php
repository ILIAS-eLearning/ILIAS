<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDataInputElement
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilDataInputElement extends ilBaseElement
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

        if ($ext_name != null) {
            $name = $ext_name;
        }

        $input_properties = ilBPMN2ParserUtils::extractILIASInputPropertiesFromElement($element);
        $array_elements = array();
        foreach ((array) $input_properties as $key => $value) {
            $array_elements[] = '"' . $key . '" => "' . $value . '"';
        }

        $definition = 'array(' . implode(',', (array) $array_elements) . ')';

        $object_definition = ilBPMN2ParserUtils::extractILIASDataObjectDefinitionFromElement($element);

        if ($object_definition != null) {
            $type = $object_definition['type'];
            $role = $object_definition['role'];
        } else {
            $type = 'mixed';
            $role = 'undefined';
        }

        $code = "";
        $code .= '
			$this->defineInstanceVar("' . $element_id . '", "' . $name . '", false, "", "' . $type . '", "' . $role . '" );
			$this->registerInputVar("' . $element_id . '", ' . $definition . ');
';

        return $code;
    }
}
