<?php declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */



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
    private ilLogger $logger;

    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->grp();
    }
    
    /**
     * @inheritDoc
     */
    public function init() : void
    {
    }

    /**
     * @inheritDoc
     */
    public function getXmlExportHeadDependencies(string $a_entity, string $a_target_release, array $a_ids) : array
    {
        // always trigger container because of co-page(s)
        return array(
            array(
                'component' => 'Services/Container',
                'entity' => 'struct',
                'ids' => $a_ids
            )
        );
    }
    
    
    /**
     * @inheritDoc
     */
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        $refs = ilObject::_getAllReferences((int) $a_id);
        $group_ref_id = end($refs);
        $group = ilObjectFactory::getInstanceByRefId($group_ref_id, false);
        
        if (!$group instanceof ilObjGroup) {
            $this->logger->warning($a_id . ' is not instance of type group');
            return '';
        }
        
        $writer = new ilGroupXMLWriter($group);
        $writer->setMode(ilGroupXMLWriter::MODE_EXPORT);
        $writer->start();
        return $writer->getXML();
    }
    
    /**
     * @inheritDoc
     */
    public function getValidSchemaVersions(string $a_entity) : array
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
