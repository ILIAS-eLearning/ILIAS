<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBibliographicImporter
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class ilBibliographicImporter extends ilXmlImporter
{

    protected ?\ilBibliographicDataSet $ds = null;


    public function init() : void
    {
        $this->ds = new ilBibliographicDataSet();
        $this->ds->setDSPrefix("ds");
        $this->ds->setImportDirectory($this->getImportDirectory());
    }


    /**
     * Executes the Import
     * @param string          $a_entity
     * @param string          $a_id
     * @param string          $a_xml
     * @param ilImportMapping $a_mapping
     * @return void
     */
    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping) : void
    {
        $parser = new ilDataSetImportParser($a_entity, $this->getSchemaVersion(), $a_xml, $this->ds, $a_mapping);
    }
}
