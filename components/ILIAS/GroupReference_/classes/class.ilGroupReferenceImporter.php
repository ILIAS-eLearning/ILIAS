<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/Export_/classes/class.ilXmlImporter.php");
include_once substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . '/components/ILIAS/ContainerReference_/classes/class.ilContainerReferenceImporter.php';


/**
* group reference xml importer
*
* @author Fabian Wolf <wolf@leifos.com>
* @extends ilContainerReferenceImporter
* @ingroup ModulesGroupReference
*/
class ilGroupReferenceImporter extends ilContainerReferenceImporter
{
    protected function getType(): string
    {
        return 'grpr';
    }

    protected function initParser(string $a_xml): ilContainerReferenceXmlParser
    {
        return new ilGroupReferenceXmlParser($a_xml);
    }
}
