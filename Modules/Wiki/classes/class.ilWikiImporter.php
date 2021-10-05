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
    public function init() : void
    {
        $this->ds = new ilWikiDataSet();
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

    /**
     * Final processing
     *
     * @param	array		mapping array
     */
    public function finalProcessing(ilImportMapping $a_mapping) : void
    {
        $wpg_map = $a_mapping->getMappingsOfEntity("Modules/Wiki", "wpg");

        foreach ($wpg_map as $wpg_id) {
            $wiki_id = ilWikiPage::lookupWikiId($wpg_id);
            ilWikiPage::_writeParentId("wpg", $wpg_id, $wiki_id);
        }
    }
}
