<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Modules/Folder/classes/class.ilFolderXmlWriter.php';
include_once './Services/Export/classes/class.ilXmlExporter.php';

/**
* Folder export
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesBooking
*/
class ilGroupExporter extends ilXmlExporter
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
        // always trigger container because of co-page(s)
        return array(
            array(
                'component'		=> 'Services/Container',
                'entity'		=> 'struct',
                'ids'			=> $a_ids
            )
        );
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
        $group_ref_id = end(ilObject::_getAllReferences($a_id));
        $group = ilObjectFactory::getInstanceByRefId($group_ref_id, false);
        
        if (!$group instanceof ilObjGroup) {
            $GLOBALS['DIC']->logger()->grp()->warning($a_id . ' is not instance of type group');
            return '';
        }
        
        include_once './Modules/Group/classes/class.ilGroupXMLWriter.php';
        $this->writer = new ilGroupXMLWriter($group);
        $this->writer->setMode(ilGroupXMLWriter::MODE_EXPORT);
        $this->writer->start();
        return $this->writer->getXML();
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
            "4.1.0" => array(
                "namespace" => "http://www.ilias.de/Modules/Group/grp/4_1",
                "xsd_file" => "ilias_grp_4_1.xsd",
                "uses_dataset" => false,
                "min" => "4.1.0",
                "max" => "4.4.999"),
            "5.0.0" => array(
                "namespace" => "http://www.ilias.de/Modules/Group/grp/5_0",
                "xsd_file" => "ilias_grp_5_0.xsd",
                "uses_dataset" => false,
                "min" => "5.0.0",
                "max" => "")
        );
    }
}
