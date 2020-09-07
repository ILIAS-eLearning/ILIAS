<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for media pools
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ModulesMediaPool
 */
class ilMetaDataImporter extends ilXmlImporter
{
    /**
     * Import XML
     *
     * @param
     * @return
     */
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        $new_id = $a_mapping->getMapping("Services/MetaData", "md", $a_id);

        if ($new_id != "") {
            include_once("./Services/MetaData/classes/class.ilMDXMLCopier.php");
            $id = explode(":", $new_id);
            $xml_copier = new ilMDXMLCopier($a_xml, $id[0], $id[1], $id[2]);
            $xml_copier->startParsing();
        }
    }
}
