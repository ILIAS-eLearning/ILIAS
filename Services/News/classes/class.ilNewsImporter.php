<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for news
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ServicesNews
 */
class ilNewsImporter extends ilXmlImporter
{

    /**
     * Initialisation
     */
    public function init()
    {
        include_once("./Services/News/classes/class.ilNewsDataSet.php");
        $this->ds = new ilNewsDataSet();
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
        include_once("./Services/DataSet/classes/class.ilDataSetImportParser.php");
        $parser = new ilDataSetImportParser(
            $a_entity,
            $this->getSchemaVersion(),
            $a_xml,
            $this->ds,
            $a_mapping
        );
    }
}
