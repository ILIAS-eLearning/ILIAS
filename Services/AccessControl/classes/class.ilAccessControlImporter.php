<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
* role role template xml importer
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesAccessControl
*/
class ilAccessControlImporter extends ilXmlImporter
{
    public function init()
    {
    }
    
    /**
     * Import XML
     *
     * @param
     * @return
     */
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        $role_folder_id = $a_mapping->getMapping('Services/AccessControl', 'rolf', 0);
        
        include_once './Services/AccessControl/classes/class.ilRoleXmlImporter.php';
        include_once './Services/AccessControl/classes/class.ilObjRole.php';
        $importer = new ilRoleXmlImporter($role_folder_id);
        $importer->setXml($a_xml);
        $importer->setRole(new ilObjRole());
        $importer->import();
    }
}
