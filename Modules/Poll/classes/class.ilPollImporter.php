<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Importer class for poll
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPollImporter extends ilXmlImporter
{
    protected $ds;
    
    /**
     * Initialisation
     */
    public function init()
    {
        $this->ds = new ilPollDataSet();
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
        $this->ds->setImportDirectory($this->getImportDirectory());
        $parser = new ilDataSetImportParser(
            $a_entity,
            $this->getSchemaVersion(),
            $a_xml,
            $this->ds,
            $a_mapping
        );
    }
}
