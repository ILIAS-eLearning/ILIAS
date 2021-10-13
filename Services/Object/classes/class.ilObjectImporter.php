<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Importer class for objects (currently focused on translation information)
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjectImporter extends ilXmlImporter
{
    private $logger = null;
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->logger = $GLOBALS['DIC']->logger()->obj();
    }

    /**
     * Initialisation
     */
    public function init() : void
    {
        $this->ds = new ilObjectDataSet();
        $this->ds->setDSPrefix("ds");
        $this->ds->setImportDirectory($this->getImportDirectory());
    }


    /**
     * Import XML
     *
     * @param
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
