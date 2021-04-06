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
     *
     * @param
     * @return
     */
    public function init()
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
