<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Importer class for media pools
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilMediaPoolImporter extends ilXmlImporter
{

    /**
     * Initialisation
     */
    public function init()
    {
        $this->ds = new ilMediaPoolDataSet();
        $this->ds->setDSPrefix("ds");

        $this->config = $this->getImport()->getConfig("Modules/MediaPool");
        if ($this->config->getTranslationImportMode()) {
            $this->ds->setTranslationImportMode(
                $this->config->getTranslationLM(),
                $this->config->getTranslationLang()
            );
            $cop_config = $this->getImport()->getConfig("Services/COPage");
            $cop_config->setUpdateIfExists(true);
            $cop_config->setForceLanguage($this->config->getTranslationLang());
            $cop_config->setReuseOriginallyExportedMedia(true);
            $cop_config->setSkipInternalLinkResolve(true);

            $mob_config = $this->getImport()->getConfig("Services/MediaObjects");
            $mob_config->setUsePreviousImportIds(true);
        }
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
        $pg_map = $a_mapping->getMappingsOfEntity("Modules/MediaPool", "pg");

        foreach ($pg_map as $pg_id) {
            $mep_id = ilMediaPoolItem::getPoolForItemId($pg_id);
            $mep_id = current($mep_id);
            ilMediaPoolPage::_writeParentId("mep", $pg_id, $mep_id);
        }
    }
}
