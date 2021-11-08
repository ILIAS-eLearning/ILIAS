<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Importer class for external feeds
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilExternalFeedImporter extends ilXmlImporter
{

    /**
     * Initialisation
     */
    public function init() : void
    {
        $this->ds = new ilExternalFeedDataSet();
        $this->ds->setDSPrefix("ds");
    }


    /**
     * Import XML
     * @param
     * @return void
     */
    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping) : void
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
