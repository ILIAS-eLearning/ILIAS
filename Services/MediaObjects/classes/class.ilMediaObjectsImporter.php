<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Importer class for media pools
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilMediaObjectsImporter extends ilXmlImporter
{

    /**
     * Init
     * @param
     * @return void
     */
    public function init() : void
    {
        $this->ds = new ilMediaObjectDataSet();
        $this->ds->setDSPrefix("ds");
        $this->ds->setImportDirectory($this->getImportDirectory());


        $this->config = $this->getImport()->getConfig("Services/MediaObjects");
        if ($this->config->getUsePreviousImportIds()) {
            $this->ds->setUsePreviousImportIds(true);
        }
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
