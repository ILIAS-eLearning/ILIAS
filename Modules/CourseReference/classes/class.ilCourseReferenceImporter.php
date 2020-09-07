<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");
include_once './Services/ContainerReference/classes/class.ilContainerReferenceImporter.php';


/**
* folder xml importer
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ModulesContainerReference
*/
class ilCourseReferenceImporter extends ilContainerReferenceImporter
{
        
    /**
     * Get reference type
     */
    protected function getType()
    {
        return 'crsr';
    }
    
    /**
     * Init xml parser
     */
    protected function initParser($a_xml)
    {
        include_once './Modules/CourseReference/classes/class.ilCourseReferenceXmlParser.php';
        return new ilCourseReferenceXmlParser($a_xml);
    }
}
