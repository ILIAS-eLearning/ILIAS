<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./components/ILIAS/Export/classes/class.ilXmlImporter.php");
include_once './components/ILIAS/ContainerReference/classes/class.ilContainerReferenceImporter.php';


/**
* folder xml importer
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup components\ILIASContainerReference
*/
class ilCourseReferenceImporter extends ilContainerReferenceImporter
{
    protected function getType(): string
    {
        return 'crsr';
    }

    protected function initParser(string $a_xml): ilContainerReferenceXmlParser
    {
        return new ilCourseReferenceXmlParser($a_xml);
    }
}
