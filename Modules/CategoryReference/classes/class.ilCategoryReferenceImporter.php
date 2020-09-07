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
class ilCategoryReferenceImporter extends ilContainerReferenceImporter
{
        
    /**
     * Get reference type
     */
    protected function getType()
    {
        return 'catr';
    }
    
    /**
     * Init xml parser
     */
    protected function initParser($a_xml)
    {
        include_once './Modules/CategoryReference/classes/class.ilCategoryReferenceXmlParser.php';
        return new ilCategoryReferenceXmlParser($a_xml);
    }
}
