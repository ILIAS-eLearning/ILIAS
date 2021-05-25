<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Importer class for wikis
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWikiImporter extends ilXmlImporter
{

    /**
     * Initialisation
     */
    public function init()
    {
        $this->ds = new ilWikiDataSet();
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

    /**
     * Final processing
     *
     * @param	array		mapping array
     */
    public function finalProcessing($a_mapping)
    {
        $wpg_map = $a_mapping->getMappingsOfEntity("Modules/Wiki", "wpg");

        foreach ($wpg_map as $wpg_id) {
            $wiki_id = ilWikiPage::lookupWikiId($wpg_id);
            ilWikiPage::_writeParentId("wpg", $wpg_id, $wiki_id);
        }
    }
}
