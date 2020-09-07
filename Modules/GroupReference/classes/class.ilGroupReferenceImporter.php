<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");
include_once './Services/ContainerReference/classes/class.ilContainerReferenceImporter.php';


/**
* group reference xml importer
*
* @author Fabian Wolf <wolf@leifos.com>
* @extends ilContainerReferenceImporter
* @ingroup ModulesGroupReference
*/
class ilGroupReferenceImporter extends ilContainerReferenceImporter
{

    /**
     * Get reference type
     * @return string
     */
    protected function getType()
    {
        return 'grpr';
    }

    /**
     * @param string $a_xml
     * @return ilGroupReferenceXmlParser
     */
    protected function initParser($a_xml)
    {
        include_once './Modules/GroupReference/classes/class.ilGroupReferenceXmlParser.php';
        return new ilGroupReferenceXmlParser($a_xml);
    }
}
