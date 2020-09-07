<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Services/Export/classes/class.ilXmlExporter.php';

/**
* Role Exporter
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesAccessControl
*/
class ilAccessControlExporter extends ilXmlExporter
{
    private $writer = null;

    /**
     * Constructor
     */
    public function __construct()
    {
    }
    
    /**
     * Init export
     * @return
     */
    public function init()
    {
    }
    
    /**
     * Get head dependencies
     *
     * @param		string		entity
     * @param		string		target release
     * @param		array		ids
     * @return		array		array of array with keys "component", entity", "ids"
     */
    public function getXmlExportHeadDependencies($a_entity, $a_target_release, $a_ids)
    {
        return array();
    }
    
    
    /**
     * Get xml
     * @param object $a_entity
     * @param object $a_schema_version
     * @param object $a_id
     * @return
     */
    public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        
        include_once './Services/AccessControl/classes/class.ilRoleXmlExport.php';
        $writer = new ilRoleXmlExport();
        
        include_once './Services/Export/classes/class.ilExportOptions.php';
        $eo = ilExportOptions::getInstance();
        $eo->read();
        
        $rolf = $eo->getOptionByObjId($a_id, ilExportOptions::KEY_ROOT);
        // @todo refactor rolf
        $writer->setRoles(array($a_id => $rolf));
        $writer->write();
        return $writer->xmlDumpMem($format);
    }
    
    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     *
     * @return
     */
    public function getValidSchemaVersions($a_entity)
    {
        return array(
            "4.3.0" => array(
                "namespace" => "http://www.ilias.de/AccessControl/Role/role/4_3",
                "xsd_file" => "ilias_role_4_3.xsd",
                "uses_dataset" => false,
                "min" => "4.3.0",
                "max" => "")
        );
    }
}
