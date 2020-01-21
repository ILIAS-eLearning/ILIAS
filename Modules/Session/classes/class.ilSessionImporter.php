<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for sessions
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ModulesSession
 */
class ilSessionImporter extends ilXmlImporter
{

    /**
     * Initialisation
     */
    public function init()
    {
        include_once("./Modules/Session/classes/class.ilSessionDataSet.php");
        $this->ds = new ilSessionDataSet();
        $this->ds->setDSPrefix("ds");
    }


    /**
     * Import XML
     *
     * @param
     * @return
     */
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        $this->ds->setTargetId($a_mapping->getTargetId());
        $parser = new ilDataSetImportParser(
            $a_entity,
            $this->getSchemaVersion(),
            $a_xml,
            $this->ds,
            $a_mapping
        );
    }
}
