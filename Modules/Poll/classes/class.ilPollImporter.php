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
    public function init() : void
    {
        $this->ds = new ilPollDataSet();
        $this->ds->setDSPrefix("ds");
    }

    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping) : void
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
