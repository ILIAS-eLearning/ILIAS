<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Importer class for taxonomies
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTaxonomyImporter extends ilXmlImporter
{

    /**
     * Initialisation
     */
    public function init()
    {
        $this->ds = new ilTaxonomyDataSet();
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
