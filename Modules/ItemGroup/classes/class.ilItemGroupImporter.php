<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Importer class for item groups
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilItemGroupImporter extends ilXmlImporter
{

    /**
     * Initialisation
     */
    public function init()
    {
        $this->ds = new ilItemGroupDataSet();
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
        $parser = new ilDataSetImportParser(
            $a_entity,
            $this->getSchemaVersion(),
            $a_xml,
            $this->ds,
            $a_mapping
        );
    }
}
